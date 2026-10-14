<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\AdminModel;

class Profile extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Profil Saya & Keamanan | Maijek Admin'
        ];
        return view('admin/profile/index', $data);
    }

    public function update()
    {
        $old_password = $this->request->getPost('old_password');
        $new_password = $this->request->getPost('new_password');
        $confirm_password = $this->request->getPost('confirm_password');

        if ($new_password !== $confirm_password) {
            return redirect()->back()->with('error', 'Konfirmasi kata sandi baru tidak cocok.');
        }

        $adminModel = new AdminModel();
        $admin = $adminModel->find(session()->get('admin_id'));

        if (!password_verify($old_password, $admin['password'])) {
            return redirect()->back()->with('error', 'Kata sandi lama Anda salah.');
        }

        $adminModel->update($admin['id'], [
            'password' => password_hash($new_password, PASSWORD_BCRYPT)
        ]);

        return redirect()->back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
