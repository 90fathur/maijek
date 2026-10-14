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

        $drivers = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $driverModel->pager;

        $data = [
            'title'        => 'Manajemen Pengemudi | Maijek Admin',
            'drivers'      => $drivers,
            'pager'        => $pager,
            'perPage'      => $perPage,
            'q'            => $q,
            'status'       => $status,
            'vehicle_type' => $vehicleType,
            'totalCount'   => $pager->getTotal(),
        ];

        return view('admin/drivers/index', $data);
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

    public function injectBalance($id)
    {
        $amount = $this->request->getPost('amount');

        if (is_numeric($amount) && $amount > 0) {
            $driverModel = new DriverModel();
            $driver = $driverModel->find($id);

            if ($driver) {
                // Tambahkan saldo
                $newBalance = ($driver['balance'] ?? 0) + $amount;
                $driverModel->update($id, ['balance' => $newBalance]);

                // Bisa tambahkan pencatatan ke tabel transaksi/topup jika perlu
                // Namun untuk sekedar inject saldo, update kolom balance sudah cukup.
            }
        }

        return redirect()->to('/admin/drivers');
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
