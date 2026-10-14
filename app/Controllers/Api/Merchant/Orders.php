<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FoodOrderModel;
use App\Models\FoodOrderItemModel;
use App\Models\UserModel;
use App\Libraries\FcmService;

class Orders extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $db = \Config\Database::connect();
        $builder = $db->table('food_orders');
        $builder->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate as driver_plate, drivers.photo as driver_photo');
        $builder->join('users', 'users.id = food_orders.user_id', 'left');
        $builder->join('drivers', 'drivers.id = food_orders.driver_id', 'left');
        $builder->where('food_orders.merchant_id', $merchant_id);
        $builder->orderBy('food_orders.created_at', 'DESC');
        $orders = $builder->get()->getResultArray();

        // Fetch items for each order
        foreach ($orders as &$order) {
            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', $order['id'])
                ->get()
                ->getResultArray();
        }

        return $this->respond(['status' => 200, 'message' => 'Daftar pesanan', 'data' => $orders]);
    }

    public function active()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $db = \Config\Database::connect();
        $builder = $db->table('food_orders');
        $builder->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate as driver_plate, drivers.photo as driver_photo');
        $builder->join('users', 'users.id = food_orders.user_id', 'left');
        $builder->join('drivers', 'drivers.id = food_orders.driver_id', 'left');
        $builder->where('food_orders.merchant_id', $merchant_id);
        $builder->whereIn('food_orders.status', ['pending', 'processing', 'accepted_by_merchant', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress']);
        $builder->orderBy('food_orders.created_at', 'DESC');
        $orders = $builder->get()->getResultArray();

        // Fetch items for each order
        foreach ($orders as &$order) {
            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', $order['id'])
                ->get()
                ->getResultArray();
        }

        return $this->respond(['status' => 200, 'message' => 'Daftar pesanan aktif', 'data' => $orders]);
    }

    public function show($id = null)
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $db = \Config\Database::connect();
        $order = $db->table('food_orders')
            ->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate as driver_plate, drivers.photo as driver_photo')
            ->join('users', 'users.id = food_orders.user_id', 'left')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('food_orders.id', $id)
            ->where('food_orders.merchant_id', $merchant_id)
            ->get()
            ->getRowArray();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $order['items'] = $db->table('food_order_items')
            ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
            ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
            ->where('food_order_items.food_order_id', $id)
            ->get()
            ->getResultArray();

        return $this->respond(['status' => 200, 'message' => 'Detail pesanan', 'data' => $order]);
    }

    public function updateStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $rules = [
            'order_id' => 'required|numeric',
            'status' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal'], 400);
        }

        $orderId = $this->request->getVar('order_id');
        $status = $this->request->getVar('status');
        $reason = $this->request->getVar('reason') ?? 'Ditolak oleh restoran';

        $orderModel = new \App\Models\FoodOrderModel();
        $order = $orderModel->where('id', $orderId)->where('merchant_id', $merchant_id)->first();
        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        // Proteksi Idempoten: Jika pesanan sudah dibatalkan sebelumnya, jangan lakukan mutasi refund ganda
        if ($status === 'cancelled' && $order['status'] === 'cancelled') {
            return $this->respond(['status' => 200, 'message' => 'Pesanan sudah dibatalkan sebelumnya']);
        }

        if ($status === 'cancelled' && ($order['status'] === 'completed' || $order['status'] === 'delivered')) {
            return $this->respond(['status' => 400, 'message' => 'Pesanan sudah selesai dan tidak dapat dibatalkan']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $updateData = ['status' => $status];
        if ($status === 'cancelled') {
            $reason = trim($reason ?? '');
            if (empty($reason)) {
                $reason = $isMart ? 'Stok barang / produk habis' : 'Stok menu makanan habis';
            }
            $updateData['cancel_reason'] = $reason;
        }
        $orderModel->update($orderId, $updateData);

        // Jika dibatalkan (TOLAK) dan pakai maipay, kembalikan saldo ke pengguna secara atomik (hanya jika belum cancelled)
        if ($status == 'cancelled' && $order['status'] !== 'cancelled' && $order['payment_method'] == 'maipay' && (float)$order['total_price'] > 0) {
            $db->table('users')->where('id', $order['user_id'])->set('balance', 'balance + ' . (float)$order['total_price'], false)->update();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(['status' => 500, 'message' => 'Gagal mengubah status pesanan'], 500);
        }

        // Kirim Notifikasi FCM
        $fcmService = new FcmService();

        $isMart = (isset($order['order_type']) && $order['order_type'] === 'mart');
        if (!$isMart && !empty($order['merchant_id'])) {
            $mCheck = $db->table('merchants')->select('merchant_type')->where('id', $order['merchant_id'])->get()->getRow();
            if ($mCheck && ($mCheck->merchant_type ?? '') === 'mart') {
                $isMart = true;
            }
        }

        // Notif ke User (Push Notification + Simpan ke Kotak Masuk)
        $serviceName = $isMart ? 'MaiMart' : 'MaiFood';
        $title = "Status Pesanan {$serviceName}";
        $body = "Pesanan Anda diperbarui.";
        if ($status == 'processing') {
            $title = $isMart ? "Toko Menyiapkan Belanjaan 📦" : "Restoran Menyiapkan Pesanan 🍳";
            $body = $isMart 
                ? "Toko sedang menyiapkan barang belanjaan {$serviceName} Anda." 
                : "Restoran sedang memasak dan menyiapkan pesanan MaiFood Anda.";
        } else if ($status == 'ready') {
            $title = $isMart ? "Belanjaan Siap & Menunggu Driver 🛍️" : "Makanan Siap & Menunggu Driver 🍲";
            $body = "Pesanan Anda sudah siap dan sedang dicarikan driver terdekat.";
        } else if ($status == 'cancelled') {
            $cancelReason = !empty($updateData['cancel_reason']) ? $updateData['cancel_reason'] : ($isMart ? 'Stok barang habis' : 'Stok menu habis');
            $title = $isMart ? "Pesanan Ditolak Toko ❌" : "Pesanan Ditolak Restoran ❌";
            $body = "Alasan: {$cancelReason}. " . ($order['payment_method'] === 'maipay' ? "Dana telah dikembalikan ke saldo Mai-Pay Anda." : "");
        }

        $fcmService->sendToUserWithInbox(
            $order['user_id'],
            $title,
            $body,
            ['type' => $isMart ? 'mart_status_update' : 'food_status_update', 'order_id' => $orderId, 'status' => $status, 'cancel_reason' => $updateData['cancel_reason'] ?? '']
        );

        // Broadcast ke Driver terdekat (Radius 15 KM) jika status 'ready'
        if ($status == 'ready') {
            $merchant = $db->table('merchants')->where('id', $order['merchant_id'])->get()->getRow();

            if ($merchant && $merchant->latitude != null && $merchant->longitude != null) {
                $pickupLat = $merchant->latitude;
                $pickupLng = $merchant->longitude;
                $radius = 15; // 15 KM

                $builder = $db->table('drivers');
                $builder->select('drivers.*, (
                    6371 * acos(
                        cos(radians(' . $db->escape($pickupLat) . ')) * 
                        cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(' . $db->escape($pickupLng) . ')) + 
                        sin(radians(' . $db->escape($pickupLat) . ')) * 
                        sin(radians(latitude))
                    )
                ) AS distance');
                $builder->where('is_online', 1);
                $builder->where('status', 'active');
                $builder->where('verification_status', 'approved');
                $builder->where('fcm_token !=', null);
                $builder->where('fcm_token !=', '');

                // --- Auto-Migration & ZOMBIE DRIVER FILTER ---
                try {
                    if ($db->fieldExists('last_heartbeat_at', 'drivers')) {
                        // Abaikan driver yang detaknya sudah mati > 1 jam yang lalu (longgar karena testing)
                        $builder->where('last_heartbeat_at >=', date('Y-m-d H:i:s', strtotime('-1 hours')));
                    }
                } catch (\Throwable $e) {
                }
                // -----------------------------

                // Filter driver sesuai kendaraan yang dipilih pemesan (Motor vs Mobil)
                $targetVehicle = strtolower($order['vehicle_type'] ?? 'motor');
                if (in_array($targetVehicle, ['mobil', 'car'])) {
                    $builder->whereIn('vehicle_type', ['mobil', 'Mobil', 'MOBIL', 'car', 'Car', 'car_premium']);
                } else {
                    $builder->whereIn('vehicle_type', ['motor', 'Motor', 'MOTOR']);
                }

                $builder->having('distance <=', $radius);
                $builder->orderBy('distance', 'ASC');
                $builder->limit(20);

                $nearbyDrivers = $builder->get()->getResult();

                $vehicleLabel = in_array($targetVehicle, ['mobil', 'car']) ? 'Kurir Mobil 🚗' : 'Kurir Motor 🛵';
                try {
                    foreach ($nearbyDrivers as $driver) {
                        if (!empty($driver->fcm_token)) {
                            $fcmService->sendPushNotification(
                                $driver->fcm_token,
                                ($isMart ? "Order MaiMart (" : "Order MaiFood (") . $vehicleLabel . ")!",
                                $isMart 
                                    ? "Ada pesanan belanja siap diantar dari toko berjarak " . round($driver->distance, 1) . " KM." 
                                    : "Ada pesanan makanan siap diantar dari restoran berjarak " . round($driver->distance, 1) . " KM.",
                                ['type' => $isMart ? 'new_mart_order' : 'new_food_order', 'order_id' => $orderId, 'vehicle_type' => $targetVehicle]
                            );
                        }
                    }
                } catch (\Throwable $fcmEx) {
                    log_message('error', 'Merchant Food FCM error: ' . $fcmEx->getMessage());
                }
            }
        }

        // Notif ke Driver jika pesanan dibatalkan resto dan sudah ada driver ditugaskan
        if ($status == 'cancelled' && !empty($order['driver_id'])) {
            $driver = $db->table('drivers')->where('id', $order['driver_id'])->get()->getRow();
            if ($driver && !empty($driver->fcm_token)) {
                $fcmService->sendPushNotification(
                    $driver->fcm_token,
                    $isMart ? 'Pesanan Toko Dibatalkan' : 'Pesanan Dibatalkan Restoran',
                    $isMart ? 'Pesanan MaiMart #' . $orderId . ' dibatalkan oleh toko.' : 'Restoran telah membatalkan pesanan #' . $orderId . '.',
                    [
                        'type'       => $isMart ? 'mart_order_cancelled' : 'food_order_cancelled',
                        'order_id'   => (string)$orderId,
                        'order_type' => $isMart ? 'mart' : 'food'
                    ]
                );
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Status pesanan berhasil diperbarui']);
    }

    public function history()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new \App\Models\FoodOrderModel();
        $db = \Config\Database::connect();

        $builder = $orderModel->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate as driver_plate')
            ->join('users', 'users.id = food_orders.user_id')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('merchant_id', $payload['uid'])
            ->whereIn('food_orders.status', ['completed', 'cancelled', 'delivered']);

        $dateFilter = $this->request->getVar('date');
        if ($dateFilter) {
            $builder->where('DATE(food_orders.created_at)', $dateFilter);
        }

        $history = $builder->orderBy('food_orders.created_at', 'DESC')->findAll();

        $totalEarnings = 0;
        $totalOrders = 0;
        foreach ($history as &$order) {
            if ($order['status'] == 'completed' || $order['status'] == 'delivered') {
                $totalEarnings += (int)$order['food_total'];
                $totalOrders++;
            }

            // Fetch items for history
            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', $order['id'])
                ->get()
                ->getResultArray();
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Riwayat pesanan restoran',
            'data' => [
                'history' => $history,
                'summary' => [
                    'total_earnings' => $totalEarnings,
                    'total_orders' => $totalOrders
                ]
            ]
        ]);
    }
}
