<?php

namespace App\Controllers\Merchant;

use App\Controllers\BaseController;
use App\Models\FoodOrderModel;
use App\Models\MerchantModel;
use App\Libraries\FcmService;

class Orders extends BaseController
{
    public function live()
    {
        $merchantId = session()->get('merchant_id');
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->find($merchantId);
        $isMart = ($merchant && ($merchant['merchant_type'] ?? '') === 'mart');
        if (!session()->get('merchant_type') && $merchant) {
            session()->set('merchant_type', $merchant['merchant_type'] ?? 'food');
        }

        return view('merchant/orders/live', [
            'title'    => $isMart ? 'Pesanan Live Kasir | MaiMart Panel' : 'Pesanan Live Kasir | MaiFood Panel',
            'merchant' => $merchant,
            'isMart'   => $isMart
        ]);
    }

    public function apiLiveOrders()
    {
        $merchantId = session()->get('merchant_id');
        $db = \Config\Database::connect();

        $builder = $db->table('food_orders');
        $builder->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate as driver_plate, drivers.vehicle_type as driver_vehicle_type');
        $builder->join('users', 'users.id = food_orders.user_id', 'left');
        $builder->join('drivers', 'drivers.id = food_orders.driver_id', 'left');
        $builder->where('food_orders.merchant_id', $merchantId);
        $builder->whereIn('food_orders.status', ['pending', 'processing', 'accepted_by_merchant', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress']);
        $builder->orderBy('food_orders.created_at', 'DESC');
        $orders = $builder->get()->getResultArray();

        foreach ($orders as &$order) {
            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', $order['id'])
                ->get()
                ->getResultArray();
        }

        return $this->response->setJSON([
            'status' => 200,
            'data'   => $orders,
            'time'   => date('H:i:s')
        ]);
    }

    public function updateStatus($orderId)
    {
        $merchantId = session()->get('merchant_id');
        $status = $this->request->getPost('status');

        if (!in_array($status, ['processing', 'ready', 'cancelled'])) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Status tidak valid']);
        }

        $orderModel = new FoodOrderModel();
        $order = $orderModel->where('id', $orderId)->where('merchant_id', $merchantId)->first();

        if (!$order) {
            return $this->response->setJSON(['status' => 404, 'message' => 'Pesanan tidak ditemukan']);
        }

        // Proteksi Idempoten: Jika pesanan sudah dibatalkan sebelumnya, jangan lakukan mutasi refund ganda
        if ($status === 'cancelled' && $order['status'] === 'cancelled') {
            return $this->response->setJSON(['status' => 200, 'message' => 'Pesanan sudah dibatalkan sebelumnya']);
        }

        if ($status === 'cancelled' && ($order['status'] === 'completed' || $order['status'] === 'delivered')) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Pesanan sudah selesai dan tidak dapat dibatalkan']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $merchant = $db->table('merchants')->where('id', $merchantId)->get()->getRow();
        $isMart = ($merchant && ($merchant->merchant_type ?? '') === 'mart');
        $serviceName = $isMart ? 'MaiMart' : 'MaiFood';

        $updateData = ['status' => $status];
        if ($status === 'cancelled') {
            $reasonInput = trim($this->request->getPost('reason') ?? '');
            if (empty($reasonInput)) {
                $reasonInput = $isMart ? 'Stok barang / produk habis' : 'Stok menu makanan habis';
            }
            $updateData['cancel_reason'] = $reasonInput;
        }
        $orderModel->update($orderId, $updateData);

        // Jika dibatalkan dan bayar via Mai-Pay, kembalikan saldo ke pengguna (hanya jika belum cancelled)
        if ($status == 'cancelled' && $order['status'] !== 'cancelled' && $order['payment_method'] == 'maipay' && (float)$order['total_price'] > 0) {
            $db->table('users')->where('id', $order['user_id'])->set('balance', 'balance + ' . (float)$order['total_price'], false)->update();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['status' => 500, 'message' => 'Gagal mengubah status']);
        }

        // Kirim Notifikasi FCM
        $fcmService = new FcmService();

        // 1. Notif ke Pelanggan (Push Notification + Simpan ke Kotak Masuk)
        $title = "Status Pesanan {$serviceName}";
        $body = "Pesanan Anda sedang diproses.";
        if ($status == 'processing') {
            $title = $isMart ? "Toko Menyiapkan Belanjaan 📦" : "Restoran Menyiapkan Makanan 🍳";
            $body = $isMart 
                ? "Toko sedang menyiapkan barang belanjaan pesanan Anda." 
                : "Restoran sedang memasak dan menyiapkan makanan pesanan Anda.";
        } else if ($status == 'ready') {
            $title = $isMart ? "Belanjaan Siap & Menunggu Driver 🛍️" : "Makanan Siap & Menunggu Driver 🍲";
            $body = $isMart 
                ? "Pesanan Anda sudah selesai dikemas dan menunggu Driver penjemput." 
                : "Pesanan Anda sudah selesai dimasak dan menunggu Driver penjemput.";
        } else if ($status == 'cancelled') {
            $cancelReason = !empty($updateData['cancel_reason']) ? $updateData['cancel_reason'] : ($isMart ? 'Stok barang habis' : 'Stok menu habis');
            $title = $isMart ? "Pesanan Ditolak Toko ❌" : "Pesanan Ditolak Restoran ❌";
            $body = "Alasan: {$cancelReason}. " . ($order['payment_method'] === 'maipay' ? "Dana telah dikembalikan ke saldo Mai-Pay Anda." : "");
        }

        $fcmService->sendToUserWithInbox($order['user_id'], $title, $body, [
            'type'          => $isMart ? 'mart_status_update' : 'food_status_update',
            'order_id'      => (string)$orderId,
            'status'        => $status,
            'cancel_reason' => $updateData['cancel_reason'] ?? ''
        ]);

        // 2. Broadcast ke Driver jika status 'ready'
        if ($status == 'ready') {
            if ($merchant && $merchant->latitude && $merchant->longitude) {
                $pickupLat = $merchant->latitude;
                $pickupLng = $merchant->longitude;
                $targetVehicle = $order['vehicle_type'] ?? 'motor';

                $builder = $db->table('drivers');
                $builder->select("*, (6371 * acos(cos(radians($pickupLat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($pickupLng)) + sin(radians($pickupLat)) * sin(radians(latitude)))) AS distance");
                $builder->where('is_online', 1);
                $builder->where('status', 'active');
                $builder->where('fcm_token !=', null);
                if (in_array(strtolower($targetVehicle), ['mobil', 'car'])) {
                    $builder->whereIn('vehicle_type', ['mobil', 'Mobil', 'MOBIL', 'car', 'Car', 'car_premium']);
                } else {
                    $builder->whereIn('vehicle_type', ['motor', 'Motor', 'MOTOR']);
                }
                $builder->having('distance <=', 10);
                $builder->orderBy('distance', 'ASC');
                $drivers = $builder->get()->getResultArray();

                foreach ($drivers as $d) {
                    $fcmService->sendPushNotification(
                        $d['fcm_token'],
                        ($isMart ? 'Pesanan Belanja Siap Diantar (' : 'Pesanan Makanan Siap Diantar (') . ($targetVehicle == 'mobil' ? 'Mobil 🚗' : 'Motor 🛵') . ')!',
                        ($isMart ? 'Toko ' : 'Restoran ') . $merchant->name . ' telah menyiapkan pesanan. Ketuk untuk ambil order!',
                        [
                            'type'         => 'new_order',
                            'order_type'   => $isMart ? 'mart' : 'food',
                            'order_id'     => (string)$orderId,
                            'vehicle_type' => $targetVehicle
                        ]
                    );
                }
            }
        }

        // 3. Notif ke Driver jika pesanan dibatalkan resto dan sudah ada driver ditugaskan
        if ($status == 'cancelled' && !empty($order['driver_id'])) {
            $driver = $db->table('drivers')->where('id', $order['driver_id'])->get()->getRow();
            if ($driver && !empty($driver->fcm_token)) {
                $fcmService->sendPushNotification(
                    $driver->fcm_token,
                    $isMart ? 'Pesanan Dibatalkan Toko' : 'Pesanan Dibatalkan Restoran',
                    $isMart ? 'Toko telah membatalkan pesanan #' . $orderId . '.' : 'Restoran telah membatalkan pesanan #' . $orderId . '.',
                    [
                        'type'       => $isMart ? 'mart_order_cancelled' : 'food_order_cancelled',
                        'order_id'   => (string)$orderId,
                        'order_type' => $isMart ? 'mart' : 'food'
                    ]
                );
            }
        }

        return $this->response->setJSON([
            'status'  => 200,
            'message' => 'Status pesanan berhasil diperbarui'
        ]);
    }

    public function printReceipt($orderId)
    {
        $merchantId = session()->get('merchant_id');
        $db = \Config\Database::connect();

        $merchant = $db->table('merchants')->where('id', $merchantId)->get()->getRowArray();
        $order = $db->table('food_orders')
            ->select('food_orders.*, users.name as user_name, users.phone as user_phone, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate as driver_plate')
            ->join('users', 'users.id = food_orders.user_id', 'left')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('food_orders.id', $orderId)
            ->where('food_orders.merchant_id', $merchantId)
            ->get()
            ->getRowArray();

        if (!$order) {
            return "Pesanan tidak ditemukan.";
        }

        $items = $db->table('food_order_items')
            ->select('food_order_items.*, merchant_menus.name as menu_name')
            ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
            ->where('food_order_items.food_order_id', $orderId)
            ->get()
            ->getResultArray();

        $isMart = ($merchant && ($merchant['merchant_type'] ?? '') === 'mart');

        return view('merchant/orders/print', [
            'merchant' => $merchant,
            'order'    => $order,
            'items'    => $items,
            'isMart'   => $isMart
        ]);
    }
}
