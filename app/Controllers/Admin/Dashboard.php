<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\DriverModel;
use App\Models\OrderModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $driverModel = new DriverModel();
        $orderModel = new OrderModel();
        $foodOrderModel = new \App\Models\FoodOrderModel();

        // Data untuk Grafik (Pesanan 7 hari terakhir)
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT date, SUM(count) as count FROM (
                SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(created_at)
                UNION ALL
                SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM food_orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(created_at)
            ) as combined
            GROUP BY date
            ORDER BY date ASC
        ");
        $chartData = $query->getResultArray();

        // Format data untuk Chart.js
        $dates = [];
        $counts = [];
        // Isi dengan 7 hari terakhir sebagai default (jika kosong)
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('d M', strtotime($date));
            $found = false;
            foreach ($chartData as $row) {
                if ($row['date'] == $date) {
                    $counts[] = $row['count'];
                    $found = true;
                    break;
                }
            }
            if (!$found) $counts[] = 0;
        }

        $settingModel = new \App\Models\SettingModel();

        $platformFee = (float)($settingModel->where('setting_key', 'platform_fee')->first()['setting_value'] ?? 15);
        $platformFeeType = $settingModel->where('setting_key', 'platform_fee_type')->first()['setting_value'] ?? 'percent';

        $foodPlatformFee = (float)($settingModel->where('setting_key', 'food_platform_fee')->first()['setting_value'] ?? 15);
        $foodPlatformFeeType = $settingModel->where('setting_key', 'food_platform_fee_type')->first()['setting_value'] ?? 'percent';

        $orderRevenue = $orderModel->selectSum('price')->where('status', 'completed')->first()['price'] ?? 0;
        $orderCount = $orderModel->where('status', 'completed')->countAllResults();

        $netOrderRevenue = ($platformFeeType == 'percent')
            ? ($orderRevenue * ($platformFee / 100))
            : ($orderCount * $platformFee);

        $foodRevenue = $foodOrderModel->selectSum('total_price')->where('status', 'completed')->first()['total_price'] ?? 0;
        $foodCount = $foodOrderModel->where('status', 'completed')->countAllResults();

        $netFoodRevenue = ($foodPlatformFeeType == 'percent')
            ? ($foodRevenue * ($foodPlatformFee / 100))
            : ($foodCount * $foodPlatformFee);

        $total_gross_revenue = $orderRevenue + $foodRevenue;
        $total_net_revenue = $netOrderRevenue + $netFoodRevenue;
        $orderDate = trim($this->request->getGet('order_date') ?? '');

        // Merge recent orders with Customer, Driver, and Merchant/Food details
        $normalBuilder = $db->table('orders')
            ->select('orders.id, orders.created_at, orders.status, orders.cancel_reason, orders.payment_method, orders.price, orders.service_type, orders.pickup_address, orders.dropoff_address, orders.item_description, orders.user_id, orders.driver_id, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left');

        if (!empty($orderDate)) {
            $normalBuilder->where('DATE(orders.created_at)', $orderDate);
        }

        $recentNormalOrders = $normalBuilder->orderBy('orders.created_at', 'DESC')
            ->limit(!empty($orderDate) ? 50 : 12)
            ->get()
            ->getResultArray();

        $foodBuilder = $db->table('food_orders')
            ->select('food_orders.id, food_orders.created_at, food_orders.status, food_orders.cancel_reason, food_orders.payment_method, food_orders.total_price as price, food_orders.order_type, food_orders.delivery_address, food_orders.user_id, food_orders.driver_id, food_orders.merchant_id, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate, merchants.name as merchant_name')
            ->join('users', 'users.id = food_orders.user_id', 'left')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->join('merchants', 'merchants.id = food_orders.merchant_id', 'left');

        if (!empty($orderDate)) {
            $foodBuilder->where('DATE(food_orders.created_at)', $orderDate);
        }

        $recentFoodOrders = $foodBuilder->orderBy('food_orders.created_at', 'DESC')
            ->limit(!empty($orderDate) ? 50 : 12)
            ->get()
            ->getResultArray();

        // Ambil rincian menu yang dipesan untuk pesanan makanan/mart
        $foodOrderIds = array_column($recentFoodOrders, 'id');
        $foodItemsMap = [];
        if (!empty($foodOrderIds)) {
            $items = $db->table('food_order_items')
                ->select('food_order_items.food_order_id, food_order_items.quantity, merchant_menus.name as menu_name')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->whereIn('food_order_items.food_order_id', $foodOrderIds)
                ->get()
                ->getResultArray();
            foreach ($items as $it) {
                $foId = $it['food_order_id'];
                $menuName = !empty($it['menu_name']) ? $it['menu_name'] : 'Menu';
                $foodItemsMap[$foId][] = $menuName . ' (' . $it['quantity'] . 'x)';
            }
        }

        $mergedOrders = [];
        foreach ($recentNormalOrders as $o) {
            $vType = strtolower($o['service_type'] ?? 'motor');
            $serviceLabel = 'MaiRide';
            if (in_array($vType, ['car', 'mobil'])) {
                $serviceLabel = 'MaiCar';
            } elseif (str_contains($vType, 'send')) {
                $serviceLabel = 'MaiSend';
            } elseif (str_contains($vType, 'titip')) {
                $serviceLabel = 'MaiTitip';
            }

            $routeDesc = '';
            if (!empty($o['pickup_address']) && !empty($o['dropoff_address'])) {
                $routeDesc = $o['pickup_address'] . ' → ' . $o['dropoff_address'];
            }

            $mergedOrders[] = [
                'id'            => $o['id'],
                'raw_id'        => (int)$o['id'],
                'display_id'    => '#' . $o['id'],
                'type'          => 'ride',
                'service_label' => $serviceLabel,
                'created_at'    => $o['created_at'],
                'status'        => $o['status'],
                'cancel_reason' => $o['cancel_reason'] ?? null,
                'payment_method'=> $o['payment_method'] ?? 'cash',
                'price'         => (float)$o['price'],
                'user_name'     => !empty($o['user_name']) ? $o['user_name'] : 'Pelanggan #' . $o['user_id'],
                'user_phone'    => $o['user_phone'] ?? '',
                'driver_name'   => !empty($o['driver_name']) ? $o['driver_name'] : null,
                'driver_phone'  => $o['driver_phone'] ?? '',
                'driver_plate'  => $o['vehicle_plate'] ?? '',
                'merchant_name' => null,
                'items_summary' => !empty($o['item_description']) ? $o['item_description'] : $routeDesc,
            ];
        }

        foreach ($recentFoodOrders as $fo) {
            $foId = $fo['id'];
            $itemsList = $foodItemsMap[$foId] ?? [];
            $itemsSummary = !empty($itemsList) ? implode(', ', $itemsList) : null;
            $orderType = ($fo['order_type'] ?? 'food') === 'mart' ? 'MaiMart' : 'MaiFood';

            $mergedOrders[] = [
                'id'            => 'F' . $fo['id'],
                'raw_id'        => (int)$fo['id'],
                'display_id'    => '#F' . $fo['id'],
                'type'          => 'food',
                'service_label' => $orderType,
                'created_at'    => $fo['created_at'],
                'status'        => $fo['status'],
                'cancel_reason' => $fo['cancel_reason'] ?? null,
                'payment_method'=> $fo['payment_method'] ?? 'cash',
                'price'         => (float)$fo['price'],
                'user_name'     => !empty($fo['user_name']) ? $fo['user_name'] : 'Pelanggan #' . $fo['user_id'],
                'user_phone'    => $fo['user_phone'] ?? '',
                'driver_name'   => !empty($fo['driver_name']) ? $fo['driver_name'] : null,
                'driver_phone'  => $fo['driver_phone'] ?? '',
                'driver_plate'  => $fo['vehicle_plate'] ?? '',
                'merchant_name' => !empty($fo['merchant_name']) ? $fo['merchant_name'] : 'Toko / Resto',
                'items_summary' => $itemsSummary,
            ];
        }

        usort($mergedOrders, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        $fiveMinsAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $onlineMotor = $driverModel->where('is_online', 1)->where('status', 'active')->where('last_heartbeat_at >=', $fiveMinsAgo)->groupStart()->where('vehicle_type', 'motor')->orWhere('vehicle_type', null)->orWhere('vehicle_type', '')->groupEnd()->countAllResults();
        $onlineMobil = $driverModel->where('is_online', 1)->where('status', 'active')->where('last_heartbeat_at >=', $fiveMinsAgo)->whereIn('vehicle_type', ['mobil', 'car'])->countAllResults();

        // Ambil daftar pengemudi aktif untuk pemantauan langsung di dashboard
        $activeDrivers = $driverModel->where('status', 'active')
            ->orderBy('is_online', 'DESC')
            ->orderBy('last_heartbeat_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Dashboard | Maijek Admin',
            'total_users' => $userModel->countAllResults(),
            'total_drivers' => $driverModel->countAllResults(),
            'online_motor' => $onlineMotor,
            'online_mobil' => $onlineMobil,
            'active_drivers' => $activeDrivers,
            'total_orders' => $orderModel->countAllResults() + $foodOrderModel->countAllResults(),
            'total_gross_revenue' => $total_gross_revenue,
            'total_net_revenue' => $total_net_revenue,
            'order_date' => $orderDate,
            'recent_orders' => !empty($orderDate) ? $mergedOrders : array_slice($mergedOrders, 0, 8),
            'chart_dates' => json_encode($dates),
            'chart_counts' => json_encode($counts),
            'chart_counts_raw' => $counts,
        ];

        return view('admin/dashboard', $data);
    }

    public function liveUpdates()
    {
        $db = \Config\Database::connect();
        $userModel = new UserModel();
        $driverModel = new DriverModel();
        $orderModel = new OrderModel();
        $foodOrderModel = new \App\Models\FoodOrderModel();

        $fiveMinsAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $onlineMotor = $driverModel->where('is_online', 1)->where('status', 'active')->where('last_heartbeat_at >=', $fiveMinsAgo)->groupStart()->where('vehicle_type', 'motor')->orWhere('vehicle_type', null)->orWhere('vehicle_type', '')->groupEnd()->countAllResults();
        $onlineMobil = $driverModel->where('is_online', 1)->where('status', 'active')->where('last_heartbeat_at >=', $fiveMinsAgo)->whereIn('vehicle_type', ['mobil', 'car'])->countAllResults();

        $orderDate = trim($this->request->getGet('order_date') ?? '');

        // 8 Pesanan Terakhir
        $ordersQuery = $db->table('orders')
            ->select('orders.id, orders.created_at, orders.status, orders.cancel_reason, orders.price, orders.service_type, orders.pickup_address, orders.dropoff_address, orders.item_description, orders.user_id, orders.driver_id, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left');
        if (!empty($orderDate)) {
            $ordersQuery->where('DATE(orders.created_at)', $orderDate);
        }
        $recentNormalOrders = $ordersQuery->orderBy('orders.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $foodOrdersQuery = $db->table('food_orders')
            ->select('food_orders.id, food_orders.created_at, food_orders.status, food_orders.cancel_reason, food_orders.total_price as price, food_orders.order_type, food_orders.user_id, food_orders.driver_id, food_orders.merchant_id, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate, merchants.name as merchant_name')
            ->join('users', 'users.id = food_orders.user_id', 'left')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->join('merchants', 'merchants.id = food_orders.merchant_id', 'left');
        if (!empty($orderDate)) {
            $foodOrdersQuery->where('DATE(food_orders.created_at)', $orderDate);
        }
        $recentFoodOrders = $foodOrdersQuery->orderBy('food_orders.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Rincian menu food
        $foodItemsMap = [];
        $foodOrderIds = array_column($recentFoodOrders, 'id');
        if (!empty($foodOrderIds)) {
            $itemsQuery = $db->table('food_order_items')
                ->select('food_order_items.food_order_id, food_order_items.quantity, merchant_menus.name')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->whereIn('food_order_items.food_order_id', $foodOrderIds)
                ->get()
                ->getResultArray();

            foreach ($itemsQuery as $it) {
                $mName = !empty($it['name']) ? $it['name'] : 'Menu';
                $foodItemsMap[$it['food_order_id']][] = $it['quantity'] . 'x ' . $mName;
            }
        }

        $mergedOrders = [];
        foreach ($recentNormalOrders as $o) {
            $vType = strtolower($o['service_type'] ?? 'motor');
            $serviceLabel = 'MaiRide';
            if (in_array($vType, ['car', 'mobil'])) {
                $serviceLabel = 'MaiCar';
            } elseif (str_contains($vType, 'send')) {
                $serviceLabel = 'MaiSend';
            } elseif (str_contains($vType, 'titip')) {
                $serviceLabel = 'MaiTitip';
            }

            $routeDesc = '-';
            if (!empty($o['pickup_address']) || !empty($o['dropoff_address'])) {
                $routeDesc = ($o['pickup_address'] ?? '-') . ' -> ' . ($o['dropoff_address'] ?? '-');
            }

            $mergedOrders[] = [
                'id'            => $o['id'],
                'raw_id'        => (int)$o['id'],
                'display_id'    => '#' . $o['id'],
                'type'          => 'ride',
                'service_label' => $serviceLabel,
                'created_at'    => $o['created_at'],
                'created_at_fmt' => date('d M, H:i', strtotime($o['created_at'])),
                'status'        => $o['status'],
                'cancel_reason' => $o['cancel_reason'] ?? null,
                'price'         => (float)$o['price'],
                'price_fmt'     => 'Rp ' . number_format((float)$o['price'], 0, ',', '.'),
                'user_name'     => !empty($o['user_name']) ? $o['user_name'] : 'Pelanggan #' . $o['user_id'],
                'user_phone'    => $o['user_phone'] ?? '',
                'driver_name'   => !empty($o['driver_name']) ? $o['driver_name'] : null,
                'driver_phone'  => $o['driver_phone'] ?? '',
                'driver_plate'  => $o['vehicle_plate'] ?? '',
                'merchant_name' => null,
                'items_summary' => !empty($o['item_description']) ? $o['item_description'] : $routeDesc,
            ];
        }

        foreach ($recentFoodOrders as $fo) {
            $foId = $fo['id'];
            $itemsList = $foodItemsMap[$foId] ?? [];
            $itemsSummary = !empty($itemsList) ? implode(', ', $itemsList) : null;
            $orderType = ($fo['order_type'] ?? 'food') === 'mart' ? 'MaiMart' : 'MaiFood';

            $mergedOrders[] = [
                'id'            => 'F' . $fo['id'],
                'raw_id'        => (int)$fo['id'],
                'display_id'    => '#F' . $fo['id'],
                'type'          => 'food',
                'service_label' => $orderType,
                'created_at'    => $fo['created_at'],
                'created_at_fmt' => date('d M, H:i', strtotime($fo['created_at'])),
                'status'        => $fo['status'],
                'cancel_reason' => $fo['cancel_reason'] ?? null,
                'price'         => (float)$fo['price'],
                'price_fmt'     => 'Rp ' . number_format((float)$fo['price'], 0, ',', '.'),
                'user_name'     => !empty($fo['user_name']) ? $fo['user_name'] : 'Pelanggan #' . $fo['user_id'],
                'user_phone'    => $fo['user_phone'] ?? '',
                'driver_name'   => !empty($fo['driver_name']) ? $fo['driver_name'] : null,
                'driver_phone'  => $fo['driver_phone'] ?? '',
                'driver_plate'  => $fo['vehicle_plate'] ?? '',
                'merchant_name' => !empty($fo['merchant_name']) ? $fo['merchant_name'] : 'Toko / Resto',
                'items_summary' => $itemsSummary,
            ];
        }

        usort($mergedOrders, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        $slicedOrders = array_slice($mergedOrders, 0, 7);
        $latestOrder = !empty($slicedOrders) ? $slicedOrders[0] : null;

        return $this->response->setJSON([
            'status'         => 'success',
            'timestamp'      => date('H:i:s'),
            'summary'        => [
                'total_orders'   => $orderModel->countAllResults() + $foodOrderModel->countAllResults(),
                'online_motor'   => $onlineMotor,
                'online_mobil'   => $onlineMobil,
                'total_drivers'  => $driverModel->countAllResults(),
                'total_users'    => $userModel->countAllResults(),
            ],
            'latest_order_key' => $latestOrder ? ($latestOrder['type'] . '_' . $latestOrder['raw_id'] . '_' . $latestOrder['status']) : '',
            'latest_order'     => $latestOrder,
            'recent_orders'    => $slicedOrders,
        ]);
    }
}
