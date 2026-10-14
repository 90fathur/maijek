<?php
namespace App\Models;
use CodeIgniter\Model;

class ZoneModel extends Model
{
    protected $table = 'zones';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'coordinates', 'status'];
    protected $useTimestamps = true;
}
