<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MerchantModel;
use Firebase\JWT\JWT;

class MerchantAuth extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[3]',
            'phone'    => 'required|is_unique[merchants.phone]',
            'password' => 'required|min_length[6]',
            'address'  => 'required',
            'ktp_image' => 'required',
            'business_image' => 'required'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            if (isset($errors['phone'])) {
                $firstError = 'Nomor HP sudah terdaftar sebagai Mitra. Silakan gunakan nomor lain atau login.';
            }
            return $this->respond([
                'status' => 400,
                'message' => $firstError ?: 'Validasi formulir pendaftaran gagal',
                'data' => $errors
            ], 400);
        }

        $uploadDir = FCPATH . 'uploads/merchants/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $maxBytes = 10 * 1024 * 1024; // 10MB

        $detectImageExt = function ($decoded) {
            if (strlen($decoded) < 8) return false;
            
            // Check magic bytes directly (100% reliable, zero external extension dependency)
            if (substr($decoded, 0, 3) === "\xFF\xD8\xFF") {
                return 'jpg';
            }
            if (substr($decoded, 0, 4) === "\x89PNG") {
                return 'png';
            }
            if (substr($decoded, 0, 4) === 'RIFF' && strlen($decoded) >= 12 && substr($decoded, 8, 4) === 'WEBP') {
                return 'webp';
            }
            
            // Fallback to getimagesizefromstring if available
            if (function_exists('getimagesizefromstring')) {
                $info = @getimagesizefromstring($decoded);
                if ($info && !empty($info['mime'])) {
                    if ($info['mime'] === 'image/jpeg') return 'jpg';
                    if ($info['mime'] === 'image/png') return 'png';
                    if ($info['mime'] === 'image/webp') return 'webp';
                }
            }
            
            // Fallback to finfo if available
            if (function_exists('finfo_open')) {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $mime = @finfo_buffer($finfo, $decoded);
                    @finfo_close($finfo);
                    if ($mime === 'image/jpeg') return 'jpg';
                    if ($mime === 'image/png') return 'png';
                    if ($mime === 'image/webp') return 'webp';
                }
            }

            return false;
        };

        // Helper to process base64 image
        $processBase64 = function ($base64Data, $prefix) use ($uploadDir, $maxBytes, $detectImageExt) {
            if (empty($base64Data)) return false;
            // Strip data uri prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }
            $decoded = base64_decode($base64Data, true);
            if ($decoded === false || strlen($decoded) === 0) {
                return false;
            }
            if (strlen($decoded) > $maxBytes) {
                return false;
            }
            $ext = $detectImageExt($decoded);
            if (!$ext) {
                return false;
            }
            $fileName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            file_put_contents($uploadDir . $fileName, $decoded);
            return $fileName;
        };

        // Simpan KTP
        $ktpName = $processBase64($this->request->getVar('ktp_image'), 'ktp');
        if (!$ktpName) {
            return $this->respond([
                'status' => 400,
                'message' => 'Format file KTP tidak valid (harus JPG/PNG/WebP maks 10MB)',
                'data' => null
            ], 400);
        }

        // Simpan Foto Usaha
        $businessName = $processBase64($this->request->getVar('business_image'), 'business');
        if (!$businessName) {
            if (file_exists($uploadDir . $ktpName)) {
                @unlink($uploadDir . $ktpName);
            }
            return $this->respond([
                'status' => 400,
                'message' => 'Format file Foto Usaha tidak valid (harus JPG/PNG/WebP maks 10MB)',
                'data' => null
            ], 400);
        }

        $merchantModel = new MerchantModel();
        $merchantType = $this->request->getVar('merchant_type') ?: 'food';
        $category = $this->request->getVar('category') ?: null;

        $merchantModel->insert([
            'name'     => $this->request->getVar('name'),
            'phone'    => $this->request->getVar('phone'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'address'  => $this->request->getVar('address'),
            'merchant_type' => $merchantType,
            'category' => $category,
            'is_open'  => 0,
            'balance'  => 0,
            'verification_status' => 'pending',
            'ktp_image' => 'uploads/merchants/' . $ktpName,
            'business_image' => 'uploads/merchants/' . $businessName,
            'photo' => 'uploads/merchants/' . $businessName
        ]);

        return $this->respond([
            'status' => 201,
            'message' => 'Pendaftaran Merchant berhasil',
            'data' => null
        ], 201);
    }

    public function login()
    {
        $phone = $this->request->getVar('phone');
        $throttler = \Config\Services::throttler();
        $rateLimitKey = 'login-merchant-' . md5($this->request->getIPAddress() . '_' . ($phone ?? ''));
        if ($throttler->check($rateLimitKey, 10, MINUTE) === false) {
            return $this->respond([
                'status' => 429,
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.',
                'data' => null
            ], 429);
        }

        $rules = ['phone' => 'required', 'password' => 'required'];
        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Input tidak lengkap', 'data' => null], 400);
        }

        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->where('phone', $phone)->first();

        if (!$merchant || !password_verify($this->request->getVar('password'), $merchant['password'])) {
            return $this->respond(['status' => 401, 'message' => 'Nomor HP atau kata sandi salah', 'data' => null], 401);
        }

        $key = getenv('JWT_SECRET_KEY') ?: 'maijek_jwt_default_secret_key_change_in_production';
        $payload = [
            'iat'  => time(),
            'exp'  => time() + (3600 * 24 * 30),
            'uid'  => $merchant['id'],
            'role' => 'merchant'
        ];
        $token = JWT::encode($payload, $key, 'HS256');

        unset($merchant['password']);

        return $this->respond([
            'status' => 200,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'merchant'  => $merchant
            ]
        ], 200);
    }
}
