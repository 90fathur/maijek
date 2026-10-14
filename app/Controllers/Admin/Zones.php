<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\ZoneModel;

class Zones extends BaseController
{
    public function index()
    {
        $zoneModel = new ZoneModel();
        
        $data = [
            'title' => 'Zona Operasional (Geofence) | Maijek Admin',
            'zones' => $zoneModel->findAll()
        ];

        return view('admin/zones/index', $data);
    }

    public function create()
    {
        $zoneModel = new ZoneModel();
        
        // Ensure coordinates is valid JSON format
        $coords = $this->request->getPost('coordinates');
        json_decode($coords);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Format Koordinat Polygon tidak valid (Harus berupa JSON Array).');
        }

        $zoneModel->insert([
            'name' => $this->request->getPost('name'),
            'coordinates' => $coords,
            'status' => $this->request->getPost('status')
        ]);

        return redirect()->to('/admin/zones')->with('success', 'Zona operasional berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $zoneModel = new ZoneModel();
        $zoneModel->delete($id);

        return redirect()->to('/admin/zones')->with('success', 'Zona operasional berhasil dihapus.');
    }
}
