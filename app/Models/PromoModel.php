<?php
namespace App\Models;
use CodeIgniter\Model;

class PromoModel extends Model
{
    protected $table = 'promos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['code', 'discount_amount', 'discount_type', 'valid_until', 'status'];
    protected $useTimestamps = true;
}
