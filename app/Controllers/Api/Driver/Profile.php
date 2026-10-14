<?php

namespace App\Controllers\Api\Driver;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DriverModel;

class Profile extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Cache-Control', 'post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');

        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();

        $driver = $driverModel->find($payload['uid']);

        if (!$driver) return $this->respond(['status' => 404, 'message' => 'Driver tidak ditemukan'], 404);

        unset($driver['password']); // Keamanan

        // Hitung total orderan yang sudah diselesaikan (Semua Layanan)
        $db = \Config\Database::connect();

        $completedRide = $db->table('orders')->where('driver_id', $payload['uid'])->where('status', 'completed')->countAllResults();
        $completedFood = $db->table('food_orders')->where('driver_id', $payload['uid'])->where('status', 'completed')->countAllResults();

        $completedOrders = $completedRide + $completedFood;

        // Hitung rata-rata rating (Semua Layanan)
        $ratingRideQuery = $db->table('orders')->selectAvg('rating')->where('driver_id', $payload['uid'])->where('status', 'completed')->where('rating >', 0)->get()->getRow();
        $ratingRide = $ratingRideQuery ? (float)$ratingRideQuery->rating : 0;
        $countRide = $db->table('orders')->where('driver_id', $payload['uid'])->where('status', 'completed')->where('rating >', 0)->countAllResults();

        $ratingFoodQuery = $db->table('food_orders')->selectAvg('driver_rating')->where('driver_id', $payload['uid'])->where('status', 'completed')->where('driver_rating >', 0)->get()->getRow();
        $ratingFood = $ratingFoodQuery ? (float)$ratingFoodQuery->driver_rating : 0;
        $countFood = $db->table('food_orders')->where('driver_id', $payload['uid'])->where('status', 'completed')->where('driver_rating >', 0)->countAllResults();

        $totalRatingCount = $countRide + $countFood;
        $performance = 100; // Default 100%
        if ($totalRatingCount > 0) {
            $avgRating = (($ratingRide * $countRide) + ($ratingFood * $countFood)) / $totalRatingCount;
            $performance = round(($avgRating / 5) * 100);
        }

        // --- PENALTI SKIP ---
        $todayStr = date('Y-m-d');
        if (isset($driver['last_skip_date']) && $driver['last_skip_date'] != $todayStr) {
            $driver['daily_skip_count'] = 0;
            try {
                $driverModel->update($driver['id'], ['daily_skip_count' => 0, 'last_skip_date' => $todayStr]);
            } catch (\Exception $e) {
                // Ignore if columns don't exist
            }
        }

        $isCar = in_array(strtolower($driver['vehicle_type'] ?? ''), ['mobil', 'car', 'car_premium']);
        if (empty($driver['vehicle_brand'])) {
            $driver['vehicle_brand'] = $isCar ? 'Toyota Avanza' : 'Honda Beat';
        }
        if (empty($driver['vehicle_capacity'])) {
            $driver['vehicle_capacity'] = $isCar ? 4 : 1;
        }

        $settingModel = new \App\Models\SettingModel();
        $skipPenaltySetting = $settingModel->where('setting_key', 'driver_skip_penalty')->first();
        $skipPenaltyRate = $skipPenaltySetting ? (float)$skipPenaltySetting['setting_value'] : 2;

        $dailySkipCount = isset($driver['daily_skip_count']) ? (int)$driver['daily_skip_count'] : 0;
        $penalty = $dailySkipCount * $skipPenaltyRate;
        $performance -= $penalty;
        if ($performance < 0) $performance = 0;
        if ($performance > 100) $performance = 100;
        // --------------------

        $driver['total_orders'] = $completedOrders;
        $driver['performance'] = $performance;

        // --- LEVEL DRIVER ---
        $driverLevel = 'Junior';
        $levelTarget = 100;

        if ($completedOrders > 1000) {
            $driverLevel = 'Legend';
            $levelTarget = 0; // max level
        } elseif ($completedOrders > 500) {
            $driverLevel = 'Master';
            $levelTarget = 1000;
        } elseif ($completedOrders > 100) {
            $driverLevel = 'Senior';
            $levelTarget = 500;
        }

        $driver['driver_level'] = $driverLevel;
        $driver['next_level_target'] = $levelTarget;
        // --------------------

        // Hitung Pendapatan Hari Ini dan Order Hari Ini (Semua Layanan)
        $todayStr = date('Y-m-d');
        $yesterdayStr = date('Y-m-d', strtotime('-1 day'));
        $thisMonthStr = date('Y-m');

        $todayStart = $todayStr . ' 00:00:00';
        $todayEnd = $todayStr . ' 23:59:59';

        $yesterdayStart = $yesterdayStr . ' 00:00:00';
        $yesterdayEnd = $yesterdayStr . ' 23:59:59';

        $monthStart = $thisMonthStr . '-01 00:00:00';
        $monthEnd = date('Y-m-t', strtotime($thisMonthStr . '-01')) . ' 23:59:59';

        $hasDiscountCol = $db->fieldExists('discount_amount', 'orders');
        $orderPriceExpr = $hasDiscountCol ? "SUM(price + COALESCE(discount_amount, 0))" : "SUM(price)";

        // Hari ini
        $todayRideQuery = $db->query("SELECT {$orderPriceExpr} as earnings, COUNT(id) as count FROM orders WHERE driver_id = ? AND status = 'completed' AND created_at >= ? AND created_at <= ?", [$payload['uid'], $todayStart, $todayEnd])->getRow();
        $todayFoodQuery = $db->query("SELECT SUM(delivery_fee) as earnings, COUNT(id) as count FROM food_orders WHERE driver_id = ? AND status = 'completed' AND created_at >= ? AND created_at <= ?", [$payload['uid'], $todayStart, $todayEnd])->getRow();

        $driver['today_earnings'] = ($todayRideQuery ? (int)$todayRideQuery->earnings : 0) + ($todayFoodQuery ? (int)$todayFoodQuery->earnings : 0);
        $driver['today_orders'] = ($todayRideQuery ? (int)$todayRideQuery->count : 0) + ($todayFoodQuery ? (int)$todayFoodQuery->count : 0);

        // Kemarin
        $yesterdayRideQuery = $db->query("SELECT {$orderPriceExpr} as earnings FROM orders WHERE driver_id = ? AND status = 'completed' AND created_at >= ? AND created_at <= ?", [$payload['uid'], $yesterdayStart, $yesterdayEnd])->getRow();
        $yesterdayFoodQuery = $db->query("SELECT SUM(delivery_fee) as earnings FROM food_orders WHERE driver_id = ? AND status = 'completed' AND created_at >= ? AND created_at <= ?", [$payload['uid'], $yesterdayStart, $yesterdayEnd])->getRow();
        $driver['yesterday_earnings'] = ($yesterdayRideQuery ? (int)$yesterdayRideQuery->earnings : 0) + ($yesterdayFoodQuery ? (int)$yesterdayFoodQuery->earnings : 0);

        // Bulan ini
        $monthRideQuery = $db->query("SELECT {$orderPriceExpr} as earnings FROM orders WHERE driver_id = ? AND status = 'completed' AND created_at >= ? AND created_at <= ?", [$payload['uid'], $monthStart, $monthEnd])->getRow();
        $monthFoodQuery = $db->query("SELECT SUM(delivery_fee) as earnings FROM food_orders WHERE driver_id = ? AND status = 'completed' AND created_at >= ? AND created_at <= ?", [$payload['uid'], $monthStart, $monthEnd])->getRow();
        $driver['this_month_earnings'] = ($monthRideQuery ? (int)$monthRideQuery->earnings : 0) + ($monthFoodQuery ? (int)$monthFoodQuery->earnings : 0);

        // Jadikan path gambar menjadi URL penuh
        $imageFields = ['photo', 'ktp_image', 'sim_image', 'stnk_image', 'vehicle_image', 'qris_image'];
        foreach ($imageFields as $field) {
            if (!empty($driver[$field]) && strpos($driver[$field], 'http') !== 0) {
                $driver[$field] = base_url($driver[$field]);
            }
        }
        $driver['qris_image_url'] = !empty($driver['qris_image']) ? $driver['qris_image'] : null;
        $driver['qris_is_active'] = (int)($driver['qris_is_active'] ?? 0);
        $driver['qris_merchant_name'] = $driver['qris_merchant_name'] ?? '';
        $driver['qris_status'] = $driver['qris_status'] ?? 'verified';
        $driver['coins'] = (int)($driver['coins'] ?? 0);

        return $this->response->setJSON([
            'status' => 200,
            'message' => 'Profil pengemudi',
            'data' => $driver
        ])
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }

    public function updateFcm()
    {
        $payload = $_SERVER['jwt_payload'];
        $json = $this->request->getJSON(true) ?? [];
        $fcmToken = $json['fcm_token'] ?? $this->request->getVar('fcm_token');

        if (empty($fcmToken)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal: fcm_token diperlukan'], 400);
        }

        $db = \Config\Database::connect();
        // Hapus token ini dari akun driver lain jika pernah login di perangkat fisik yang sama
        $db->table('drivers')->where('fcm_token', $fcmToken)->where('id !=', $payload['uid'])->update(['fcm_token' => null]);

        $driverModel = new DriverModel();
        $driverModel->update($payload['uid'], [
            'fcm_token' => $fcmToken
        ]);

        return $this->respond(['status' => 200, 'message' => 'FCM Token berhasil diperbarui']);
    }

    public function updateLocation()
    {
        $payload = $_SERVER['jwt_payload'];

        // Mendukung format 'latitude' & 'longitude' (dari APK Driver) serta 'lat' & 'lng'
        $lat = $this->request->getVar('latitude') ?? $this->request->getVar('lat');
        $lng = $this->request->getVar('longitude') ?? $this->request->getVar('lng');

        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return $this->respond(['status' => 400, 'message' => 'Kordinat latitude & longitude wajib diisi'], 400);
        }

        $driverModel = new DriverModel();

        $updateData = [
            'latitude' => (float)$lat,
            'longitude' => (float)$lng,
        ];

        $db = \Config\Database::connect();
        try {
            if ($db->fieldExists('last_heartbeat_at', 'drivers')) {
                $updateData['last_heartbeat_at'] = date('Y-m-d H:i:s');
            }
        } catch (\Throwable $e) {
        }

        $driverModel->update($payload['uid'], $updateData);

        return $this->respond(['status' => 200, 'message' => 'Lokasi diperbarui', 'data' => null]);
    }

    public function toggleStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();
        $driver = $driverModel->find($payload['uid']);

        // Mengecek apakah request adalah untuk offline
        $isOffline = false;
        if ($this->request->getVar('is_offline')) {
            $isOffline = true;
        } elseif ($this->request->getVar('status') === 'offline') {
            $isOffline = true;
        }

        // Jika mencoba online tapi belum diverifikasi admin
        if (!$isOffline && $driver['verification_status'] !== 'approved') {
            return $this->respond([
                'status' => 403,
                'message' => 'Akun Anda belum diverifikasi oleh Admin. Anda tidak bisa Online.'
            ], 403);
        }

        $newStatus = $isOffline ? 0 : 1;
        $updateData = ['is_online' => $newStatus];

        $db = \Config\Database::connect();
        try {
            if ($db->fieldExists('last_heartbeat_at', 'drivers')) {
                // Jika offline, hapus heartbeat agar tidak terkena radar/zombie filter
                $updateData['last_heartbeat_at'] = $isOffline ? null : date('Y-m-d H:i:s');
            }
        } catch (\Throwable $e) {
        }

        $driverModel->update($payload['uid'], $updateData);

        return $this->respond([
            'status' => 200,
            'message' => $newStatus ? 'Anda sekarang Online' : 'Anda sekarang Offline',
            'data' => null
        ]);
    }

    public function kyc()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();

        $rules = [
            'photo' => 'uploaded[photo]|max_size[photo,10240]',
            'ktp_image' => 'uploaded[ktp_image]|max_size[ktp_image,10240]',
            'sim_image' => 'uploaded[sim_image]|max_size[sim_image,10240]',
            'stnk_image' => 'uploaded[stnk_image]|max_size[stnk_image,10240]',
            'vehicle_image' => 'uploaded[vehicle_image]|max_size[vehicle_image,10240]'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => $this->validator->getErrors()], 400);
        }

        if (!is_dir(FCPATH . 'uploads/kyc')) {
            mkdir(FCPATH . 'uploads/kyc', 0777, true);
        }
        if (!is_dir(FCPATH . 'uploads/profiles')) {
            mkdir(FCPATH . 'uploads/profiles', 0755, true);
        }
        if (!is_dir(FCPATH . 'uploads/kyc')) {
            mkdir(FCPATH . 'uploads/kyc', 0755, true);
        }

        $fieldsToUpdate = ['verification_status' => 'pending'];

        $files = [
            'photo' => 'uploads/profiles',
            'ktp_image' => 'uploads/kyc',
            'sim_image' => 'uploads/kyc',
            'stnk_image' => 'uploads/kyc',
            'vehicle_image' => 'uploads/kyc',
        ];

        foreach ($files as $field => $folder) {
            $file = $this->request->getFile($field);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                if ($file->getSize() > 10 * 1024 * 1024) {
                    return $this->respond(['status' => 400, 'message' => "Ukuran file {$field} melebihi batas 10MB."], 400);
                }

                $safeExt = $this->determineSafeFileExt($file, ['jpg', 'jpeg', 'png', 'webp', 'pdf']);
                if (!$safeExt) {
                    return $this->respond(['status' => 400, 'message' => "Format file {$field} tidak diizinkan. Hanya JPG, PNG, WEBP, atau PDF."], 400);
                }

                $newName = uniqid($field . '_') . '.' . $safeExt;

                $file->move(FCPATH . $folder, $newName);
                $fieldsToUpdate[$field] = $folder . '/' . $newName;
            } else {
                return $this->respond(['status' => 400, 'message' => 'Gagal mengunggah file atau file wajib diisi: ' . $field], 400);
            }
        }

        $driverModel->update($payload['uid'], $fieldsToUpdate);

        return $this->respond(['status' => 200, 'message' => 'Semua dokumen KYC berhasil diunggah', 'data' => ['verification_status' => 'pending']]);
    }

    public function updatePhoto()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();

        $file = $this->request->getFile('photo');
        if (!$file || !$file->isValid()) {
            $err = $file ? $file->getErrorString() : 'File foto tidak ditemukan';
            return $this->respond(['status' => 400, 'message' => 'Gagal mengunggah foto: ' . $err], 400);
        }

        if (!is_dir(FCPATH . 'uploads/profiles')) {
            mkdir(FCPATH . 'uploads/profiles', 0777, true);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->respond(['status' => 400, 'message' => 'Ukuran foto profil melebihi 10MB.'], 400);
        }

        $safeExt = $this->determineSafeFileExt($file, ['jpg', 'jpeg', 'png', 'webp']);
        if (!$safeExt) {
            return $this->respond(['status' => 400, 'message' => 'Format file foto tidak diizinkan. Hanya JPG, PNG, atau WEBP.'], 400);
        }

        $newName = uniqid('profile_') . '.' . $safeExt;

        $file->move(FCPATH . 'uploads/profiles', $newName);
        $photoPath = 'uploads/profiles/' . $newName;

        $driverModel->update($payload['uid'], [
            'photo' => $photoPath
        ]);

        return $this->respond([
            'status' => 200,
            'message' => 'Foto profil berhasil diperbarui',
            'data' => [
                'photo' => base_url($photoPath)
            ]
        ]);
    }
    public function reviews()
    {
        $payload = $_SERVER['jwt_payload'];
        $db = \Config\Database::connect();

        $reviews = $db->table('orders')
            ->select('orders.rating, orders.review_comment, orders.created_at, users.name as customer_name')
            ->join('users', 'users.id = orders.user_id')
            ->where('orders.driver_id', $payload['uid'])
            ->where('orders.status', 'completed')
            ->where('orders.rating >', 0)
            ->orderBy('orders.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // Samarkan nama pelanggan untuk privasi
        foreach ($reviews as &$r) {
            $name = trim($r['customer_name']);
            if (strlen($name) > 2) {
                $r['customer_name'] = substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1);
            } else if (strlen($name) == 2) {
                $r['customer_name'] = substr($name, 0, 1) . '*';
            }
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil ulasan pelanggan',
            'data' => $reviews
        ]);
    }

    public function withdraw()
    {
        $payload = $_SERVER['jwt_payload'];
        $amount = $this->request->getVar('amount');
        $bankName = $this->request->getVar('bank_name');
        $accountNumber = $this->request->getVar('account_number');
        $accountName = $this->request->getVar('account_name');

        if (!$amount || $amount < 50000) {
            return $this->respond(['status' => 400, 'message' => 'Minimal pencairan Rp 50.000'], 400);
        }

        $db = \Config\Database::connect();

        $db->transStart();
        $driver = $db->table('drivers')->where('id', $payload['uid'])->get()->getRow();

        if (!$driver || $driver->balance < $amount) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Saldo tidak mencukupi'], 400);
        }

        // Potong saldo
        $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance - ' . $amount, false)->update();

        // Masukkan ke antrean pencairan (tabel withdrawals jika ada, jika tidak, asumsikan kita masukkan nanti)
        // Cek apakah tabel withdrawals ada
        if ($db->tableExists('withdrawals')) {
            $db->table('withdrawals')->insert([
                'user_type' => 'driver',
                'user_id' => $payload['uid'],
                'amount' => $amount,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(['status' => 500, 'message' => 'Terjadi kesalahan sistem saat pencairan'], 500);
        }

        return $this->respond(['status' => 200, 'message' => 'Permintaan pencairan berhasil dikirim dan sedang diproses.']);
    }

    public function dailyReward()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();
        $driver = $driverModel->find($payload['uid']);

        if (!$driver) {
            return $this->respond(['status' => 404, 'message' => 'Driver tidak ditemukan'], 404);
        }

        $db = \Config\Database::connect();
        // Auto-add column if not exists to save user manual work
        if (!$db->fieldExists('last_daily_reward', 'drivers')) {
            $db->query("ALTER TABLE drivers ADD COLUMN last_daily_reward DATE DEFAULT NULL");
        }

        // Re-fetch driver to get the new column just in case it was newly added
        $driver = $driverModel->find($payload['uid']);
        $today = date('Y-m-d');

        if (empty($driver['last_daily_reward']) || $driver['last_daily_reward'] < $today) {
            $newKoin = ($driver['koin'] ?? 0) + 10;
            $driverModel->update($payload['uid'], [
                'koin' => $newKoin,
                'last_daily_reward' => $today
            ]);

            return $this->respond([
                'status' => 200,
                'message' => 'Selamat! Anda mendapatkan 10 koin harian.',
                'koin_added' => 10,
                'total_koin' => $newKoin
            ]);
        }

        return $this->respond(['status' => 400, 'message' => 'Sudah klaim hari ini']);
    }

    public function uploadQris()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();
        $driver = $driverModel->find($payload['uid']);

        if (!$driver) {
            return $this->respond(['status' => 404, 'message' => 'Driver tidak ditemukan'], 404);
        }

        $merchantName = trim($this->request->getVar('qris_merchant_name') ?? '');
        $isActive = $this->request->getVar('qris_is_active');

        $updateData = [];
        if ($merchantName !== '') {
            $updateData['qris_merchant_name'] = $merchantName;
        }
        if ($isActive !== null) {
            $updateData['qris_is_active'] = (int)$isActive;
        }

        $file = $this->request->getFile('qris_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->respond(['status' => 400, 'message' => 'Ukuran file QRIS melebihi 10MB.'], 400);
            }

            $safeExt = $this->determineSafeFileExt($file, ['jpg', 'jpeg', 'png', 'webp']);
            if (!$safeExt) {
                return $this->respond(['status' => 400, 'message' => 'Format file QRIS tidak valid. Gunakan gambar JPG, PNG, atau WEBP.'], 400);
            }

            if (!is_dir(FCPATH . 'uploads/drivers/qris')) {
                mkdir(FCPATH . 'uploads/drivers/qris', 0755, true);
            }

            $newName = 'qris_' . $payload['uid'] . '_' . time() . '.' . $safeExt;

            $file->move(FCPATH . 'uploads/drivers/qris', $newName);
            $updateData['qris_image'] = 'uploads/drivers/qris/' . $newName;
            $updateData['qris_status'] = 'verified'; // Otomatis verified
        }

        if (!empty($updateData)) {
            $driverModel->update($payload['uid'], $updateData);
        }

        $updatedDriver = $driverModel->find($payload['uid']);
        $rawImage = $updatedDriver['qris_image'] ?? '';
        $qrisUrl = !empty($rawImage) ? (strpos($rawImage, 'http') === 0 ? $rawImage : base_url($rawImage)) : null;

        return $this->respond([
            'status' => 200,
            'message' => 'Pengaturan QRIS berhasil disimpan',
            'data' => [
                'qris_image' => $rawImage,
                'qris_image_url' => $qrisUrl,
                'qris_merchant_name' => $updatedDriver['qris_merchant_name'] ?? '',
                'qris_is_active' => (int)($updatedDriver['qris_is_active'] ?? 0),
                'qris_status' => $updatedDriver['qris_status'] ?? 'verified'
            ]
        ]);
    }

    public function toggleQris()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();
        $driver = $driverModel->find($payload['uid']);

        if (!$driver) {
            return $this->respond(['status' => 404, 'message' => 'Driver tidak ditemukan'], 404);
        }

        $isActive = (int)($this->request->getVar('is_active') ?? $this->request->getVar('qris_is_active') ?? 0);
        $driverModel->update($payload['uid'], ['qris_is_active' => $isActive]);

        return $this->respond([
            'status' => 200,
            'message' => $isActive ? 'Pembayaran QRIS diaktifkan' : 'Pembayaran QRIS dinonaktifkan',
            'data' => [
                'qris_is_active' => $isActive
            ]
        ]);
    }

    public function deleteAccount()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverId = $payload['uid'];

        $driverModel = new DriverModel();
        $driver = $driverModel->find($driverId);

        if (!$driver) {
            return $this->respond(['status' => 404, 'message' => 'Driver tidak ditemukan'], 404);
        }

        // De-identify personal data & mark driver account as deleted (inactive)
        $updateData = [
            'name' => 'Driver Dihapus',
            'phone' => 'deleted_' . $driverId . '_' . time(),
            'email' => null,
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'vehicle_plate' => 'DELETED',
            'is_online' => 0,
            'status' => 'inactive',
            'fcm_token' => null,
            'photo' => null,
            'ktp_image' => null,
            'sim_image' => null,
            'stnk_image' => null,
            'vehicle_image' => null,
            'qris_image' => null,
            'qris_is_active' => 0,
        ];

        $driverModel->update($driverId, $updateData);

        return $this->respond([
            'status' => 200,
            'message' => 'Akun dan seluruh data pribadi pengemudi Anda berhasil dihapus dari sistem Maijek.',
            'data' => null
        ], 200);
    }

    public function updateVehicle()
    {
        return $this->respond([
            'status' => 403,
            'message' => 'Perubahan data kendaraan hanya dapat dilakukan oleh Kantor Operasional / Panel Admin Maijek.'
        ], 403);
    }

    /**
     * Memvalidasi file upload gambar/dokumen secara mendalam dan mengembalikan ekstensi yang aman.
     */
    private function determineSafeFileExt($file, array $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf']): ?string
    {
        if (function_exists('validate_uploaded_file_ext')) {
            $ext = validate_uploaded_file_ext($file, $allowedExts);
            if ($ext) return $ext;
        }

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $tempPath = $file->getTempName();
        $detectedExt = null;

        if (!empty($tempPath) && file_exists($tempPath) && is_readable($tempPath)) {
            $imgInfo = @getimagesize($tempPath);
            if ($imgInfo !== false && isset($imgInfo[2])) {
                switch ($imgInfo[2]) {
                    case IMAGETYPE_JPEG:
                        $detectedExt = 'jpg';
                        break;
                    case IMAGETYPE_PNG:
                        $detectedExt = 'png';
                        break;
                    case IMAGETYPE_WEBP:
                        $detectedExt = 'webp';
                        break;
                }
            }
        }

        if (!$detectedExt) {
            $clientExt = strtolower($file->getClientExtension() ?? pathinfo($file->getClientName() ?? '', PATHINFO_EXTENSION));
            if (in_array($clientExt, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                $detectedExt = ($clientExt === 'jpeg') ? 'jpg' : $clientExt;
            }
        }

        if ($detectedExt && in_array($detectedExt, $allowedExts)) {
            return $detectedExt;
        }

        return null;
    }
}
