<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        if (empty($header)) {
            $header = $request->getServer('HTTP_AUTHORIZATION') ?: $request->getServer('REDIRECT_HTTP_AUTHORIZATION');
        }
        $token = null;

        // extract token from header "Bearer <token>"
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/i', $header, $matches)) {
                $token = $matches[1];
            }
        }

        // Jika tidak ada di header, coba ambil dari query parameter (berguna untuk fitur unduh file/browser)
        if (empty($token) && $request->getGet('token')) {
            $token = $request->getGet('token');
        }

        // Token is empty
        if (is_null($token) || empty($token)) {
            $response = service('response');
            $response->setJSON(['error' => 'Access denied. Token is required.']);
            $response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            return $response;
        }

        // Verify Token with all known keys
        $decoded = null;
        $keysToTry = array_unique(array_filter([
            env('JWT_SECRET_KEY'),
            getenv('JWT_SECRET_KEY'),
            'maiJekSuperSecretKey2026!OjekOnline',
            'maijek_jwt_default_secret_key_change_in_production'
        ]));

        foreach ($keysToTry as $k) {
            try {
                $decoded = JWT::decode($token, new Key($k, 'HS256'));
                if ($decoded) {
                    break;
                }
            } catch (Exception $ex) {
                // coba key berikutnya
            }
        }

        if (!$decoded) {
            $response = service('response');
            $response->setJSON([
                'status' => 401,
                'message' => 'Token tidak valid atau sudah kedaluwarsa. Silakan login ulang.',
                'data' => null
            ]);
            $response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            return $response;
        }

        // Periksa role jika diberikan di routes (misal: filter => jwt:driver)
        if (!empty($arguments) && isset($decoded->role)) {
            $requiredRole = $arguments[0];
            if ($decoded->role !== $requiredRole) {
                $response = service('response');
                $response->setJSON([
                    'status' => 403,
                    'message' => 'Akses ditolak. Peran tidak sesuai.',
                    'data' => null
                ]);
                $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
                return $response;
            }
        }

        // Simpan payload ke global atau request agar bisa dibaca controller
        $_SERVER['jwt_payload'] = (array) $decoded;

        // Periksa apakah akun sedang disuspend oleh Admin (Aman dari kegagalan DB)
        try {
            if (isset($decoded->role) && isset($decoded->uid)) {
                $db = \Config\Database::connect();
                $table = ($decoded->role === 'driver') ? 'drivers' : (($decoded->role === 'user') ? 'users' : null);
                if ($table && $db->fieldExists('status', $table)) {
                    $account = $db->table($table)->select('status')->where('id', $decoded->uid)->get()->getRow();
                    if ($account && isset($account->status) && $account->status === 'suspended') {
                        $response = service('response');
                        $response->setJSON([
                            'status' => 403,
                            'message' => 'Akun Anda sedang disuspend oleh Admin karena pelanggaran kebijakan. Silakan hubungi Customer Service.',
                            'data' => null
                        ]);
                        $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
                        return $response;
                    }
                }
            }
        } catch (\Throwable $dbEx) {
            // Abaikan jika database kolom status belum ada di server hosting
            log_message('error', 'JwtFilter check suspend error: ' . $dbEx->getMessage());
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
