<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'phone', 'email', 'password', 'photo', 'balance', 'coins', 'last_daily_reward', 'fcm_token', 'device_id', 'is_verified', 'status', 'otp_code', 'otp_expired_at', 'name_changes_count', 'name_changed_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
