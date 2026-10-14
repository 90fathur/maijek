<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MerchantModel;

class MerchantVerifications extends BaseController
{
    public function index()
    {
        $merchantModel = new MerchantModel();
        $q = trim($this->request->getGet('q') ?? '');
        $merchantType = trim($this->request->getGet('merchant_type') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $merchantModel;

        if ($status !== '' && in_array($status, ['unverified', 'pending', 'rejected', 'approved'])) {
            $builder = $builder->where('verification_status', $status);
        } else {
            $builder = $builder->whereIn('verification_status', ['unverified', 'pending', 'rejected']);
        }

        if ($merchantType !== '' && in_array($merchantType, ['food', 'mart'])) {
            $builder = $builder->where('merchant_type', $merchantType);
        }

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('name', $q)
                ->orLike('phone', $q)
                ->orLike('address', $q)
                ->orLike('category', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        $merchants = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $merchantModel->pager;

        $data = [
            'title'         => 'Verifikasi Mitra UMKM (MaiFood & MaiMart) | Maijek Admin',
            'merchants'     => $merchants,
            'pager'         => $pager,
            'perPage'       => $perPage,
            'q'             => $q,
            'merchant_type' => $merchantType,
            'status'        => $status,
            'totalCount'    => $pager->getTotal(),
        ];

        return view('admin/merchants/verifications', $data);
    }

    public function verify($id, $status)
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            return redirect()->back();
        }

        $merchantModel = new MerchantModel();

        // Update status verifikasi
        $merchantModel->update($id, [
            'verification_status' => $status,
            'is_open' => $status == 'approved' ? 1 : 0
        ]);

        session()->setFlashdata('success', 'Status verifikasi UMKM berhasil diubah menjadi: ' . strtoupper($status));
        return redirect()->to('/admin/merchant-verifications');
    }
}
