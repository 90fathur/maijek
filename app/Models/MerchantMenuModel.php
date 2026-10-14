<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantMenuModel extends Model
{
    protected $table = 'merchant_menus';
    protected $primaryKey = 'id';
    protected $allowedFields = ['merchant_id', 'name', 'description', 'price', 'discount_price', 'photo', 'is_available', 'unit', 'category'];
    protected $useTimestamps = true;
}
