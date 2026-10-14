<?php

namespace App\Models;

use CodeIgniter\Model;

class FoodOrderItemModel extends Model
{
    protected $table = 'food_order_items';
    protected $primaryKey = 'id';
    protected $allowedFields = ['food_order_id', 'menu_id', 'quantity', 'price', 'variant_details', 'notes'];
    protected $useTimestamps = false; // Karena tidak ada created_at di script DB saya
}
