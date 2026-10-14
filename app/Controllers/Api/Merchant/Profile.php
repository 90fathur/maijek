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
        $merchantModel = new MerchantModel();

        $merchant = $merchantModel->find($payload['uid']);
        if (!$merchant) return $this->respond(['status' => 404, 'message' => 'Not found'], 404);

        $newStatus = $merchant['is_open'] == 1 ? 0 : 1;
        $merchantModel->update($payload['uid'], ['is_open' => $newStatus]);

        return $this->respond([
            'status' => 200,
            'message' => 'Status berhasil diubah',
            'data' => ['is_open' => $newStatus]
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

        $merchantModel = new MerchantModel();

        $data = [];
        if ($this->request->getVar('opening_time')) {
            $data['opening_time'] = $this->request->getVar('opening_time');
        }
        if ($this->request->getVar('closing_time')) {
            $data['closing_time'] = $this->request->getVar('closing_time');
        }
        if ($this->request->getVar('operating_days') !== null) {
            $days = $this->request->getVar('operating_days');
            $data['operating_days'] = is_array($days) ? implode(',', $days) : (string)$days;
        }
        if ($this->request->getVar('name')) {
            $data['name'] = $this->request->getVar('name');
        }
        if ($this->request->getVar('latitude') !== null) {
            $data['latitude'] = $this->request->getVar('latitude');
        }
        if ($this->request->getVar('longitude') !== null) {
            $data['longitude'] = $this->request->getVar('longitude');
        }
        if ($this->request->getVar('address') !== null) {
            $data['address'] = $this->request->getVar('address');
        }
        if ($this->request->getVar('merchant_type') !== null) {
            $data['merchant_type'] = $this->request->getVar('merchant_type');
        }
        if ($this->request->getVar('category') !== null) {
            $data['category'] = $this->request->getVar('category');
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
