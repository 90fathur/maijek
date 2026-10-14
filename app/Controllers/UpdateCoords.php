<?php

namespace App\Controllers;

class UpdateCoords extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $db->query("UPDATE merchants SET latitude = -3.4287, longitude = 119.1432 WHERE latitude IS NULL OR longitude IS NULL");
        return "Updated successfully!";
    }
}
