<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\WithdrawalModel;
use App\Models\DriverModel;
use App\Models\UserModel;
use App\Models\TransactionModel;

class Withdrawals extends BaseController
{
    public function index()
    {
        $withdrawalModel = new WithdrawalModel();
        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $withdrawalModel->select('withdrawals.*, drivers.name as driver_name, drivers.phone as driver_phone, users.name as user_name, users.phone as user_phone')
            ->join('drivers', 'drivers.id = withdrawals.driver_id', 'left')
            ->join('users', 'users.id = withdrawals.user_id', 'left');

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('withdrawals.bank_name', $q)
                ->orLike('withdrawals.account_number', $q)
                ->orLike('withdrawals.account_name', $q)
                ->orLike('drivers.name', $q)
                ->orLike('users.name', $q)
                ->orLike('drivers.phone', $q)
                ->orLike('users.phone', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('withdrawals.id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $builder = $builder->where('withdrawals.status', $status);
        }

        $withdrawals = $builder->orderBy('withdrawals.created_at', 'DESC')->paginate($perPage);
        $pager = $withdrawalModel->pager;

        foreach ($withdrawals as &$wd) {
            $isUser = (!empty($wd['user_type']) && $wd['user_type'] == 'user') || !empty($wd['user_id']);
            $wd['applicant_name'] = $isUser
                ? ($wd['user_name'] ?? 'Penumpang')
                : ($wd['driver_name'] ?? 'Pengemudi');
            $wd['applicant_phone'] = $isUser ? ($wd['user_phone'] ?? '-') : ($wd['driver_phone'] ?? '-');
            $wd['role_label'] = $isUser ? 'Penumpang' : 'Pengemudi';
        }

        $data = [
            'title'       => 'Pencairan Saldo (Driver & Penumpang) | Maijek Admin',
            'withdrawals' => $withdrawals,
            'pager'       => $pager,
            'perPage'     => $perPage,
            'q'           => $q,
            'status'      => $status,
            'totalCount'  => $pager->getTotal(),
        ];

        return view('admin/withdrawals/index', $data);
    }

    public function process($id, $status)
    {
        if (!in_array($status, ['approved', 'rejected'])) return redirect()->back();

        $withdrawalModel = new WithdrawalModel();
        $driverModel = new DriverModel();
        $userModel = new UserModel();

        $withdrawal = $withdrawalModel->find($id);
        if (!$withdrawal || $withdrawal['status'] != 'pending') return redirect()->back();

        $isUser = (!empty($withdrawal['user_type']) && $withdrawal['user_type'] == 'user') || !empty($withdrawal['user_id']);

        if ($status == 'approved') {
            // Saldo sudah dipotong saat pengguna/driver mengajukan penarikan
            $withdrawalModel->update($id, ['status' => 'approved']);
            session()->setFlashdata('success', 'Penarikan dana berhasil disetujui.');
        } else {
            // Jika ditolak, kembalikan saldo ke pengguna / driver
            if ($isUser && !empty($withdrawal['user_id'])) {
                $user = $userModel->find($withdrawal['user_id']);
                if ($user) {
                    $userModel->update($user['id'], [
                        'balance' => (float)$user['balance'] + (float)$withdrawal['amount']
                    ]);
                    $txModel = new TransactionModel();
                    $txModel->insert([
                        'user_id' => $user['id'],
                        'type' => 'credit',
                        'amount' => $withdrawal['amount'],
                        'description' => 'Pengembalian Dana: Penarikan Ditolak'
                    ]);
                }
            } else if (!empty($withdrawal['driver_id'])) {
                $driver = $driverModel->find($withdrawal['driver_id']);
                if ($driver) {
                    $driverModel->update($driver['id'], [
                        'balance' => (float)$driver['balance'] + (float)$withdrawal['amount']
                    ]);
                }
            }
            $withdrawalModel->update($id, ['status' => 'rejected']);
            session()->setFlashdata('success', 'Permintaan penarikan ditolak dan saldo telah dikembalikan ke akun pemohon.');
        }

        return redirect()->to('/admin/withdrawals');
    }

    public function exportCsv()
    {
        $db = \Config\Database::connect();
        $withdrawals = $db->table('withdrawals')
            ->select('withdrawals.*, drivers.name as driver_name, users.name as user_name')
            ->join('drivers', 'drivers.id = withdrawals.driver_id', 'left')
            ->join('users', 'users.id = withdrawals.user_id', 'left')
            ->orderBy('withdrawals.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $filename = 'laporan_penarikan_saldo_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Tipe', 'Nama Pemohon', 'Bank/Tujuan', 'No Rekening', 'Atas Nama', 'Jumlah (Rp)', 'Status', 'Tanggal Pengajuan']);

        foreach ($withdrawals as $w) {
            $isUser = (!empty($w['user_type']) && $w['user_type'] == 'user') || !empty($w['user_id']);
            $nama = $isUser ? ($w['user_name'] ?? 'Penumpang') : ($w['driver_name'] ?? 'Driver');
            fputcsv($output, [
                $w['id'],
                $isUser ? 'Penumpang' : 'Driver',
                $nama,
                $w['bank_name'],
                "'" . $w['account_number'],
                $w['account_name'],
                $w['amount'],
                strtoupper($w['status']),
                $w['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
