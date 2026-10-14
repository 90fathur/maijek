<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DriverModel;

class Api extends BaseController
{
    public function getDriversLocation()
    {
        $driverModel = new DriverModel();
        // Fetch only active drivers with coordinates
        $drivers = $driverModel->where('status', 'active')
            ->where('is_online', 1)
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->findAll();

        return $this->response->setJSON($drivers);
    }
}
