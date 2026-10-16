<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'user_id',
        'driver_id',
        'service_type',
        'pickup_address',
        'notes',
        'pickup_lat',
        'pickup_lng',
        'dropoff_address',
        'dropoff_lat',
        'dropoff_lng',
        'distance',
        'price',
        'discount_amount',
        'promo_code',
        'status',
        'rating',
        'review_comment',
        'payment_method',
        'recipient_name',
        'recipient_phone',
        'item_description',
        'item_weight',
        'proof_of_pickup',
        'proof_of_delivery',
        'titip_item_price',
        'titip_receipt_img',
        'titip_total_cost',
        'accepted_at',
        'arrived_at',
        'in_progress_at',
        'completed_at',
        'cancel_reason',
        'priority_dispatched_at',
        'fallback_dispatched_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
