<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class Auth extends BaseController
{
    public function login()
    {
        // Jika sudah login, lempar ke dashboard
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        if ($this->request->getMethod() == 'POST') {
            // Anti Brute-Force: Maks 5 percobaan login per 5 menit per IP
            $throttler = \Config\Services::throttler();
            $ip = $this->request->getIPAddress();
            if ($throttler->check('admin_login_' . md5($ip), 5, 300) === false) {
                session()->setFlashdata('error', 'Terlalu banyak percobaan login gagal. Silakan tunggu 5 menit.');
                return redirect()->back();
            }

            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $captcha_answer = $this->request->getPost('captcha');

            // Validasi Captcha
            if ($captcha_answer != session()->get('captcha_result')) {
                session()->setFlashdata('error', 'Jawaban Captcha salah!');
                return redirect()->back();
            }

            $adminModel = new AdminModel();
            $admin = $adminModel->where('username', $username)->first();

            if ($admin && password_verify($password, $admin['password'])) {
                // Cegah Session Fixation attack
                session()->regenerate();

                session()->set([
                    'admin_logged_in' => true,
                    'admin_id' => $admin['id'],
                    'admin_username' => $admin['username'],
                    'admin_role' => $admin['role'] ?? 'superadmin'
                ]);
                return redirect()->to('/admin/dashboard');
            } else {
                session()->setFlashdata('error', 'Username atau password salah.');
                return redirect()->back();
            }
        }

        // Generate Math Captcha for GET request
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        session()->set('captcha_result', $num1 + $num2);

        $data = [
            'captcha_question' => "Berapa hasil dari {$num1} + {$num2}?"
        ];

        return view('admin/login', $data);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
