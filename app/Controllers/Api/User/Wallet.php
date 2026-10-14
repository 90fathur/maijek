<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TransactionModel;
use App\Models\UserModel;
use App\Models\WithdrawalModel;

class Wallet extends ResourceController
{
    protected $format = 'json';

    public function history()
    {
        $payload = $_SERVER['jwt_payload'];
        $userId = $payload['uid'];

        $transactionModel = new TransactionModel();

        // Ambil riwayat dari yang terbaru
        $transactions = $transactionModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil riwayat transaksi',
            'data' => $transactions
        ], 200);
    }

    public function topup()
    {
        // KEAMANAN: Matikan penambahan saldo instan tanpa payment gateway valid.
        // Mencegah eksploitasi saldo gratis oleh penumpang.
        return $this->respond([
            'status' => 400,
            'message' => 'Top Up otomatis via aplikasi saat ini sedang dalam pemeliharaan sistem. Silakan hubungi Admin / CS Maijek untuk pengisian saldo.',
            'data' => null
        ], 400);
    }

    // Endpoint Tarik Tunai (Withdraw) Penumpang
    public function withdraw()
    {
        $payload = $_SERVER['jwt_payload'];
        $userId = $payload['uid'];

        $db = \Config\Database::connect();

        // Auto-migration aman jika kolom belum ada di database server
        try {
            if (!$db->fieldExists('user_id', 'withdrawals')) {
                $db->query("ALTER TABLE withdrawals ADD COLUMN user_id INT(11) UNSIGNED NULL AFTER driver_id");
            }
            if (!$db->fieldExists('user_type', 'withdrawals')) {
                $db->query("ALTER TABLE withdrawals ADD COLUMN user_type ENUM('driver', 'user', 'merchant') NOT NULL DEFAULT 'driver' AFTER user_id");
            }
            $db->query("ALTER TABLE withdrawals MODIFY driver_id INT(11) UNSIGNED NULL");
        } catch (\Throwable $e) {
            // Ignore if already exists
        }

        $rules = [
            'amount' => 'required|numeric|greater_than_equal_to[20000]',
            'bank_name' => 'required',
            'account_number' => 'required',
            'account_name' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status' => 400,
                'message' => 'Validasi gagal, minimal penarikan Rp 20.000 dan data rekening harus lengkap.',
                'data' => $this->validator->getErrors()
            ], 400);
        }

        $amount = (float)$this->request->getVar('amount');
        $bankName = trim($this->request->getVar('bank_name'));
        $accountNumber = trim($this->request->getVar('account_number'));
        $accountName = trim($this->request->getVar('account_name'));

        $withdrawalModel = new WithdrawalModel();

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Lock row user agar balance tidak berubah oleh transaksi paralel (Anti Race Condition)
        $user = $db->query("SELECT * FROM users WHERE id = ? FOR UPDATE", [$userId])->getRowArray();
        if (!$user || (float)$user['balance'] < $amount) {
            $db->transRollback();
            return $this->respond([
                'status' => 400,
                'message' => 'Saldo MaiPay Anda tidak mencukupi untuk melakukan penarikan ini.'
            ], 400);
        }

        // Cek pending withdrawal di dalam lock
        $pending = $withdrawalModel->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            $db->transRollback();
            return $this->respond([
                'status' => 400,
                'message' => 'Anda masih memiliki permintaan penarikan yang sedang diproses. Harap tunggu hingga selesai.'
            ], 400);
        }

        // 2. Potong Saldo User secara atomik
        $deducted = $db->table('users')
            ->where('id', $userId)
            ->where('balance >=', $amount)
            ->set('balance', 'balance - ' . $amount, false)
            ->update();

        if (!$deducted) {
            $db->transRollback();
            return $this->respond([
                'status' => 400,
                'message' => 'Gagal memproses penarikan saldo, saldo tidak mencukupi.'
            ], 400);
        }

        $newBalance = (float)$user['balance'] - $amount;

        // 2. Catat Riwayat Transaksi Debit
        $transactionModel = new TransactionModel();
        $transactionModel->insert([
            'user_id' => $userId,
            'type' => 'debit',
            'amount' => $amount,
            'description' => "Tarik Tunai ke $bankName ($accountNumber) a.n $accountName"
        ]);

        // 3. Masukkan ke Antrean Penarikan (Tabel withdrawals)
        $withdrawalModel->insert([
            'user_id' => $userId,
            'user_type' => 'user',
            'amount' => $amount,
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'status' => 'pending'
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond([
                'status' => 500,
                'message' => 'Gagal memproses penarikan saldo, silakan coba beberapa saat lagi.'
            ], 500);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Permintaan penarikan berhasil diajukan dan sedang diproses dalam 1x24 jam kerja.',
            'data' => [
                'new_balance' => $newBalance,
                'amount' => $amount,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_name' => $accountName
            ]
        ], 200);
    }

    // Mengambil status dan riwayat penarikan pengguna
    public function withdrawStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $userId = $payload['uid'];

        $withdrawalModel = new WithdrawalModel();
        $history = $withdrawalModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        return $this->respond([
            'status' => 200,
            'message' => 'Riwayat penarikan dana',
            'data' => $history
        ], 200);
    }
}
