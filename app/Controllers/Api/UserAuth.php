<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use Firebase\JWT\JWT;

class UserAuth extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $rules = [
            'name'     => 'required|min_length[3]',
            'email'    => 'permit_empty|valid_email|is_unique[users.email]'
        ];

        $messages = [
            'name' => [
                'required' => 'Nama lengkap wajib diisi.',
                'min_length' => 'Nama lengkap minimal 3 karakter.'
            ],
            'email' => [
                'valid_email' => 'Format alamat email tidak valid.',
                'is_unique' => 'Alamat email ini sudah terdaftar. Gunakan email lain.'
            ]
        ];

        // Rate limit: Mencegah bot spam pendaftaran / OTP Flooding (maks 5 request per IP per menit)
        $throttler = \Config\Services::throttler();
        $ip = $this->request->getIPAddress();
        if ($throttler->check('reg_ip_' . md5($ip), 5, 60) === false) {
            return $this->respond([
                'status' => 429,
                'message' => 'Terlalu banyak permintaan pendaftaran dari perangkat/jaringan Anda. Silakan coba lagi dalam 1 menit.',
                'data' => null
            ], 429);
        }

        if (!$this->validate($rules, $messages)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            return $this->respond([
                'status' => 400,
                'message' => $firstError ?: 'Validasi gagal',
                'data' => $errors
            ], 400);
        }

        $rawPhone = (string)$this->request->getVar('phone');
        $phone = $this->normalizePhone($rawPhone);
        if (empty($phone) || strlen($phone) < 9 || strlen($phone) > 16) {
            return $this->respond([
                'status' => 400,
                'message' => 'Nomor handphone tidak valid (minimal 9 digit).',
                'data' => ['phone' => 'Nomor handphone tidak valid.']
            ], 400);
        }

        $userModel = new UserModel();
        $existing = $this->findUserByPhone($phone, $userModel);
        if ($existing) {
            return $this->respond([
                'status' => 400,
                'message' => 'Nomor handphone ini sudah terdaftar. Silakan masuk.',
                'data' => ['phone' => 'Nomor handphone ini sudah terdaftar. Silakan masuk.']
            ], 400);
        }

        $deviceId = trim((string)$this->request->getVar('device_id'));
        $ignoredDeviceIds = ['unknown_device_id', 'unknown', 'unknown_ios_id', '', 'null'];

        if (!empty($deviceId) && !in_array($deviceId, $ignoredDeviceIds) && strlen($deviceId) >= 6) {
            // Hanya hitung akun yang BENAR-BENAR sudah terverifikasi OTP (is_verified = 1).
            // Batas wajar: Maksimal 5 akun per perangkat.
            $deviceCount = $userModel->where('device_id', $deviceId)->where('is_verified', 1)->countAllResults();
            if ($deviceCount >= 5) {
                return $this->respond([
                    'status' => 403,
                    'message' => 'Perangkat ini sudah mencapai batas pendaftaran akun (maks. 5 akun per perangkat).',
                    'data' => null
                ], 403);
            }
        }

        // Cek apakah ada file foto profil yang diunggah
        $photoName = null;
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->guessExtension() ?? $file->getExtension() ?? '');
            $mime = $file->getMimeType();
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Format file foto tidak valid. Gunakan file gambar JPG, PNG, atau WEBP.',
                    'data' => null
                ], 400);
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Ukuran foto profil maksimal 10MB.',
                    'data' => null
                ], 400);
            }

            $uploadDir = ROOTPATH . 'public/uploads/profiles';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $photoName = $file->getRandomName();
            $file->move($uploadDir, $photoName);
        }

        $isAppleReviewer = $this->isAppleReviewerPhone($phone);
        $otpCode = $isAppleReviewer ? '123456' : (string) random_int(100000, 999999);
        $otpExpiredAt = $isAppleReviewer ? date('Y-m-d H:i:s', strtotime('+1 year')) : date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $email = trim((string)$this->request->getVar('email'));

        $userModel->insert([
            'name'     => trim((string)$this->request->getVar('name')),
            'phone'    => $phone,
            'email'    => !empty($email) ? $email : null,
            'photo'    => $photoName,
            'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT), // Random password placeholder
            'device_id' => $deviceId,
            'is_verified' => $isAppleReviewer ? 1 : 0,
            'otp_code' => $otpCode,
            'otp_expired_at' => $otpExpiredAt
        ]);

        if (!$isAppleReviewer) {
            helper('bablast');
            send_whatsapp_otp($phone, $otpCode);
        }

        return $this->respond([
            'status' => 201,
            'message' => 'Pendaftaran berhasil. Silakan verifikasi OTP.',
            'data' => ['phone' => $phone]
        ], 201);
    }

    public function login()
    {
        $rules = ['phone' => 'required'];
        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal', 'data' => $this->validator->getErrors()], 400);
        }

        $userModel = new UserModel();
        $user = $this->findUserByPhone($this->request->getVar('phone'), $userModel);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'Nomor HP tidak terdaftar', 'data' => null], 404);
        }

        if (isset($user['status']) && $user['status'] === 'suspended') {
            return $this->respond([
                'status' => 403,
                'message' => 'Akun Anda sedang disuspend oleh Admin karena pelanggaran kebijakan. Silakan hubungi Customer Service.',
                'data' => null
            ], 403);
        }

        $deviceId = trim((string)$this->request->getVar('device_id'));
        $ignoredDeviceIds = ['unknown_device_id', 'unknown', 'unknown_ios_id', '', 'null'];
        $isAppleReviewer = $this->isAppleReviewerPhone($user['phone']);

        if (!$isAppleReviewer) {
            if (!empty($deviceId) && !in_array($deviceId, $ignoredDeviceIds) && strlen($deviceId) >= 6 && (!isset($user['device_id']) || $user['device_id'] !== $deviceId)) {
                $userModel = new UserModel();
                $deviceCount = $userModel->where('device_id', $deviceId)->where('is_verified', 1)->countAllResults();
                if ($deviceCount >= 5) {
                    return $this->respond([
                        'status' => 403,
                        'message' => 'Perangkat ini sudah mencapai batas penggunaan akun (maks. 5 akun per perangkat).',
                        'data' => null
                    ], 403);
                }
            }
            // Rate limit: Mencegah OTP Bombing (Batas aman: 8x per 30 menit)
            $throttler = \Config\Services::throttler();
            if ($throttler->check('otp_limit_' . $user['phone'], 8, 1800) === false) {
                return $this->respond(['status' => 429, 'message' => 'Terlalu banyak permintaan OTP. Demi keamanan, silakan coba lagi dalam 15 menit.', 'data' => null], 429);
            }

            if ($user['otp_expired_at']) {
                $timeSinceLastOtp = time() - (strtotime($user['otp_expired_at']) - 300);
                if ($timeSinceLastOtp < 60) {
                    $waitSec = 60 - $timeSinceLastOtp;
                    return $this->respond(['status' => 429, 'message' => "Terlalu cepat. Tunggu {$waitSec} detik sebelum meminta OTP baru.", 'data' => null], 429);
                }
            }

            $otpCode = (string) random_int(100000, 999999);
            $otpExpiredAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            $userModel->update($user['id'], [
                'otp_code' => $otpCode,
                'otp_expired_at' => $otpExpiredAt
            ]);

            helper('bablast');
            send_whatsapp_otp($user['phone'], $otpCode);
        } else {
            // Bypass untuk Akun Demo Apple Reviewer: Set static OTP 123456 dan lewati kirim WA
            $userModel->update($user['id'], [
                'otp_code' => '123456',
                'otp_expired_at' => date('Y-m-d H:i:s', strtotime('+1 year'))
            ]);
        }

        return $this->respond(['status' => 403, 'message' => 'OTP telah dikirim ke WhatsApp Anda.', 'data' => ['requires_otp' => true]], 403);
    }

    public function verifyOtp()
    {
        $rules = ['phone' => 'required', 'otp_code' => 'required'];
        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Input tidak lengkap', 'data' => null], 400);
        }

        $userModel = new UserModel();
        $user = $this->findUserByPhone($this->request->getVar('phone'), $userModel);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'Pengguna tidak ditemukan', 'data' => null], 404);
        }

        if (isset($user['status']) && $user['status'] === 'suspended') {
            return $this->respond([
                'status' => 403,
                'message' => 'Akun Anda sedang disuspend oleh Admin karena pelanggaran kebijakan. Silakan hubungi Customer Service.',
                'data' => null
            ], 403);
        }

        $inputOtp = trim((string)$this->request->getVar('otp_code'));
        $isAppleReviewer = $this->isAppleReviewerPhone($user['phone']);

        if ($isAppleReviewer) {
            // Reviewer Apple selalu lolos dengan kode statis 123456
            if ($inputOtp !== '123456') {
                return $this->respond(['status' => 400, 'message' => 'Kode OTP salah', 'data' => null], 400);
            }
        } else {
            // Anti Brute-Force: Maksimal 5 percobaan verifikasi per 15 menit
            $throttler = \Config\Services::throttler();
            $verifyKey = 'otp_verify_' . md5($user['phone']);
            if ($throttler->check($verifyKey, 5, 900) === false) {
                // Hanguskan kode OTP jika terindikasi serangan tebak kode (brute-force)
                $userModel->update($user['id'], [
                    'otp_code' => null,
                    'otp_expired_at' => null
                ]);
                return $this->respond([
                    'status' => 429,
                    'message' => 'Terlalu banyak percobaan kode OTP yang salah. Kode OTP dibatalkan demi keamanan. Silakan minta OTP baru.',
                    'data' => null
                ], 429);
            }

            if (empty($user['otp_code']) || !hash_equals((string)$user['otp_code'], $inputOtp)) {
                return $this->respond(['status' => 400, 'message' => 'Kode OTP salah', 'data' => null], 400);
            }

            if (strtotime($user['otp_expired_at']) < time()) {
                return $this->respond(['status' => 400, 'message' => 'Kode OTP sudah kedaluwarsa', 'data' => null], 400);
            }
        }

        // Update device_id after successful verification
        $deviceId = $this->request->getVar('device_id');
        $updateData = [
            'is_verified' => 1,
            'otp_code' => $isAppleReviewer ? '123456' : null,
            'otp_expired_at' => $isAppleReviewer ? date('Y-m-d H:i:s', strtotime('+1 year')) : null
        ];
        if (!empty($deviceId)) {
            $updateData['device_id'] = $deviceId;
        }

        $userModel->update($user['id'], $updateData);

        $key = getenv('JWT_SECRET_KEY');
        $payload = [
            'iat'  => time(),
            'exp'  => time() + (3600 * 24 * 30),
            'uid'  => $user['id'],
            'role' => 'user'
        ];
        $token = JWT::encode($payload, $key, 'HS256');

        $latestUser = $userModel->find($user['id']);
        $photoUrl = '';
        if (!empty($latestUser['photo'])) {
            $photoUrl = (strpos($latestUser['photo'], 'http') === 0)
                ? $latestUser['photo']
                : base_url('uploads/profiles/' . $latestUser['photo']);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Verifikasi berhasil',
            'data' => [
                'token' => $token,
                'user'  => [
                    'id'        => $latestUser['id'],
                    'name'      => $latestUser['name'],
                    'phone'     => $latestUser['phone'],
                    'email'     => $latestUser['email'] ?? '',
                    'photo_url' => $photoUrl,
                    'balance'   => $latestUser['balance'],
                    'coins'     => $latestUser['coins'] ?? 0
                ]
            ]
        ], 200);
    }

    public function resendOtp()
    {
        $rules = ['phone' => 'required'];
        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Nomor HP diperlukan', 'data' => null], 400);
        }

        $userModel = new UserModel();
        $user = $this->findUserByPhone($this->request->getVar('phone'), $userModel);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'Pengguna tidak ditemukan', 'data' => null], 404);
        }

        $isAppleReviewer = $this->isAppleReviewerPhone($user['phone']);
        if ($isAppleReviewer) {
            $userModel->update($user['id'], [
                'otp_code' => '123456',
                'otp_expired_at' => date('Y-m-d H:i:s', strtotime('+1 year'))
            ]);
            return $this->respond(['status' => 200, 'message' => 'OTP baru telah dikirim', 'data' => null], 200);
        }

        $throttler = \Config\Services::throttler();
        if ($throttler->check('otp_limit_' . $user['phone'], 8, 1800) === false) {
            return $this->respond(['status' => 429, 'message' => 'Terlalu banyak permintaan OTP. Demi keamanan, silakan coba lagi dalam 15 menit.', 'data' => null], 429);
        }

        if ($user['otp_expired_at']) {
            $timeSinceLastOtp = time() - (strtotime($user['otp_expired_at']) - 300);
            if ($timeSinceLastOtp < 60) {
                $waitSec = 60 - $timeSinceLastOtp;
                return $this->respond(['status' => 429, 'message' => "Terlalu cepat. Tunggu {$waitSec} detik sebelum meminta OTP baru.", 'data' => null], 429);
            }
        }

        $otpCode = (string) random_int(100000, 999999);
        $otpExpiredAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $userModel->update($user['id'], [
            'otp_code' => $otpCode,
            'otp_expired_at' => $otpExpiredAt
        ]);

        helper('bablast');
        send_whatsapp_otp($user['phone'], $otpCode);

        return $this->respond(['status' => 200, 'message' => 'OTP baru telah dikirim', 'data' => null], 200);
    }

    private function isAppleReviewerPhone($phone)
    {
        $normalized = $this->normalizePhone($phone);
        $reviewerPhones = [
            '08124148559',
        ];
        return in_array($normalized, $reviewerPhones, true);
    }

    private function normalizePhone($phone)
    {
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (str_starts_with($clean, '62')) {
            return '0' . substr($clean, 2);
        } elseif (str_starts_with($clean, '8')) {
            return '0' . $clean;
        }
        return $clean;
    }

    private function findUserByPhone($rawPhone, UserModel $userModel)
    {
        $phone = $this->normalizePhone($rawPhone);
        $altPhone = str_starts_with($phone, '0') ? substr($phone, 1) : $phone;
        $intlPhone = str_starts_with($phone, '0') ? ('62' . substr($phone, 1)) : ('62' . $phone);

        return $userModel->groupStart()
            ->where('phone', $phone)
            ->orWhere('phone', $altPhone)
            ->orWhere('phone', $intlPhone)
            ->orWhere('phone', (string)$rawPhone)
            ->groupEnd()
            ->first();
    }
}
