<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FoodOrderModel;

class Analytics extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchantId = $payload['uid'];

        $db = \Config\Database::connect();

        // Total Pendapatan Bulan Ini
        $month = date('m');
        $year = date('Y');

        $totalIncomeQuery = $db->query("SELECT SUM(food_total) as total_income FROM food_orders WHERE merchant_id = $merchantId AND status = 'completed' AND MONTH(created_at) = $month AND YEAR(created_at) = $year");
        $totalIncome = $totalIncomeQuery->getRow()->total_income ?? 0;

        // Total Order Bulan Ini
        $totalOrderQuery = $db->query("SELECT COUNT(id) as total_order FROM food_orders WHERE merchant_id = $merchantId AND status = 'completed' AND MONTH(created_at) = $month AND YEAR(created_at) = $year");
        $totalOrder = $totalOrderQuery->getRow()->total_order ?? 0;

        // Best Sellers (Top 5 Menus)
        $bestSellersQuery = $db->query("
            SELECT m.name, m.photo, SUM(i.quantity) as sold_count
            FROM food_order_items i
            JOIN food_orders o ON o.id = i.food_order_id
            JOIN merchant_menus m ON m.id = i.menu_id
            WHERE o.merchant_id = $merchantId AND o.status = 'completed'
            GROUP BY i.menu_id
            ORDER BY sold_count DESC
            LIMIT 5
        ");
        $bestSellers = $bestSellersQuery->getResultArray();

        return $this->respond([
            'status' => 200,
            'message' => 'Statistik Penjualan',
            'data' => [
                'total_income' => $totalIncome,
                'total_orders' => $totalOrder,
                'best_sellers' => $bestSellers
            ]
        ]);
    }
}
