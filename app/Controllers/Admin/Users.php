<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $builder = $userModel;

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('name', $q)
                ->orLike('phone', $q)
                ->orLike('email', $q);

            $idNumber = preg_replace('/[^0-9]/', '', $q);
            if ($idNumber !== '') {
                $builder = $builder->orWhere('id', (int)$idNumber);
            }
            $builder = $builder->groupEnd();
        }

        if ($status !== '' && in_array($status, ['active', 'suspended'])) {
            $builder = $builder->where('status', $status);
        }

        $users = $builder->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager = $userModel->pager;

        $data = [
            'title'      => 'Manajemen Pelanggan | Maijek Admin',
            'users'      => $users,
            'pager'      => $pager,
            'perPage'    => $perPage,
            'q'          => $q,
            'status'     => $status,
            'totalCount' => $pager->getTotal(),
        ];

        return view('admin/users/index', $data);
    }

    public function resetDeviceId($id)
    {
        $userModel = new UserModel();
        $userModel->update($id, ['device_id' => null]);

        session()->setFlashdata('success', 'Device ID pelanggan berhasil di-reset.');
        return redirect()->to('/admin/users');
    }

    public function resetBalance($id)
    {
        $userModel = new UserModel();
        $userModel->update($id, ['balance' => 0]);

        session()->setFlashdata('success', 'Saldo pelanggan berhasil di-reset ke Rp 0.');
        return redirect()->to('/admin/users');
    }

    public function setBalance($id)
    {
        $amount = (float)$this->request->getPost('balance');
        $userModel = new UserModel();
        $userModel->update($id, ['balance' => max(0, $amount)]);

        session()->setFlashdata('success', 'Saldo pelanggan berhasil diperbarui.');
        return redirect()->to('/admin/users');
    }

    public function resetPassword($id)
    {
        $newPassword = $this->request->getPost('new_password');

        if (!empty($newPassword)) {
            $userModel = new UserModel();
            $userModel->update($id, [
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
            session()->setFlashdata('success', 'Password pelanggan berhasil di-reset.');
        }

        return redirect()->to('/admin/users');
    }

    public function changeStatus($id)
    {
        $status = $this->request->getPost('status');

        if (in_array($status, ['active', 'suspended'])) {
            $userModel = new UserModel();
            $userModel->update($id, ['status' => $status]);

            $label = $status === 'suspended' ? 'disuspend (dinonaktifkan)' : 'diaktifkan kembali';
            session()->setFlashdata('success', "Akun penumpang berhasil {$label}.");
        }

        return redirect()->to('/admin/users');
    }
}
