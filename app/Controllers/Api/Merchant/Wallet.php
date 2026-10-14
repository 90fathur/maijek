<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;

class Wallet extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $db = \Config\Database::connect();

        // Calculate total earnings from completed orders (status = 'completed')
        $builder = $db->table('food_orders');
        $builder->selectSum('food_total');
        $builder->where('merchant_id', $merchant_id);
        $builder->where('status', 'completed');
        $result = $builder->get()->getRow();
        $totalEarnings = $result ? ($result->food_total ?? 0) : 0;

        // Fetch transaction history (the completed orders)
        $history = $db->table('food_orders')
            ->where('merchant_id', $merchant_id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        return $this->respond([
            'status' => 200,
            'message' => 'Data dompet mitra',
            'data' => [
                'balance' => $totalEarnings,
                'history' => $history
            ]
        ]);
    }
}
