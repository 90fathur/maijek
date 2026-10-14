<?php

namespace App\Controllers\Merchant;

use App\Controllers\BaseController;
use App\Models\MerchantModel;
use App\Models\FoodOrderModel;
use App\Models\MerchantMenuModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $merchantId = session()->get('merchant_id');
        $db = \Config\Database::connect();

        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);
        if (!$merchant) {
            session()->destroy();
            return redirect()->to('/merchant/login');
        }

        // Update session state
        session()->set([
            'merchant_name'    => $merchant['name'],
            'merchant_is_open' => $merchant['is_open'],
            'merchant_photo'   => $merchant['photo'] ?? null,
            'merchant_type'    => $merchant['merchant_type'] ?? 'food',
        ]);

        $today = date('Y-m-d');

        // Statistik Hari Ini
        $ordersToday = $db->table('food_orders')
            ->where('merchant_id', $merchantId)
            ->where('DATE(created_at)', $today)
            ->countAllResults();

        $incomeToday = $db->table('food_orders')
            ->where('merchant_id', $merchantId)
            ->where('DATE(created_at)', $today)
            ->whereIn('status', ['processing', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress', 'completed'])
            ->select('SUM(COALESCE(food_total, total_price - COALESCE(delivery_fee, 0))) as income')
            ->get()
            ->getRow()->income ?? 0;

        $activeOrders = $db->table('food_orders')
            ->where('merchant_id', $merchantId)
            ->whereIn('status', ['pending', 'processing', 'accepted_by_merchant', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress'])
            ->countAllResults();

        $menuCount = $db->table('merchant_menus')
            ->where('merchant_id', $merchantId)
            ->countAllResults();

        // Pesanan Terbaru (5 order)
        $recentOrders = $db->table('food_orders')
            ->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name')
            ->join('users', 'users.id = food_orders.user_id', 'left')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('food_orders.merchant_id', $merchantId)
            ->orderBy('food_orders.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        foreach ($recentOrders as &$order) {
            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', $order['id'])
                ->get()
                ->getResultArray();
        }

        $isMart = (($merchant['merchant_type'] ?? '') === 'mart');

        return view('merchant/dashboard/index', [
            'title'        => $isMart ? 'Dashboard Toko | MaiMart Panel' : 'Dashboard Restoran | MaiFood Panel',
            'merchant'     => $merchant,
            'isMart'       => $isMart,
            'ordersToday'  => $ordersToday,
            'incomeToday'  => $incomeToday,
            'activeOrders' => $activeOrders,
            'menuCount'    => $menuCount,
            'recentOrders' => $recentOrders
        ]);
    }

    public function toggleStatus()
    {
        $merchantId = session()->get('merchant_id');
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);

        if (!$merchant) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Toko tidak ditemukan']);
        }

        $newStatus = $merchant['is_open'] == 1 ? 0 : 1;
        $merchantModel->update($merchantId, ['is_open' => $newStatus]);
        session()->set('merchant_is_open', $newStatus);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 200,
                'is_open' => $newStatus,
                'message' => $newStatus == 1 ? 'Toko Anda sekarang BUKA untuk pesanan.' : 'Toko Anda sekarang TUTUP sementara.'
            ]);
        }

        return redirect()->back()->with('success', $newStatus == 1 ? 'Toko berhasil DIBUKA!' : 'Toko berhasil DITUTUP sementara.');
    }
}
