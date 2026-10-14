<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\AdminModel;

class Admins extends BaseController
{
    public function index()
    {
        if (session()->get('admin_role') != 'superadmin') return redirect()->back()->with('error', 'Akses ditolak.');

        $adminModel = new AdminModel();
        
        $data = [
            'title' => 'Manajemen Hak Akses | Maijek Admin',
            'admins' => $adminModel->findAll()
        ];

        return view('admin/admins/index', $data);
    }

    public function create()
    {
        if (session()->get('admin_role') != 'superadmin') return redirect()->back();

        $adminModel = new AdminModel();
        
        $adminModel->insert([
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role' => $this->request->getPost('role')
        ]);

        return redirect()->to('/admin/admins')->with('success', 'Akun admin berhasil ditambahkan.');
    }

    public function delete($id)
    {
        if (session()->get('admin_role') != 'superadmin' || session()->get('admin_id') == $id) return redirect()->back();

        $adminModel = new AdminModel();
        $adminModel->delete($id);

        return redirect()->to('/admin/admins')->with('success', 'Akun admin berhasil dihapus.');
    }
}
