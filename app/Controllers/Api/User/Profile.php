<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Profile extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $userModel = new UserModel();
        $user = $userModel->find($payload['uid']);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'User tidak ditemukan', 'data' => null], 404);
        }

        // Hapus password dari response
        unset($user['password']);

        // Pastikan kolom name_changes_count dan name_changed_at ada di database
        $db = \Config\Database::connect();
        try {
            if (!$db->fieldExists('name_changes_count', 'users')) {
                $db->query("ALTER TABLE users ADD COLUMN name_changes_count INT NOT NULL DEFAULT 0");
            }
            if (!$db->fieldExists('name_changed_at', 'users')) {
                $db->query("ALTER TABLE users ADD COLUMN name_changed_at DATETIME NULL");
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // Format photo_url
        if (!empty($user['photo'])) {
            $user['photo_url'] = (strpos($user['photo'], 'http') === 0)
                ? $user['photo']
                : base_url('uploads/profiles/' . $user['photo']);
        } else {
            $user['photo_url'] = '';
        }

        $user['name_changes_count'] = (int)($user['name_changes_count'] ?? 0);
        $user['can_change_name'] = $user['name_changes_count'] < 1;

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil profil',
            'data' => $user
        ]);
    }

    public function updateProfile()
    {
        $payload = $_SERVER['jwt_payload'];
        $userId = $payload['uid'];

        $db = \Config\Database::connect();
        try {
            if (!$db->fieldExists('photo', 'users')) {
                $db->query("ALTER TABLE users ADD COLUMN photo VARCHAR(255) NULL AFTER email");
            }
            if (!$db->fieldExists('name_changes_count', 'users')) {
                $db->query("ALTER TABLE users ADD COLUMN name_changes_count INT NOT NULL DEFAULT 0");
            }
            if (!$db->fieldExists('name_changed_at', 'users')) {
                $db->query("ALTER TABLE users ADD COLUMN name_changed_at DATETIME NULL");
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        $rules = [
            'name' => 'required|min_length[2]',
            'phone' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => 400,
                'message' => 'Nama dan nomor telepon tidak boleh kosong',
                'data' => $this->validator->getErrors()
            ], 400);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'User tidak ditemukan'], 404);
        }

        $newName = trim((string)$this->request->getVar('name'));
        $currentName = trim((string)($user['name'] ?? ''));
        $nameChangesCount = (int)($user['name_changes_count'] ?? 0);

        $updateData = [];

        // Aturan: Ubah nama CUMA 1 KALI saja seumur hidup akun
        if (!empty($newName) && $newName !== $currentName) {
            if ($nameChangesCount >= 1) {
                return $this->respond([
                    'status' => 400,
                    'message' => 'Nama akun hanya dapat diubah 1 kali saja demi keamanan identitas. Anda sudah pernah mengubah nama sebelumnya.'
                ], 400);
            }
            $updateData['name'] = $newName;
            $updateData['name_changes_count'] = $nameChangesCount + 1;
            $updateData['name_changed_at'] = date('Y-m-d H:i:s');
        }

        $newPhone = trim((string)$this->request->getVar('phone'));
        if (!empty($newPhone)) {
            $updateData['phone'] = $newPhone;
        }

        $email = trim((string)$this->request->getVar('email'));
        if (!empty($email)) {
            $updateData['email'] = $email;
        }

        // Cek apakah ada file foto yang diunggah
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->respond(['status' => 400, 'message' => 'Ukuran foto profil melebihi 10MB.'], 400);
            }

            $safeExt = function_exists('validate_uploaded_file_ext')
                ? validate_uploaded_file_ext($file, ['jpg', 'jpeg', 'png', 'webp'])
                : null;

            if (!$safeExt) {
                return $this->respond(['status' => 400, 'message' => 'Format file foto tidak valid. Hanya JPG, PNG, atau WEBP.'], 400);
            }

            $uploadDir = ROOTPATH . 'public/uploads/profiles';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = $file->getRandomName();
            $file->move($uploadDir, $newName);
            $updateData['photo'] = $newName;
        }

        if (!empty($updateData)) {
            $userModel->update($userId, $updateData);
        }

        // Ambil data terbaru
        $updatedUser = $userModel->find($userId);
        unset($updatedUser['password']);

        if (!empty($updatedUser['photo'])) {
            $updatedUser['photo_url'] = (strpos($updatedUser['photo'], 'http') === 0)
                ? $updatedUser['photo']
                : base_url('uploads/profiles/' . $updatedUser['photo']);
        } else {
            $updatedUser['photo_url'] = '';
        }

        $updatedUser['name_changes_count'] = (int)($updatedUser['name_changes_count'] ?? 0);
        $updatedUser['can_change_name'] = $updatedUser['name_changes_count'] < 1;

        return $this->respond([
            'status' => 200,
            'message' => 'Profil berhasil diperbarui',
            'data' => $updatedUser
        ]);
    }

    public function updateFcm()
    {
        try {
            $payload = $_SERVER['jwt_payload'] ?? null;
            $uid = $payload['uid'] ?? null;

            if (!$uid) {
                return $this->respond(['status' => 401, 'message' => 'Unauthorized: payload tidak valid'], 401);
            }

            $json = $this->request->getJSON(true) ?? [];
            $fcmToken = $json['fcm_token'] ?? $this->request->getVar('fcm_token');

            if (empty($fcmToken)) {
                return $this->respond(['status' => 400, 'message' => 'Validasi gagal: fcm_token diperlukan'], 400);
            }

            $userModel = new UserModel();
            $userModel->update($uid, [
                'fcm_token' => $fcmToken
            ]);

            return $this->respond(['status' => 200, 'message' => 'FCM Token berhasil diperbarui']);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => 500,
                'message' => 'Server Error: ' . $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    public function dailyReward()
    {
        $payload = $_SERVER['jwt_payload'];
        $userModel = new UserModel();
        $user = $userModel->find($payload['uid']);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'User tidak ditemukan'], 404);
        }

        $db = \Config\Database::connect();
        // Auto-add column if not exists to save user manual work
        if (!$db->fieldExists('last_daily_reward', 'users')) {
            $db->query("ALTER TABLE users ADD COLUMN last_daily_reward DATE DEFAULT NULL");
        }

        // Re-fetch user to get the new column just in case it was newly added
        $user = $userModel->find($payload['uid']);
        $today = date('Y-m-d');

        if (empty($user['last_daily_reward']) || $user['last_daily_reward'] < $today) {
            $newKoin = ($user['coins'] ?? 0) + 10;
            $userModel->update($payload['uid'], [
                'coins' => $newKoin,
                'last_daily_reward' => $today
            ]);

            return $this->respond([
                'status' => 200,
                'message' => 'Selamat! Anda mendapatkan 10 koin harian.',
                'koin_added' => 10,
                'total_koin' => $newKoin
            ]);
        }

        return $this->respond(['status' => 400, 'message' => 'Sudah klaim hari ini'], 400);
    }

    public function deleteAccount()
    {
        $payload = $_SERVER['jwt_payload'];
        $userId = $payload['uid'];

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->respond(['status' => 404, 'message' => 'Akun pengguna tidak ditemukan'], 404);
        }

        // De-identify personal data & mark account as deleted (Google Play & Indonesia PDP compliant)
        $userModel->update($userId, [
            'name' => 'Pengguna Dihapus',
            'phone' => 'deleted_' . $userId . '_' . time(),
            'email' => 'deleted_' . $userId . '_' . time() . '@maijek.com',
            'fcm_token' => null,
            'device_id' => null,
            'status' => 'deleted',
            'is_verified' => 0,
            'photo' => null,
        ]);

        return $this->respond([
            'status' => 200,
            'message' => 'Akun dan seluruh data pribadi Anda berhasil dihapus dari sistem Maijek.',
            'data' => null
        ], 200);
    }
}
