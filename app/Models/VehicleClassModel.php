<?php
namespace App\Models;
use CodeIgniter\Model;

class VehicleClassModel extends Model
{
    protected $table = 'vehicle_classes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'type', 'status'];
    protected $useTimestamps = true;
}
