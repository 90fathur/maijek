<?php

namespace App\Controllers\Api\Driver;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DriverModel;
use App\Models\WithdrawalModel;

class Wallet extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();
        $withdrawalModel = new WithdrawalModel();

        $driver = $driverModel->find($payload['uid']);
        $recent_withdrawals = $withdrawalModel->where('driver_id', $payload['uid'])->orderBy('created_at', 'DESC')->findAll(10);

        return $this->respond([
            'status' => 200,
            'message' => 'Data dompet pengemudi',
            'data' => [
                'balance' => (int)($driver['balance'] ?? 0),
                'history' => $recent_withdrawals
            ]
        ]);
    }

    public function withdraw()
    {
        $payload = $_SERVER['jwt_payload'];
        $amount = $this->request->getVar('amount');
        $bank_name = $this->request->getVar('bank_name');
        $account_number = $this->request->getVar('account_number');
        $account_name = $this->request->getVar('account_name') ?? $payload['name'] ?? 'Driver';

        if (!$amount || !$bank_name || !$account_number) {
            return $this->respond(['status' => 400, 'message' => 'Mohon lengkapi data penarikan'], 400);
        }

        $settingModel = new \App\Models\SettingModel();
        $minWdSetting = $settingModel->where('setting_key', 'driver_min_withdraw')->first();
        $minWithdraw = $minWdSetting ? (int)$minWdSetting['setting_value'] : 50000;

        if ($amount < $minWithdraw) {
            return $this->respond(['status' => 400, 'message' => 'Minimal penarikan adalah Rp ' . number_format($minWithdraw, 0, ',', '.')], 400);
        }

        $driverModel = new DriverModel();
        $withdrawalModel = new WithdrawalModel();

        // Cek apakah masih ada request pending
        $pending = $withdrawalModel->where('driver_id', $payload['uid'])->where('status', 'pending')->first();
        if ($pending) {
            return $this->respond(['status' => 400, 'message' => 'Anda masih memiliki penarikan yang sedang diproses.'], 400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Lock baris driver dengan FOR UPDATE (Anti-Race Condition & Overdraw)
        $driver = $db->query("SELECT * FROM drivers WHERE id = ? FOR UPDATE", [$payload['uid']])->getRowArray();

        if (!$driver || (float)$driver['balance'] < $amount) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Saldo tidak mencukupi'], 400);
        }

        // Cek pending withdrawal di dalam row lock
        $pendingInside = $withdrawalModel->where('driver_id', $payload['uid'])->where('status', 'pending')->first();
        if ($pendingInside) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Anda masih memiliki penarikan yang sedang diproses.'], 400);
        }

        // 2. Potong saldo driver secara atomik bersyarat
        $deducted = $db->table('drivers')
            ->where('id', $payload['uid'])
            ->where('balance >=', $amount)
            ->set('balance', 'balance - ' . (float)$amount, false)
            ->update();

        if (!$deducted) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Gagal memproses penarikan, saldo tidak mencukupi.'], 400);
        }

        // 2. Buat record penarikan
        $db->table('withdrawals')->insert([
            'driver_id' => $payload['uid'],
            'user_type' => 'driver',
            'amount' => $amount,
            'bank_name' => $bank_name,
            'account_number' => $account_number,
            'account_name' => $account_name,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(['status' => 500, 'message' => 'Terjadi kesalahan pada sistem'], 500);
        }

        return $this->respond(['status' => 200, 'message' => 'Permintaan penarikan berhasil diajukan.', 'data' => null]);
    }

    public function withdrawStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $withdrawalModel = new WithdrawalModel();
        $list = $withdrawalModel->where('driver_id', $payload['uid'])->orderBy('created_at', 'DESC')->findAll(30);

        return $this->respond([
            'status' => 200,
            'message' => 'Riwayat penarikan pengemudi',
            'data' => $list
        ]);
    }

    public function analytics()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverId = $payload['uid'];

        $orderModel = new \App\Models\OrderModel();

        // Buat rentang 7 hari terakhir
        $analytics = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            // Ambil nama hari dalam bahasa Indonesia (opsional, kita pakai date saja)
            $analytics[$date] = [
                'date' => $date,
                'total_earnings' => 0,
                'total_orders' => 0
            ];
        }

        // Ambil data dari database
        $db = \Config\Database::connect();
        $hasDiscountCol = $db->fieldExists('discount_amount', 'orders');
        $sumExpr = $hasDiscountCol ? 'SUM(price + COALESCE(discount_amount, 0))' : 'SUM(price)';

        $builder = $orderModel->builder();
        $builder->select("DATE(created_at) as date, {$sumExpr} as total_earnings, COUNT(id) as total_orders");
        $builder->where('driver_id', $driverId);
        $builder->where('status', 'completed');
        $builder->where('created_at >=', date('Y-m-d', strtotime('-7 days')));
        $builder->groupBy('DATE(created_at)');

        $query = $builder->get();
        $results = $query->getResultArray();

        // Gabungkan hasil database ke array 7 hari
        foreach ($results as $row) {
            $date = $row['date'];
            if (isset($analytics[$date])) {
                $analytics[$date]['total_earnings'] = (int)$row['total_earnings'];
                $analytics[$date]['total_orders'] = (int)$row['total_orders'];
            }
        }

        // Ubah menjadi array indexed (berurutan)
        $formattedData = array_values($analytics);

        return $this->respond([
            'status' => 200,
            'message' => 'Data analitik 7 hari terakhir',
            'data' => $formattedData
        ]);
    }
}
