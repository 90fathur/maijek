<?php

namespace App\Models;

use CodeIgniter\Model;

class DriverModel extends Model
{
    protected $table            = 'drivers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'name',
        'phone',
        'email',
        'password',
        'vehicle_plate',
        'vehicle_type',
        'vehicle_brand',
        'vehicle_capacity',
        'status',
        'is_online',
        'latitude',
        'longitude',
        'ktp_image',
        'sim_image',
        'stnk_image',
        'vehicle_image',
        'verification_status',
        'balance',
        'photo',
        'fcm_token',
        'last_heartbeat_at',
        'rating',
        'rating_count',
        'daily_skip_count',
        'last_skip_date',
        'qris_image',
        'qris_merchant_name',
        'qris_is_active',
        'qris_status',
        'is_priority'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
