<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\VehicleClassModel;

class VehicleClasses extends BaseController
{
    public function index()
    {
        $vcModel = new VehicleClassModel();
        
        $data = [
            'title' => 'Kategori Kendaraan | Maijek Admin',
            'classes' => $vcModel->findAll()
        ];

        return view('admin/vehicleclasses/index', $data);
    }

    public function create()
    {
        $vcModel = new VehicleClassModel();
        
        $vcModel->insert([
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'status' => $this->request->getPost('status')
        ]);

        return redirect()->to('/admin/vehicleclasses')->with('success', 'Kategori kendaraan berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $vcModel = new VehicleClassModel();
        $vcModel->delete($id);

        return redirect()->to('/admin/vehicleclasses')->with('success', 'Kategori kendaraan berhasil dihapus.');
    }
}
