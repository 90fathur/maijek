<?php

namespace App\Models;

use CodeIgniter\Model;

class FoodOrderModel extends Model
{
    protected $table = 'food_orders';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'driver_id',
        'merchant_id',
        'order_type',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'distance',
        'food_total',
        'delivery_fee',
        'discount_amount',
        'promo_code',
        'total_price',
        'payment_method',
        'vehicle_type',
        'status',
        'cancel_reason',
        'merchant_rating',
        'driver_rating',
        'review_merchant',
        'review_driver',
        'in_progress_at',
        'completed_at',
        'cancel_reason',
        'priority_dispatched_at',
        'fallback_dispatched_at'
    ];
    protected $useTimestamps = true;
}
