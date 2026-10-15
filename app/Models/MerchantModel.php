<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantModel extends Model
{
    protected $table = 'merchants';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'phone', 'password', 'address', 'latitude', 'longitude', 'photo', 'is_open', 'last_active_at', 'merchant_type', 'category', 'balance', 'fcm_token', 'opening_time', 'closing_time', 'operating_days', 'verification_status', 'ktp_image', 'business_image'];
    protected $useTimestamps = true;
}
