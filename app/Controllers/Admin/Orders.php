<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\FoodOrderModel;

class Orders extends BaseController
{
    public function index()
    {
        $tab = trim($this->request->getGet('tab') ?? 'ride');
        if (!in_array($tab, ['ride', 'food'])) {
            $tab = 'ride';
        }

        $q = trim($this->request->getGet('q') ?? '');
        $status = trim($this->request->getGet('status') ?? '');
        $perPage = 15;

        $orderModel = new OrderModel();
        $foodOrderModel = new FoodOrderModel();

        $totalRide = $orderModel->countAllResults();
        $totalFood = $foodOrderModel->countAllResults();

        if ($tab === 'food') {
            $builder = $foodOrderModel->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate, merchants.name as merchant_name, merchants.phone as merchant_phone')
                ->join('users', 'users.id = food_orders.user_id', 'left')
                ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
                ->join('merchants', 'merchants.id = food_orders.merchant_id', 'left');

            if ($q !== '') {
                $builder = $builder->groupStart()
                    ->like('food_orders.delivery_address', $q)
                    ->orLike('users.name', $q)
                    ->orLike('drivers.name', $q)
                    ->orLike('merchants.name', $q);

                $idNumber = preg_replace('/[^0-9]/', '', $q);
                if ($idNumber !== '') {
                    $builder = $builder->orWhere('food_orders.id', (int)$idNumber)
                        ->orWhere('food_orders.user_id', (int)$idNumber)
                        ->orWhere('food_orders.driver_id', (int)$idNumber)
                        ->orWhere('food_orders.merchant_id', (int)$idNumber);
                }
                $builder = $builder->groupEnd();
            }

            if ($status !== '' && in_array($status, ['pending', 'processing', 'ready', 'accepted_by_driver', 'in_progress', 'completed', 'cancelled'])) {
                $builder = $builder->where('food_orders.status', $status);
            }

            $orders = $builder->orderBy('food_orders.created_at', 'DESC')->paginate($perPage);
            $pager = $foodOrderModel->pager;

            // Ambil rincian menu untuk setiap pesanan food_orders
            $foodOrderIds = array_column($orders, 'id');
            $foodItemsMap = [];
            if (!empty($foodOrderIds)) {
                $db = \Config\Database::connect();
                $items = $db->table('food_order_items')
                    ->select('food_order_items.food_order_id, food_order_items.quantity, food_order_items.price, merchant_menus.name as menu_name')
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

            foreach ($orders as &$ord) {
                $ord['items_summary'] = isset($foodItemsMap[$ord['id']]) ? implode(', ', $foodItemsMap[$ord['id']]) : '-';
            }
            unset($ord);

        } else {
            $builder = $orderModel->select('orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate')
                ->join('users', 'users.id = orders.user_id', 'left')
                ->join('drivers', 'drivers.id = orders.driver_id', 'left');

            if ($q !== '') {
                $builder = $builder->groupStart()
                    ->like('orders.pickup_address', $q)
                    ->orLike('orders.dropoff_address', $q)
                    ->orLike('users.name', $q)
                    ->orLike('drivers.name', $q);

                $idNumber = preg_replace('/[^0-9]/', '', $q);
                if ($idNumber !== '') {
                    $builder = $builder->orWhere('orders.id', (int)$idNumber)
                        ->orWhere('orders.user_id', (int)$idNumber)
                        ->orWhere('orders.driver_id', (int)$idNumber);
                }
                $builder = $builder->groupEnd();
            }

            if ($status !== '' && in_array($status, ['pending', 'accepted', 'in_progress', 'on_trip', 'completed', 'cancelled'])) {
                $builder = $builder->where('orders.status', $status);
            }

            $orders = $builder->orderBy('orders.created_at', 'DESC')->paginate($perPage);
            $pager = $orderModel->pager;
        }

        $data = [
            'title'      => 'Riwayat Pesanan | Maijek Admin',
            'orders'     => $orders,
            'pager'      => $pager,
            'perPage'    => $perPage,
            'q'          => $q,
            'status'     => $status,
            'tab'        => $tab,
            'totalRide'  => $totalRide,
            'totalFood'  => $totalFood,
            'totalCount' => $pager->getTotal(),
        ];

        return view('admin/orders/index', $data);
    }

    public function exportCsv($type = 'mairide')
    {
        $db = \Config\Database::connect();
        $startDate = $this->request->getGet('start_date') ?? '';
        $endDate = $this->request->getGet('end_date') ?? '';

        $hasDateFilter = (!empty($startDate) || !empty($endDate));
        if ($hasDateFilter) {
            if (empty($startDate)) $startDate = '2020-01-01';
            if (empty($endDate)) $endDate = date('Y-m-d');
        }

        if ($type == 'maifood') {
            $builder = $db->table('food_orders')
                ->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate, merchants.name as merchant_name')
                ->join('users', 'users.id = food_orders.user_id', 'left')
                ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
                ->join('merchants', 'merchants.id = food_orders.merchant_id', 'left');

            if ($hasDateFilter) {
                $builder->where('food_orders.created_at >=', $startDate . ' 00:00:00')
                        ->where('food_orders.created_at <=', $endDate . ' 23:59:59');
            }

            $orders = $builder->orderBy('food_orders.created_at', 'DESC')->get()->getResultArray();
            $filename = 'Laporan_Pesanan_MaiFood_' . ($hasDateFilter ? ($startDate . '_sd_' . $endDate) : date('Ymd')) . '.csv';
            $header = ["No", "ID Pesanan", "Waktu", "Status", "Pelanggan", "No HP Pelanggan", "Toko/Resto", "Driver/Kurir", "No HP Driver", "Plat Nomor", "Total Belanja & Ongkir", "Alamat Antar"];
        } else {
            $builder = $db->table('orders')
                ->select('orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate')
                ->join('users', 'users.id = orders.user_id', 'left')
                ->join('drivers', 'drivers.id = orders.driver_id', 'left');

            if ($hasDateFilter) {
                $builder->where('orders.created_at >=', $startDate . ' 00:00:00')
                        ->where('orders.created_at <=', $endDate . ' 23:59:59');
            }

            $orders = $builder->orderBy('orders.created_at', 'DESC')->get()->getResultArray();
            $filename = 'Laporan_Pesanan_MaiRide_' . ($hasDateFilter ? ($startDate . '_sd_' . $endDate) : date('Ymd')) . '.csv';
            $header = ["No", "ID Pesanan", "Waktu", "Layanan", "Status", "Pelanggan", "No HP Pelanggan", "Driver", "No HP Driver", "Plat Nomor", "Tarif (Rp)", "Jarak (KM)", "Alamat Jemput", "Alamat Tujuan"];
        }

        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: text/csv; charset=utf-8");
        header("Pragma: no-cache");
        header("Expires: 0");

        $file = fopen('php://output', 'w');
        // Excel UTF-8 BOM
        fputs($file, "\xEF\xBB\xBF");
        fputcsv($file, $header);

        $no = 1;
        foreach ($orders as $order) {
            if ($type == 'maifood') {
                fputcsv($file, [
                    $no++,
                    '#F' . $order['id'],
                    $order['created_at'],
                    $order['status'],
                    $order['user_name'] ?? 'Pelanggan #' . $order['user_id'],
                    $order['user_phone'] ?? '-',
                    $order['merchant_name'] ?? '-',
                    $order['driver_name'] ?? 'Belum ada driver',
                    $order['driver_phone'] ?? '-',
                    $order['vehicle_plate'] ?? '-',
                    $order['total_price'],
                    $order['delivery_address'] ?? '-'
                ]);
            } else {
                fputcsv($file, [
                    $no++,
                    '#' . $order['id'],
                    $order['created_at'],
                    $order['service_type'] ?? 'motor',
                    $order['status'],
                    $order['user_name'] ?? 'Pelanggan #' . $order['user_id'],
                    $order['user_phone'] ?? '-',
                    $order['driver_name'] ?? 'Belum ada driver',
                    $order['driver_phone'] ?? '-',
                    $order['vehicle_plate'] ?? '-',
                    $order['price'],
                    $order['distance'] ?? '0',
                    $order['pickup_address'] ?? '-',
                    $order['dropoff_address'] ?? '-'
                ]);
            }
        }

        fclose($file);
        exit;
    }

    public function forceCancel($type, $id)
    {
        $db = \Config\Database::connect();

        if ($type == 'maifood') {
            $order = $db->table('food_orders')->where('id', $id)->get()->getRow();
            if ($order && in_array($order->status, ['pending', 'processing', 'ready', 'accepted_by_driver', 'in_progress'])) {
                $db->table('food_orders')->where('id', $id)->update(['status' => 'cancelled']);

                // Refund Mai-Pay (Penuh atau Split Ongkir)
                if ($order->payment_method == 'maipay') {
                    $db->table('users')->where('id', $order->user_id)->set('balance', 'balance + ' . $order->total_price, false)->update();
                } else if ($order->payment_method == 'split_delivery_maipay') {
                    $db->table('users')->where('id', $order->user_id)->set('balance', 'balance + ' . $order->delivery_fee, false)->update();
                }

                session()->setFlashdata('success', 'Pesanan MaiFood berhasil dibatalkan paksa oleh Admin (Refund sukses)');
            }
            return redirect()->to('/admin/orders?tab=food');
        } else {
            $order = $db->table('orders')->where('id', $id)->get()->getRow();
            if ($order && in_array($order->status, ['pending', 'accepted', 'in_progress'])) {
                $db->table('orders')->where('id', $id)->update(['status' => 'cancelled']);

                // Refund Mai-Pay
                if ($order->payment_method == 'maipay') {
                    $db->table('users')->where('id', $order->user_id)->set('balance', 'balance + ' . $order->price, false)->update();
                }

                session()->setFlashdata('success', 'Pesanan MaiRide berhasil dibatalkan paksa oleh Admin (Refund sukses)');
            }
            return redirect()->to('/admin/orders?tab=ride');
        }
    }

    public function cancel($type, $id)
    {
        return $this->forceCancel($type, $id);
    }

    public function detail($type = 'ride', $id = 0)
    {
        $db = \Config\Database::connect();

        if ($type === 'maifood' || $type === 'food') {
            $order = $db->table('food_orders')
                ->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate, merchants.name as merchant_name, merchants.phone as merchant_phone, merchants.address as merchant_address, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng')
                ->join('users', 'users.id = food_orders.user_id', 'left')
                ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
                ->join('merchants', 'merchants.id = food_orders.merchant_id', 'left')
                ->where('food_orders.id', (int)$id)
                ->get()
                ->getRowArray();

            if (!$order) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'])->setStatusCode(404);
            }

            // Ambil rincian menu
            $items = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', (int)$id)
                ->get()
                ->getResultArray();

            $formattedItems = [];
            foreach ($items as $it) {
                $photo = null;
                if (!empty($it['menu_photo'])) {
                    $photo = str_starts_with($it['menu_photo'], 'http') ? $it['menu_photo'] : base_url($it['menu_photo']);
                }
                $formattedItems[] = [
                    'menu_name'       => !empty($it['menu_name']) ? $it['menu_name'] : 'Menu',
                    'quantity'        => (int)$it['quantity'],
                    'price'           => (float)$it['price'],
                    'subtotal'        => (float)($it['quantity'] * $it['price']),
                    'variant_details' => $it['variant_details'] ?? null,
                    'notes'           => $it['notes'] ?? null,
                    'menu_photo'      => $photo
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'type'   => 'food',
                'data'   => [
                    'id'               => $order['id'],
                    'display_id'       => '#F-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT),
                    'order_type'       => $order['order_type'] ?? 'food',
                    'service_label'    => ($order['order_type'] ?? 'food') === 'mart' ? 'MaiMart' : 'MaiFood',
                    'status'           => $order['status'],
                    'created_at'       => date('d M Y, H:i', strtotime($order['created_at'])) . ' WITA',
                    'payment_method'   => ($order['payment_method'] === 'split_delivery_maipay') ? 'SPLIT (ONGKIR MAIPAY)' : strtoupper($order['payment_method'] ?? 'CASH'),
                    'is_split_payment' => ($order['payment_method'] === 'split_delivery_maipay'),
                    'customer_name'    => !empty($order['user_name']) ? $order['user_name'] : 'Pelanggan #' . $order['user_id'],
                    'customer_phone'   => $order['user_phone'] ?? '-',
                    'merchant_name'    => $order['merchant_name'] ?? 'Mitra Resto/Toko',
                    'merchant_phone'   => $order['merchant_phone'] ?? '-',
                    'merchant_address' => $order['merchant_address'] ?? '-',
                    'merchant_lat'     => $order['merchant_lat'] ?? null,
                    'merchant_lng'     => $order['merchant_lng'] ?? null,
                    'delivery_address' => $order['delivery_address'] ?? '-',
                    'delivery_lat'     => $order['delivery_lat'] ?? null,
                    'delivery_lng'     => $order['delivery_lng'] ?? null,
                    'driver_name'      => $order['driver_name'] ?? null,
                    'driver_phone'     => $order['driver_phone'] ?? '-',
                    'driver_plate'     => $order['vehicle_plate'] ?? '-',
                    'items'            => $formattedItems,
                    'food_total'       => (float)($order['food_total'] ?? 0),
                    'delivery_fee'     => (float)($order['delivery_fee'] ?? 0),
                    'discount_amount'  => (float)($order['discount_amount'] ?? 0),
                    'promo_code'       => $order['promo_code'] ?? null,
                    'total_price'      => (float)($order['total_price'] ?? 0),
                    'distance'         => $order['distance'] ?? '0',
                    'cancel_reason'    => $order['cancel_reason'] ?? null,
                    'merchant_rating'  => $order['merchant_rating'] ?? null,
                    'driver_rating'    => $order['driver_rating'] ?? null,
                    'review_merchant'  => $order['review_merchant'] ?? null,
                    'review_driver'    => $order['review_driver'] ?? null,
                ]
            ]);
        } else {
            $order = $db->table('orders')
                ->select('orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate')
                ->join('users', 'users.id = orders.user_id', 'left')
                ->join('drivers', 'drivers.id = orders.driver_id', 'left')
                ->where('orders.id', (int)$id)
                ->get()
                ->getRowArray();

            if (!$order) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Pesanan tidak ditemukan'])->setStatusCode(404);
            }

            $vType = strtolower($order['service_type'] ?? 'motor');
            $serviceLabel = 'MaiRide';
            if (in_array($vType, ['car', 'mobil'])) {
                $serviceLabel = 'MaiCar';
            } elseif (str_contains($vType, 'send')) {
                $serviceLabel = 'MaiSend';
            } elseif (str_contains($vType, 'titip')) {
                $serviceLabel = 'MaiTitip';
            }

            return $this->response->setJSON([
                'status' => 'success',
                'type'   => 'ride',
                'data'   => [
                    'id'                => $order['id'],
                    'display_id'        => '#ORD-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT),
                    'service_type'      => $order['service_type'],
                    'service_label'     => $serviceLabel,
                    'status'            => $order['status'],
                    'created_at'        => date('d M Y, H:i', strtotime($order['created_at'])) . ' WITA',
                    'payment_method'    => strtoupper($order['payment_method'] ?? 'CASH'),
                    'customer_name'     => !empty($order['user_name']) ? $order['user_name'] : 'Pelanggan #' . $order['user_id'],
                    'customer_phone'    => $order['user_phone'] ?? '-',
                    'pickup_address'    => $order['pickup_address'] ?? '-',
                    'pickup_lat'        => $order['pickup_lat'] ?? null,
                    'pickup_lng'        => $order['pickup_lng'] ?? null,
                    'dropoff_address'   => $order['dropoff_address'] ?? '-',
                    'dropoff_lat'       => $order['dropoff_lat'] ?? null,
                    'dropoff_lng'       => $order['dropoff_lng'] ?? null,
                    'distance'          => $order['distance'] ?? '0',
                    'driver_name'       => $order['driver_name'] ?? null,
                    'driver_phone'      => $order['driver_phone'] ?? '-',
                    'driver_plate'      => $order['vehicle_plate'] ?? '-',
                    'recipient_name'    => $order['recipient_name'] ?? null,
                    'recipient_phone'   => $order['recipient_phone'] ?? null,
                    'item_description'  => $order['item_description'] ?? null,
                    'item_weight'       => $order['item_weight'] ?? null,
                    'proof_of_pickup'   => !empty($order['proof_of_pickup']) ? (str_starts_with($order['proof_of_pickup'], 'http') ? $order['proof_of_pickup'] : base_url($order['proof_of_pickup'])) : null,
                    'proof_of_delivery' => !empty($order['proof_of_delivery']) ? (str_starts_with($order['proof_of_delivery'], 'http') ? $order['proof_of_delivery'] : base_url($order['proof_of_delivery'])) : null,
                    'titip_item_price'  => $order['titip_item_price'] ?? null,
                    'titip_receipt_img' => !empty($order['titip_receipt_img']) ? (str_starts_with($order['titip_receipt_img'], 'http') ? $order['titip_receipt_img'] : base_url($order['titip_receipt_img'])) : null,
                    'titip_total_cost'  => $order['titip_total_cost'] ?? null,
                    'price'             => (float)($order['price'] ?? 0),
                    'discount_amount'   => (float)($order['discount_amount'] ?? 0),
                    'promo_code'        => $order['promo_code'] ?? null,
                    'cancel_reason'     => $order['cancel_reason'] ?? null,
                    'rating'            => $order['rating'] ?? null,
                    'review_comment'    => $order['review_comment'] ?? null,
                ]
            ]);
        }
    }
}
