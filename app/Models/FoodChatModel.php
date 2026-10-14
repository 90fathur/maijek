<?php

namespace App\Models;

use CodeIgniter\Model;

class FoodChatModel extends Model
{
    protected $table = 'food_chats';
    protected $primaryKey = 'id';
    protected $allowedFields = ['food_order_id', 'sender_type', 'sender_id', 'message', 'created_at'];
    protected $useTimestamps = false; // Karena menggunakan created_at default di DB
}
