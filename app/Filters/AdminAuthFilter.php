<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/admin/login');
        }

        $role = session()->get('admin_role') ?? 'superadmin';
        $uri = $request->getUri()->getSegment(2); // 'admin' is segment 1, next is segment 2

        if ($role == 'cs') {
            $allowed = ['dashboard', 'orders', 'reviews', 'tickets', 'logout', ''];
            if (!in_array($uri, $allowed)) return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki izin.');
        } else if ($role == 'finance') {
            $allowed = ['dashboard', 'withdrawals', 'logout', ''];
            if (!in_array($uri, $allowed)) return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki izin.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
