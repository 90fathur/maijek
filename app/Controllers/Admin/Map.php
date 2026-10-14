<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\ZoneModel;

class Map extends BaseController
{
    public function index()
    {
        $zoneModel = new ZoneModel();
        $zones = $zoneModel->where('status', 'active')->findAll();

        $data = [
            'title' => 'Peta Driver Langsung | Maijek Admin',
            'zones' => $zones
        ];
        return view('admin/map/index', $data);
    }
}
