<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DriverModel;

class Drivers extends BaseController
{
    public function index()
    {
        $driverModel = new DriverModel();
        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $vehicleType = trim($this->request->getGet('vehicle_type') ?? '');
        $perPage = 15;

        $builder = $driverModel;

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('name', $q)
                ->orLike('phone', $q)
                ->orLike('vehicle_plate', $q)
                ->orLike('vehicle_brand', $q)
                ->orLike('vehicle_type', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        if ($status !== '' && in_array($status, ['active', 'inactive', 'suspended'])) {
            $builder = $builder->where('status', $status);
        }

        if ($vehicleType !== '' && in_array($vehicleType, ['motor', 'mobil'])) {
            $builder = $builder->where('vehicle_type', $vehicleType);
        }

        $isPriority = trim($this->request->getGet('is_priority') ?? '');
        if ($isPriority === '1') {
            $builder = $builder->where('is_priority', 1);
        }

        $drivers = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $driverModel->pager;

        $db = \Config\Database::connect();
        try {
            $db->table('drivers')->where('balance <', 0)->update(['balance' => 0]);
        } catch (\Throwable $e) {}

        $totalPriorityCount = 0;
        try {
            if ($db->fieldExists('is_priority', 'drivers')) {
                $totalPriorityCount = $db->table('drivers')->where('is_priority', 1)->countAllResults();
            }
        } catch (\Throwable $e) {}

        $data = [
            'title'              => 'Manajemen Pengemudi | Maijek Admin',
            'drivers'            => $drivers,
            'pager'              => $pager,
            'perPage'            => $perPage,
            'q'                  => $q,
            'status'             => $status,
            'vehicle_type'       => $vehicleType,
            'is_priority'        => $isPriority,
            'totalPriorityCount' => $totalPriorityCount,
            'totalCount'         => $pager->getTotal(),
        ];

        return view('admin/drivers/index', $data);
    }

    public function resetNegativeBalances()
    {
        $db = \Config\Database::connect();
        $affected = $db->table('drivers')->where('balance <', 0)->countAllResults();
        if ($affected > 0) {
            $db->table('drivers')->where('balance <', 0)->update(['balance' => 0]);
            session()->setFlashdata('success', "Berhasil mereset {$affected} akun pengemudi dengan saldo minus menjadi Rp 0.");
        } else {
            session()->setFlashdata('success', 'Semua saldo pengemudi sudah normal (tidak ada saldo minus).');
        }
        return redirect()->to('/admin/drivers');
    }

    public function togglePriority($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Accept') === 'application/json') {
                return $this->response->setJSON(['status' => 404, 'message' => 'Driver tidak ditemukan']);
            }
            session()->setFlashdata('success', 'Gagal: Driver tidak ditemukan');
            return redirect()->to('/admin/drivers');
        }

        $db = \Config\Database::connect();
        // Pastikan kolom ada
        if (!$db->fieldExists('is_priority', 'drivers')) {
            $db->query("ALTER TABLE drivers ADD COLUMN is_priority TINYINT(1) NOT NULL DEFAULT 0 AFTER status;");
        }

        $currentPriority = (int)($driver['is_priority'] ?? 0);
        $newPriority = ($currentPriority === 1) ? 0 : 1;

        $db->table('drivers')->where('id', $id)->update(['is_priority' => $newPriority]);

        $statusText = $newPriority ? 'Ditetapkan sebagai Driver Prioritas ⭐' : 'Status Driver Prioritas Dihapus';

        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setJSON([
                'status'      => 200,
                'message'     => 'Sukses: ' . $statusText,
                'is_priority' => $newPriority,
                'driver_id'   => $id
            ]);
        }

        session()->setFlashdata('success', $driver['name'] . ': ' . $statusText);
        return redirect()->to('/admin/drivers');
    }

    public function changeStatus($id)
    {
        $status = $this->request->getPost('status');

        if (in_array($status, ['active', 'inactive', 'suspended'])) {
            $driverModel = new DriverModel();

            $updateData = ['status' => $status];
            if ($status === 'active') {
                $updateData['verification_status'] = 'approved';
            }

            $driverModel->update($id, $updateData);
        }

        return redirect()->to('/admin/drivers');
    }

    public function adjustBalance($id)
    {
        $action = strtolower(trim((string)($this->request->getPost('action') ?? 'add'))); // 'add' atau 'deduct'
        $amount = (float)$this->request->getPost('amount');
        $note = trim((string)$this->request->getPost('note'));
        $sendNotif = $this->request->getPost('send_notif') !== '0';

        if ($amount <= 0) {
            session()->setFlashdata('success', 'Gagal: Nominal saldo harus lebih besar dari Rp 0!');
            return redirect()->to('/admin/drivers');
        }

        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            session()->setFlashdata('success', 'Gagal: Data pengemudi tidak ditemukan!');
            return redirect()->to('/admin/drivers');
        }

        $currentBalance = (float)($driver['balance'] ?? 0);
        $db = \Config\Database::connect();
        $db->transStart();

        if ($action === 'deduct') {
            if ($currentBalance < $amount) {
                $db->transRollback();
                session()->setFlashdata('success', 'Gagal: Saldo pengemudi saat ini (Rp ' . number_format($currentBalance, 0, ',', '.') . ') tidak mencukupi untuk dipotong sebesar Rp ' . number_format($amount, 0, ',', '.') . '!');
                return redirect()->to('/admin/drivers');
            }

            $newBalance = $currentBalance - $amount;
            $txType = 'debit';
            $desc = 'Pemotongan Saldo MaiPay oleh Admin' . ($note ? ": $note" : '');
            $notifTitle = 'Penyesuaian Saldo MaiPay ⚠️';
            $notifBody = 'Saldo MaiPay Anda telah dipotong sebesar Rp ' . number_format($amount, 0, ',', '.') . ($note ? " ($note)" : '') . '. Sisa saldo: Rp ' . number_format($newBalance, 0, ',', '.');
        } else {
            $newBalance = $currentBalance + $amount;
            $txType = 'credit';
            $desc = 'Penambahan Saldo MaiPay oleh Admin' . ($note ? ": $note" : '');
            $notifTitle = 'Saldo MaiPay Bertambah! 💰';
            $notifBody = 'Saldo MaiPay Anda telah ditambahkan sebesar Rp ' . number_format($amount, 0, ',', '.') . ($note ? " ($note)" : '') . '. Total saldo: Rp ' . number_format($newBalance, 0, ',', '.');
        }

        // 1. Update saldo pengemudi
        $db->table('drivers')->where('id', $id)->update(['balance' => $newBalance]);

        // 2. Catat ke tabel riwayat transaksi (transactions)
        if ($db->tableExists('transactions')) {
            $db->table('transactions')->insert([
                'driver_id'   => $id,
                'user_id'     => null,
                'type'        => $txType,
                'amount'      => (int)$amount,
                'description' => $desc,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        // 3. Kirim notifikasi FCM langsung ke HP pengemudi
        if ($sendNotif && !empty($driver['fcm_token'])) {
            try {
                $fcm = new \App\Libraries\FcmService();
                $fcm->sendPushNotification($driver['fcm_token'], $notifTitle, $notifBody, [
                    'type' => 'wallet_balance_adjusted',
                    'action' => $action,
                    'amount' => (string)$amount,
                    'new_balance' => (string)$newBalance
                ]);
            } catch (\Throwable $e) {}
        }

        $actionWord = ($action === 'deduct') ? 'dipotong' : 'ditambahkan';
        session()->setFlashdata('success', 'Berhasil: Saldo MaiPay pengemudi ' . esc($driver['name']) . ' berhasil ' . $actionWord . ' sebesar Rp ' . number_format($amount, 0, ',', '.') . '. Saldo baru: Rp ' . number_format($newBalance, 0, ',', '.') . '.');

        return redirect()->to('/admin/drivers');
    }

    public function injectBalance($id)
    {
        if (!$this->request->getPost('action')) {
            $_POST['action'] = 'add';
        }
        return $this->adjustBalance($id);
    }

    public function resetPassword($id)
    {
        $newPassword = $this->request->getPost('new_password');

        if (!empty($newPassword)) {
            $driverModel = new DriverModel();
            $driverModel->update($id, [
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
            session()->setFlashdata('success', 'Password pengemudi berhasil di-reset.');
        }

        return redirect()->to('/admin/drivers');
    }

    public function updateVehicle($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);

        if (!$driver) {
            session()->setFlashdata('success', 'Gagal: Driver tidak ditemukan!');
            return redirect()->to('/admin/drivers');
        }

        $db = \Config\Database::connect();
        try {
            if (!$db->fieldExists('vehicle_brand', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_brand VARCHAR(100) NULL AFTER vehicle_plate");
            }
            if (!$db->fieldExists('vehicle_capacity', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_capacity INT DEFAULT 4 AFTER vehicle_brand");
            }
        } catch (\Throwable $e) {}

        $vehicle_type = strtolower(trim((string)$this->request->getPost('vehicle_type')));
        $vehicle_plate = strtoupper(trim((string)$this->request->getPost('vehicle_plate')));
        $vehicle_brand = trim((string)$this->request->getPost('vehicle_brand'));
        $vehicle_capacity = (int)$this->request->getPost('vehicle_capacity');

        $isCar = ($vehicle_type === 'mobil');
        if (empty($vehicle_brand)) {
            $vehicle_brand = $isCar ? 'Toyota Avanza' : 'Honda Beat';
        }
        if ($vehicle_capacity <= 0) {
            $vehicle_capacity = $isCar ? 4 : 1;
        }

        $updateData = [
            'vehicle_plate' => $vehicle_plate ?: ($driver['vehicle_plate'] ?? ''),
            'vehicle_brand' => $vehicle_brand,
            'vehicle_capacity' => $vehicle_capacity,
        ];
        if (in_array($vehicle_type, ['motor', 'mobil'])) {
            $updateData['vehicle_type'] = $vehicle_type;
        }

        // Direct DB update to prevent model field protection from discarding any column
        $db->table('drivers')->where('id', $id)->update($updateData);

        session()->setFlashdata('success', 'Data kendaraan pengemudi ' . esc($driver['name']) . ' berhasil diperbarui (' . $vehicle_capacity . ' Kursi)!');

        return redirect()->to('/admin/drivers');
    }

    public function store()
    {
        $name = $this->request->getPost('name');
        $phone = $this->request->getPost('phone');
        $password = $this->request->getPost('password');
        $vehicle_plate = $this->request->getPost('vehicle_plate');
        $vehicle_type = $this->request->getPost('vehicle_type');
        $vehicle_brand = $this->request->getPost('vehicle_brand');
        $vehicle_capacity = (int)$this->request->getPost('vehicle_capacity');

        if (!empty($name) && !empty($phone) && !empty($password) && !empty($vehicle_type)) {
            $driverModel = new DriverModel();

            // Cek duplikasi no hp
            $existing = $driverModel->where('phone', $phone)->first();
            if ($existing) {
                session()->setFlashdata('success', 'Gagal: Nomor HP sudah digunakan!');
                return redirect()->to('/admin/drivers');
            }

            $isCar = (strtolower($vehicle_type) === 'mobil');
            if (empty($vehicle_brand)) {
                $vehicle_brand = $isCar ? 'Toyota Avanza' : 'Honda Beat';
            }
            if ($vehicle_capacity <= 0) {
                $vehicle_capacity = $isCar ? 4 : 1;
            }

            $driverModel->insert([
                'name' => $name,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'vehicle_plate' => strtoupper(trim((string)$vehicle_plate)),
                'vehicle_brand' => trim((string)$vehicle_brand),
                'vehicle_capacity' => $vehicle_capacity,
                'vehicle_type' => $vehicle_type,
                'status' => 'active',
                'verification_status' => 'approved', // Langsung approved
                'balance' => 0
            ]);

            session()->setFlashdata('success', 'Pengemudi berhasil ditambahkan dan langsung aktif!');
        }

        return redirect()->to('/admin/drivers');
    }
}
