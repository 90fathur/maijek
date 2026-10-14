<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DriverModel;
use Firebase\JWT\JWT;

class DriverAuth extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $rules = [
            'name'           => 'required|min_length[3]',
            'phone'          => 'required|is_unique[drivers.phone]',
            'password'       => 'required|min_length[6]',
            'vehicle_plate'  => 'required',
            'vehicle_class_id' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => 400,
                'message' => 'Validasi gagal',
                'data' => $this->validator->getErrors()
            ], 400);
        }

        $db = \Config\Database::connect();
        try {
            if (!$db->fieldExists('vehicle_brand', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_brand VARCHAR(100) NULL AFTER vehicle_plate");
            }
            if (!$db->fieldExists('vehicle_capacity', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_capacity INT DEFAULT 4 AFTER vehicle_brand");
            }
        } catch (\Throwable $e) {
        }

        $isCar = ($this->request->getVar('vehicle_class_id') == 2);
        $vehicleBrand = $this->request->getVar('vehicle_brand');
        if (empty($vehicleBrand)) {
            $vehicleBrand = $isCar ? 'Toyota Avanza' : 'Honda Beat';
        }
        $vehicleCapacity = (int)($this->request->getVar('vehicle_capacity') ?? ($isCar ? 4 : 1));
        if ($vehicleCapacity <= 0) {
            $vehicleCapacity = $isCar ? 4 : 1;
        }

        $driverModel = new DriverModel();
        $driverModel->insert([
            'name'             => $this->request->getVar('name'),
            'phone'            => $this->request->getVar('phone'),
            'password'         => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'vehicle_plate'    => strtoupper(trim((string)$this->request->getVar('vehicle_plate'))),
            'vehicle_brand'    => trim((string)$vehicleBrand),
            'vehicle_capacity' => $vehicleCapacity,
            'vehicle_type'     => $isCar ? 'mobil' : 'motor',
            'status'           => 'inactive', // Harus di-approve admin
            'verification_status' => 'unverified',
            'balance'          => 0
        ]);

        return $this->respond([
            'status' => 201,
            'message' => 'Pendaftaran pengemudi berhasil. Silakan lengkapi dokumen KYC Anda.',
            'data' => null
        ], 201);
    }

    public function login()
    {
        $rules = ['phone' => 'required', 'password' => 'required'];
        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Input tidak lengkap', 'data' => null], 400);
        }

        // Anti Brute-Force: Maks 10 percobaan login per menit per IP
        $throttler = \Config\Services::throttler();
        $ip = $this->request->getIPAddress();
        if ($throttler->check('driver_login_' . md5($ip), 10, 60) === false) {
            return $this->respond(['status' => 429, 'message' => 'Terlalu banyak percobaan login. Silakan tunggu 1 menit.', 'data' => null], 429);
        }

        $driverModel = new DriverModel();
        $driver = $driverModel->where('phone', $this->request->getVar('phone'))->first();

        if (!$driver || !password_verify($this->request->getVar('password'), $driver['password'])) {
            return $this->respond(['status' => 401, 'message' => 'Nomor HP atau kata sandi salah', 'data' => null], 401);
        }

        if (isset($driver['status']) && $driver['status'] === 'suspended') {
            return $this->respond(['status' => 403, 'message' => 'Akun pengemudi Anda sedang disuspend oleh Admin. Silakan hubungi kantor operasional.', 'data' => null], 403);
        }

        $key = getenv('JWT_SECRET_KEY');
        // Token berlaku 30 hari
        $payload = [
            'iat'  => time(),
            'exp'  => time() + (3600 * 24 * 30),
            'uid'  => $driver['id'],
            'role' => 'driver'
        ];
        $token = JWT::encode($payload, $key, 'HS256');

        return $this->respond([
            'status' => 200,
            'message' => 'Login berhasil',
            'data' => [
                'token'  => $token,
                'driver' => [
                    'id'                  => $driver['id'],
                    'name'                => $driver['name'],
                    'phone'               => $driver['phone'],
                    'status'              => $driver['status'], // active, pending, suspended
                    'verification_status' => $driver['verification_status'] ?? 'unverified',
                    'photo'               => !empty($driver['photo']) ? (strpos($driver['photo'], 'http') === 0 ? $driver['photo'] : base_url($driver['photo'])) : '',
                    'vehicle_plate'       => $driver['vehicle_plate'] ?? '',
                    'vehicle_type'        => $driver['vehicle_type'] ?? 'motor',
                    'vehicle_brand'       => $driver['vehicle_brand'] ?? '',
                    'vehicle_capacity'    => (int)($driver['vehicle_capacity'] ?? 1),
                    'balance'             => (int)($driver['balance'] ?? 0),
                    'coins'               => (int)($driver['coins'] ?? 0),
                    'rating'              => (float)($driver['rating'] ?? 5.0),
                    'qris_image'          => !empty($driver['qris_image']) ? (strpos($driver['qris_image'], 'http') === 0 ? $driver['qris_image'] : base_url($driver['qris_image'])) : '',
                    'qris_merchant_name'  => $driver['qris_merchant_name'] ?? '',
                    'qris_is_active'      => (int)($driver['qris_is_active'] ?? 0),
                    'qris_status'         => $driver['qris_status'] ?? 'unverified',
                ]
            ]
        ], 200);
    }
}
