<?php

namespace App\Models;

use CodeIgniter\Model;

class CsChatModel extends Model
{
    protected $table = 'cs_chats';
    protected $primaryKey = 'id';
    protected $allowedFields = ['room_id', 'sender_type', 'sender_id', 'message', 'is_read', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}
