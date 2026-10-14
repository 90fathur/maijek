<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\DriverModel;

class Verifications extends BaseController
{
    public function index()
    {
        $driverModel = new DriverModel();
        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $driverModel;

        if ($status !== '' && in_array($status, ['unverified', 'pending', 'rejected', 'approved'])) {
            $builder = $builder->where('verification_status', $status);
        } else {
            $builder = $builder->whereIn('verification_status', ['unverified', 'pending', 'rejected']);
        }

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('name', $q)
                ->orLike('phone', $q)
                ->orLike('vehicle_plate', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        $drivers = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $driverModel->pager;

        $data = [
            'title'      => 'Verifikasi Dokumen Pengemudi | Maijek Admin',
            'drivers'    => $drivers,
            'pager'      => $pager,
            'perPage'    => $perPage,
            'q'          => $q,
            'status'     => $status,
            'totalCount' => $pager->getTotal(),
        ];

        return view('admin/verifications/index', $data);
    }

    public function verify($id, $status)
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            return redirect()->back();
        }

        $driverModel = new DriverModel();
        $driverModel->update($id, [
            'verification_status' => $status,
            'status' => $status == 'approved' ? 'active' : 'inactive'
        ]);

        session()->setFlashdata('success', 'Status verifikasi berhasil diubah menjadi: ' . strtoupper($status));
        return redirect()->to('/admin/verifications');
    }
}
