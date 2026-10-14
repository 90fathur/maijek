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
        $key = getenv('JWT_SECRET_KEY');
        $header = $request->getServer('HTTP_AUTHORIZATION');
        $token = null;

        // extract token from header "Bearer <token>"
        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
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

        try {
            // Verify Token
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $ex) {
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
