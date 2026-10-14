<?php

namespace App\Controllers\Merchant;

use App\Controllers\BaseController;
use App\Models\MerchantModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('merchant_logged_in')) {
            return redirect()->to('/merchant/dashboard');
        }

        return view('merchant/auth/login', [
            'title' => 'Login Mitra Restoran | MaiFood'
        ]);
    }

    public function attemptLogin()
    {
        $phone = trim($this->request->getPost('phone') ?? '');
        $password = $this->request->getPost('password') ?? '';

        if (empty($phone) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Nomor HP dan Password wajib diisi.');
        }

        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->where('phone', $phone)->first();

        if (!$merchant) {
            return redirect()->back()->withInput()->with('error', 'Akun toko dengan nomor HP tersebut tidak ditemukan.');
        }

        if (!password_verify($password, $merchant['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password yang Anda masukkan salah.');
        }

        if ($merchant['verification_status'] === 'suspended') {
            return redirect()->back()->withInput()->with('error', 'Akun toko Anda sedang disuspend. Hubungi CS Maijek.');
        }

        if ($merchant['verification_status'] === 'rejected') {
            return redirect()->back()->withInput()->with('error', 'Pendaftaran toko Anda ditolak.');
        }

        $merchantType = $merchant['merchant_type'] ?? 'food';

        // Set session
        session()->set([
            'merchant_logged_in' => true,
            'merchant_id'        => $merchant['id'],
            'merchant_name'      => $merchant['name'],
            'merchant_phone'     => $merchant['phone'],
            'merchant_photo'     => $merchant['photo'] ?? null,
            'merchant_is_open'   => $merchant['is_open'] ?? 1,
            'merchant_type'      => $merchantType,
        ]);

        $panelName = ($merchantType === 'mart') ? 'MaiMart' : 'MaiFood';
        return redirect()->to('/merchant/dashboard')->with('success', 'Selamat datang di Panel ' . $panelName . ', ' . esc($merchant['name']) . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/merchant/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
