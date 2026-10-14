<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MerchantModel;

class MerchantWebAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('merchant_logged_in')) {
            return redirect()->to('/merchant/login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        // Pastikan status merchant aktif
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find(session()->get('merchant_id'));
        if (!$merchant || $merchant['verification_status'] === 'suspended' || $merchant['verification_status'] === 'rejected') {
            session()->destroy();
            return redirect()->to('/merchant/login')->with('error', 'Akun toko Anda sedang disuspend atau dinonaktifkan.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
