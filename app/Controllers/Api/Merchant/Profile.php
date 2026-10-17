<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MerchantModel;

class Profile extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchantModel = new MerchantModel();

        $merchant = $merchantModel->find($payload['uid']);
        if (!$merchant) return $this->respond(['status' => 404, 'message' => 'Toko tidak ditemukan'], 404);

        unset($merchant['password']);

        // Jika foto menggunakan path relatif, buat jadi URL absolut
        if (!empty($merchant['photo']) && strpos($merchant['photo'], 'http') !== 0) {
            $merchant['photo'] = base_url($merchant['photo']);
        }

        return $this->respond(['status' => 200, 'message' => 'Profil merchant', 'data' => $merchant]);
    }

    public function toggleStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $db = \Config\Database::connect();
        $merchantModel = new MerchantModel();

        $merchant = $merchantModel->find($payload['uid']);
        if (!$merchant) {
            return $this->respond(['status' => 404, 'message' => 'Toko tidak ditemukan'], 404);
        }

        // Cek apakah akun toko disuspend atau ditolak oleh Admin
        if (isset($merchant['verification_status']) && in_array($merchant['verification_status'], ['suspended', 'rejected'])) {
            return $this->respond([
                'status' => 403,
                'message' => 'Akun toko Anda sedang dinonaktifkan/disuspend oleh Admin. Silakan hubungi Customer Service.',
                'data' => ['is_open' => 0]
            ], 403);
        }

        // Ambil target status eksplisit jika dikirim oleh aplikasi
        $explicitIsOpen = $this->request->getVar('is_open');
        if ($explicitIsOpen === null) {
            $json = $this->request->getJSON(true);
            if (isset($json['is_open'])) {
                $explicitIsOpen = $json['is_open'];
            } elseif (isset($json['status'])) {
                $explicitIsOpen = ($json['status'] === 'open' || $json['status'] == 1 || $json['status'] === true) ? 1 : 0;
            }
        }

        if ($explicitIsOpen !== null) {
            $newStatus = ($explicitIsOpen == 1 || $explicitIsOpen === true || $explicitIsOpen === '1') ? 1 : 0;
        } else {
            $currentStatus = (int)($merchant['is_open'] ?? 0);
            $newStatus = ($currentStatus === 1) ? 0 : 1;
        }

        // Pastikan kolom last_active_at dan verification_status aman di database
        if (!$db->fieldExists('last_active_at', 'merchants')) {
            try {
                $db->query("ALTER TABLE merchants ADD COLUMN last_active_at DATETIME NULL DEFAULT NULL AFTER is_open;");
            } catch (\Throwable $e) {}
        }
        if (!$db->fieldExists('verification_status', 'merchants')) {
            try {
                $db->query("ALTER TABLE merchants ADD COLUMN verification_status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'approved' AFTER balance;");
            } catch (\Throwable $e) {}
        }

        $updateData = ['is_open' => $newStatus];

        if ($db->fieldExists('last_active_at', 'merchants')) {
            $updateData['last_active_at'] = ($newStatus == 1) ? date('Y-m-d H:i:s') : null;
        }

        // Jika toko dibuka dan status verifikasi masih pending/unverified/kosong, auto-approve agar langsung aktif di MaiFood/MaiMart
        if ($newStatus == 1 && $db->fieldExists('verification_status', 'merchants')) {
            if (empty($merchant['verification_status']) || in_array($merchant['verification_status'], ['pending', 'unverified'])) {
                $updateData['verification_status'] = 'approved';
            }
        }

        $merchantModel->update($payload['uid'], $updateData);

        return $this->respond([
            'status' => 200,
            'message' => $newStatus == 1 ? 'Toko berhasil DIBUKA & siap menerima pesanan.' : 'Toko berhasil DITUTUP sementara.',
            'data' => [
                'is_open' => $newStatus,
                'status_label' => $newStatus == 1 ? 'BUKA' : 'TUTUP'
            ]
        ]);
    }

    public function updateFcm()
    {
        $payload = $_SERVER['jwt_payload'];
        $json = $this->request->getJSON(true) ?? [];
        $fcmToken = $json['fcm_token'] ?? $this->request->getVar('fcm_token');

        if (empty($fcmToken)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal: fcm_token diperlukan'], 400);
        }

        $merchantModel = new MerchantModel();
        $merchantModel->update($payload['uid'], [
            'fcm_token' => $fcmToken
        ]);

        return $this->respond(['status' => 200, 'message' => 'FCM Token berhasil diperbarui']);
    }

    public function updateProfile()
    {
        $payload = $_SERVER['jwt_payload'];
        $db = \Config\Database::connect();
        $merchantModel = new MerchantModel();

        $data = [];
        $json = $this->request->getJSON(true) ?? [];

        if ($this->request->getVar('opening_time')) {
            $data['opening_time'] = $this->request->getVar('opening_time');
        } elseif (isset($json['opening_time'])) {
            $data['opening_time'] = $json['opening_time'];
        }

        if ($this->request->getVar('closing_time')) {
            $data['closing_time'] = $this->request->getVar('closing_time');
        } elseif (isset($json['closing_time'])) {
            $data['closing_time'] = $json['closing_time'];
        }

        if ($this->request->getVar('operating_days') !== null) {
            $days = $this->request->getVar('operating_days');
            $data['operating_days'] = is_array($days) ? implode(',', $days) : (string)$days;
        } elseif (isset($json['operating_days'])) {
            $days = $json['operating_days'];
            $data['operating_days'] = is_array($days) ? json_encode($days) : (string)$days;
        }

        if ($this->request->getVar('name')) {
            $data['name'] = $this->request->getVar('name');
        } elseif (isset($json['name'])) {
            $data['name'] = $json['name'];
        }

        if ($this->request->getVar('latitude') !== null) {
            $data['latitude'] = $this->request->getVar('latitude');
        } elseif (isset($json['latitude'])) {
            $data['latitude'] = $json['latitude'];
        }

        if ($this->request->getVar('longitude') !== null) {
            $data['longitude'] = $this->request->getVar('longitude');
        } elseif (isset($json['longitude'])) {
            $data['longitude'] = $json['longitude'];
        }

        if ($this->request->getVar('address') !== null) {
            $data['address'] = $this->request->getVar('address');
        } elseif (isset($json['address'])) {
            $data['address'] = $json['address'];
        }

        if ($this->request->getVar('merchant_type') !== null) {
            $data['merchant_type'] = $this->request->getVar('merchant_type');
        } elseif (isset($json['merchant_type'])) {
            $data['merchant_type'] = $json['merchant_type'];
        }

        if ($this->request->getVar('category') !== null) {
            $data['category'] = $this->request->getVar('category');
        } elseif (isset($json['category'])) {
            $data['category'] = $json['category'];
        }

        // Dukungan status is_open pada updateProfile
        $reqIsOpen = $this->request->getVar('is_open') ?? ($json['is_open'] ?? null);
        if ($reqIsOpen !== null) {
            $isOpenVal = ($reqIsOpen == 1 || $reqIsOpen === true || $reqIsOpen === '1') ? 1 : 0;
            $data['is_open'] = $isOpenVal;
            if ($db->fieldExists('last_active_at', 'merchants')) {
                $data['last_active_at'] = ($isOpenVal == 1) ? date('Y-m-d H:i:s') : null;
            }
        }

        if (empty($data)) {
            return $this->respond(['status' => 400, 'message' => 'Tidak ada data yang diubah'], 400);
        }

        $merchantModel->update($payload['uid'], $data);

        return $this->respond(['status' => 200, 'message' => 'Profil berhasil diperbarui']);
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
        $merchant = $db->table('merchants')->where('id', $payload['uid'])->get()->getRow();

        if (!$merchant || $merchant->balance < $amount) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Saldo tidak mencukupi'], 400);
        }

        // Potong saldo
        $db->table('merchants')->where('id', $payload['uid'])->set('balance', 'balance - ' . $amount, false)->update();

        // Masukkan ke antrean pencairan (tabel withdrawals jika ada)
        if ($db->tableExists('withdrawals')) {
            $db->table('withdrawals')->insert([
                'user_type' => 'merchant',
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

    public function uploadPhoto()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchantModel = new MerchantModel();

        $file = $this->request->getFile('photo');
        if (!$file || !$file->isValid()) {
            return $this->respond(['status' => 400, 'message' => 'File foto tidak valid atau tidak ditemukan'], 400);
        }

        if (!is_dir(FCPATH . 'uploads/merchants')) {
            mkdir(FCPATH . 'uploads/merchants', 0777, true);
        }

        $ext = $file->guessExtension() ?? $file->getExtension();
        if (empty($ext) || $ext === 'bin' || $ext === '') $ext = 'jpg';
        $newName = uniqid('merchant_') . '.' . $ext;

        $file->move(FCPATH . 'uploads/merchants', $newName);
        $photoPath = 'uploads/merchants/' . $newName;

        $merchantModel->update($payload['uid'], [
            'photo' => $photoPath
        ]);

        return $this->respond([
            'status' => 200,
            'message' => 'Foto profil toko berhasil diperbarui',
            'data' => [
                'photo' => base_url($photoPath)
            ]
        ]);
    }
}
