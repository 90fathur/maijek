<?php

namespace App\Controllers\Api\Driver;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FoodOrderModel;
use App\Models\FoodOrderItemModel;
use App\Models\UserModel;
use App\Models\MerchantModel;
use App\Models\DriverModel;
use App\Libraries\FcmService;

class Food extends ResourceController
{
    protected $format = 'json';

    // Mendapatkan order makanan yang belum diambil oleh driver (pending / accepted_by_merchant)
    public function available()
    {
        $orderModel = new FoodOrderModel();

        // --- Auto-Migration & SISTEM HEARTBEAT ---
        if (isset($_SERVER['jwt_payload']['uid'])) {
            $db = \Config\Database::connect();
            try {
                if ($db->fieldExists('last_heartbeat_at', 'drivers')) {
                    $db->table('drivers')->where('id', $_SERVER['jwt_payload']['uid'])->update([
                        'last_heartbeat_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (\Throwable $e) {
            }
        }
        // ------------------------

        $driverModel = new \App\Models\DriverModel();
        $driver = $driverModel->find($_SERVER['jwt_payload']['uid'] ?? 0);
        $driverVehicleType = strtolower($driver['vehicle_type'] ?? 'motor');

        // Ambil order yang statusnya siap dijemput oleh driver (ready)
        $orderBuilder = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('users', 'users.id = food_orders.user_id')
            ->where('driver_id', null)
            ->where('food_orders.status', 'ready');

        if (in_array($driverVehicleType, ['mobil', 'car'])) {
            $orderBuilder->whereIn('food_orders.vehicle_type', ['mobil', 'car']);
        } else {
            $orderBuilder->groupStart()
                ->where('food_orders.vehicle_type', 'motor')
                ->orWhere('food_orders.vehicle_type', null)
                ->orWhere('food_orders.vehicle_type', '')
                ->groupEnd();
        }

        $orders = $orderBuilder->orderBy('created_at', 'DESC')->findAll();

        $settingModel = new \App\Models\SettingModel();
        $feeSetting = $settingModel->where('setting_key', 'food_platform_fee')->first();
        $feeTypeSetting = $settingModel->where('setting_key', 'food_platform_fee_type')->first();
        $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
        $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

        $db = \Config\Database::connect();
        foreach ($orders as &$order) {
            $this->enrichFoodFinancials($order, $platformFee, $platformFeeType);

            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_id', $order['id'])
                ->get()
                ->getResultArray();

            if (!empty($order['user_photo'])) {
                $order['user_photo'] = (strpos($order['user_photo'], 'http') === 0)
                    ? $order['user_photo']
                    : base_url('uploads/profiles/' . $order['user_photo']);
            } else {
                $order['user_photo'] = '';
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Pesanan makanan tersedia', 'data' => $orders]);
    }

    public function active()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new FoodOrderModel();

        $activeOrders = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('users', 'users.id = food_orders.user_id')
            ->where('driver_id', $payload['uid'])
            ->whereIn('food_orders.status', ['driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress'])
            ->findAll();

        if (empty($activeOrders)) {
            return $this->respond(['status' => 404, 'message' => 'Tidak ada pesanan makanan aktif', 'data' => []], 404);
        }

        $settingModel = new \App\Models\SettingModel();
        $feeSetting = $settingModel->where('setting_key', 'food_platform_fee')->first();
        $feeTypeSetting = $settingModel->where('setting_key', 'food_platform_fee_type')->first();
        $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
        $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

        $db = \Config\Database::connect();
        foreach ($activeOrders as &$order) {
            $this->enrichFoodFinancials($order, $platformFee, $platformFeeType);

            $order['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_id', $order['id'])
                ->get()
                ->getResultArray();

            if (!empty($order['user_photo'])) {
                $order['user_photo'] = (strpos($order['user_photo'], 'http') === 0)
                    ? $order['user_photo']
                    : base_url('uploads/profiles/' . $order['user_photo']);
            } else {
                $order['user_photo'] = '';
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Pesanan makanan aktif ditemukan', 'data' => $activeOrders]);
    }

    public function detail($id = null)
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new FoodOrderModel();

        $order = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.phone as merchant_phone, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('users', 'users.id = food_orders.user_id')
            ->where('food_orders.id', $id)
            ->where('food_orders.driver_id', $payload['uid'])
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan makanan tidak ditemukan'], 404);
        }

        $db = \Config\Database::connect();
        $order['items'] = $db->table('food_order_items')
            ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
            ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
            ->where('food_order_id', $order['id'])
            ->get()
            ->getResultArray();

        $settingModel = new \App\Models\SettingModel();
        $feeSetting = $settingModel->where('setting_key', 'food_platform_fee')->first();
        $feeTypeSetting = $settingModel->where('setting_key', 'food_platform_fee_type')->first();
        $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
        $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

        $this->enrichFoodFinancials($order, $platformFee, $platformFeeType);

        if (!empty($order['user_photo'])) {
            $order['user_photo'] = (strpos($order['user_photo'], 'http') === 0)
                ? $order['user_photo']
                : base_url('uploads/profiles/' . $order['user_photo']);
        } else {
            $order['user_photo'] = '';
        }

        return $this->respond(['status' => 200, 'message' => 'Detail pesanan makanan', 'data' => $order]);
    }

    public function accept()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getVar('order_id');

        $orderModel = new FoodOrderModel();
        $order = $orderModel->find($orderId);

        if (!$order) return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);

        $driverModel = new \App\Models\DriverModel();
        $driver = $driverModel->find($payload['uid']);

        // Cek kecocokan tipe kendaraan (Motor vs Mobil)
        $driverVehicleType = strtolower($driver['vehicle_type'] ?? 'motor');
        $orderVehicleType = strtolower($order['vehicle_type'] ?? 'motor');
        $isDriverCar = in_array($driverVehicleType, ['mobil', 'car']);
        $isOrderCar = in_array($orderVehicleType, ['mobil', 'car']);

        if ($isDriverCar !== $isOrderCar) {
            return $this->respond([
                'status' => 400,
                'message' => $isOrderCar
                    ? 'Pesanan ini membutuhkan kurir mobil (porsi besar/katering).'
                    : 'Pesanan ini khusus untuk kurir motor.'
            ], 400);
        }

        // Cek Komisi & Saldo untuk metode Tunai
        if (isset($order['payment_method']) && $order['payment_method'] == 'cash') {
            $settingModel = new \App\Models\SettingModel();
            $feeSetting = $settingModel->where('setting_key', 'food_platform_fee')->first();
            $feeTypeSetting = $settingModel->where('setting_key', 'food_platform_fee_type')->first();

            $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
            $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

            $komisi = 0;
            if ($platformFeeType == 'percent') {
                $komisi = round((float)$order['delivery_fee'] * ($platformFee / 100));
            } else {
                $komisi = round($platformFee);
            }

            $discountSubsidi = (float)($order['discount_amount'] ?? 0);
            $netDeduction = $komisi - $discountSubsidi;
            $driverBalance = (float)($driver['balance'] ?? 0);

            // Ambil Pengaturan Kebijakan Saldo Driver dari Admin Panel
            $zeroBalanceSetting = $settingModel->where('setting_key', 'driver_zero_balance_enabled')->first();
            $isZeroBalanceEnabled = $zeroBalanceSetting ? ($zeroBalanceSetting['setting_value'] === '1') : false;

            $minBalanceSetting = $settingModel->where('setting_key', 'driver_min_balance')->first();
            $minBalance = $minBalanceSetting ? (float)$minBalanceSetting['setting_value'] : 10000;

            $promoBypassSetting = $settingModel->where('setting_key', 'driver_promo_zero_balance_bypass')->first();
            $isPromoBypass = $promoBypassSetting ? ($promoBypassSetting['setting_value'] === '1') : true;

            // Evaluasi Kebijakan Saldo:
            // 1. Jika ada promo cashback (subsidi promo >= komisi aplikasi) dan promo bypass aktif:
            //    Driver diizinkan menarik pesanan walaupun saldo Rp 0 (karena saldo akan bertambah, misal +Rp 120).
            if ($netDeduction <= 0 && $isPromoBypass) {
                // Diizinkan menarik order (Bebas Saldo Rp 0 saat Promo Cashback)
            } elseif ($isZeroBalanceEnabled) {
                // 2. Jika Fitur Saldo Driver Rp 0 AKTIF di Admin:
                //    Driver bebas menarik order kapanpun meski saldo Rp 0 / tanpa saldo.
            } else {
                // 3. Jika Fitur Saldo Rp 0 NONAKTIF (Kembali ke aturan standar batas minimal saldo):
                if ($driverBalance < $minBalance) {
                    return $this->respond([
                        'status' => 400,
                        'message' => 'Saldo Mai-Pay Anda (Rp ' . number_format($driverBalance, 0, ',', '.') . ') kurang dari batas minimal Rp ' . number_format($minBalance, 0, ',', '.') . '. Silakan top up saldo terlebih dahulu untuk menerima pesanan.'
                    ], 400);
                }

                if ($netDeduction > 0 && $driverBalance < $netDeduction) {
                    return $this->respond([
                        'status' => 400,
                        'message' => 'Saldo Mai-Pay Anda tidak cukup untuk potongan komisi pesanan ini. Saldo minimal: Rp ' . number_format($netDeduction, 0, ',', '.')
                    ], 400);
                }
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Anti-Race Condition & Idempotency: Kunci pesanan makanan jika belum ada driver atau jika sudah diambil oleh driver ini sendiri
        $db->table('food_orders')
            ->where('id', $orderId)
            ->groupStart()
                ->whereIn('status', ['ready', 'processing', 'accepted_by_merchant', 'accepted', 'driver_assigned'])
            ->groupEnd()
            ->groupStart()
                ->where('driver_id', null)
                ->orWhere('driver_id', 0)
                ->orWhere('driver_id', '')
                ->orWhere('driver_id', $payload['uid'])
            ->groupEnd()
            ->update([
                'driver_id' => $payload['uid'],
                'status' => 'driver_assigned'
            ]);

        // Cek apakah update berhasil (artinya pesanan belum diambil orang lain)
        if ($db->affectedRows() == 0) {
            $checkSelf = $db->table('food_orders')->where('id', $orderId)->where('driver_id', $payload['uid'])->countAllResults();
            if ($checkSelf == 0) {
                $db->transRollback();
                return $this->respond(['status' => 400, 'message' => 'Maaf, pesanan ini baru saja diambil oleh supir lain'], 400);
            }
        }

        $db->transComplete();

        // Kirim Notifikasi FCM
        $fcmService = new FcmService();
        $db = \Config\Database::connect();

        $driverName = $payload['name'] ?? 'Driver';

        $isMart = (isset($order['order_type']) && $order['order_type'] === 'mart');
        if (!$isMart && !empty($order['merchant_id'])) {
            $mCheck = $db->table('merchants')->select('merchant_type')->where('id', $order['merchant_id'])->get()->getRow();
            if ($mCheck && ($mCheck->merchant_type ?? '') === 'mart') {
                $isMart = true;
            }
        }

        // Ke User (Push Notification + Simpan ke Kotak Masuk)
        $userTitle = $isMart ? "Driver Menuju Toko 🛍️" : "Driver Menuju Restoran 🛵";
        $userBody = $isMart
            ? "Driver $driverName sedang dalam perjalanan menuju ke toko untuk mengambil barang belanjaan Anda."
            : "Driver $driverName sedang dalam perjalanan menuju ke restoran untuk mengambil pesanan Anda.";

        $fcmService->sendToUserWithInbox(
            $order['user_id'],
            $userTitle,
            $userBody,
            ['type' => $isMart ? 'mart_status_update' : 'food_status_update', 'order_id' => $orderId, 'status' => 'driver_assigned']
        );

        // Ke Merchant
        $merchant = $db->table('merchants')->where('id', $order['merchant_id'])->get()->getRow();
        if ($merchant && !empty($merchant->fcm_token)) {
            $mTitle = $isMart ? "Driver Menuju Toko" : "Driver Menuju Lokasi";
            $mBody = $isMart ? "Driver $driverName akan mengambil barang belanjaan." : "Driver $driverName akan mengambil pesanan.";
            $fcmService->sendPushNotification($merchant->fcm_token, $mTitle, $mBody, ['type' => $isMart ? 'mart_status_update' : 'food_status_update', 'order_id' => $orderId]);
        }

        // Ambil data pesanan makanan terbaru secara lengkap untuk langsung dipakai frontend Driver
        $orderModel = new FoodOrderModel();
        $updatedFoodOrder = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.phone as merchant_phone, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('users', 'users.id = food_orders.user_id')
            ->where('food_orders.id', $orderId)
            ->first();

        if ($updatedFoodOrder) {
            $settingModel = new \App\Models\SettingModel();
            $feeSetting = $settingModel->where('setting_key', 'food_platform_fee')->first();
            $feeTypeSetting = $settingModel->where('setting_key', 'food_platform_fee_type')->first();
            $pFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
            $pFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

            $this->enrichFoodFinancials($updatedFoodOrder, $pFee, $pFeeType);

            $updatedFoodOrder['items'] = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_id', $orderId)
                ->get()
                ->getResultArray();

            if (!empty($updatedFoodOrder['user_photo']) && strpos($updatedFoodOrder['user_photo'], 'http') !== 0) {
                $updatedFoodOrder['user_photo'] = base_url('uploads/profiles/' . $updatedFoodOrder['user_photo']);
            }

            $updatedFoodOrder['order_type'] = ($updatedFoodOrder['order_type'] ?? '') === 'mart' ? 'mart' : 'food';
            $updatedFoodOrder['status'] = 'driver_assigned';
        }

        $orderType = ($order['order_type'] ?? '') === 'mart' ? 'mart' : 'food';

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil pesanan',
            'data' => $updatedFoodOrder ?: ['id' => $orderId, 'status' => 'driver_assigned', 'order_type' => $orderType]
        ]);
    }

    public function updateStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getVar('order_id');
        $newStatus = $this->request->getVar('status');

        $orderModel = new FoodOrderModel();
        $order = $orderModel->find($orderId);
        $db = \Config\Database::connect();

        if (!$order || $order['driver_id'] != $payload['uid']) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak'], 403);
        }

        // Anti-Tampering: Jika pesanan sudah dibatalkan sebelumnya, jangan ubah status
        if ($order['status'] === 'cancelled' && $newStatus !== 'cancelled') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan ini sudah dibatalkan sebelumnya.'], 400);
        }

        $isMart = (isset($order['order_type']) && $order['order_type'] === 'mart');
        if (!$isMart && !empty($order['merchant_id'])) {
            $mCheck = $db->table('merchants')->select('merchant_type')->where('id', $order['merchant_id'])->get()->getRow();
            if ($mCheck && ($mCheck->merchant_type ?? '') === 'mart') {
                $isMart = true;
            }
        }

        if ($newStatus == 'completed') {
            // Jika pesanan sudah completed sebelumnya, langsung kembalikan respon sukses tanpa duplikasi mutasi saldo
            if ($order['status'] === 'completed') {
                return $this->respond(['status' => 200, 'message' => 'Status pesanan berhasil diubah dan pembayaran diselesaikan', 'data' => ['id' => (int)$orderId, 'status' => 'completed']]);
            }

            $db->transStart();

            // Update order status
            $db->table('food_orders')->where('id', $orderId)->update(['status' => 'completed']);

            // Hitung Komisi dari Ongkir (delivery_fee)
            $settingModel = new \App\Models\SettingModel();
            $feeSetting = $settingModel->where('setting_key', 'food_platform_fee')->first();
            $feeTypeSetting = $settingModel->where('setting_key', 'food_platform_fee_type')->first();

            $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 15;
            $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

            $komisi = 0;
            if ($platformFeeType == 'percent') {
                $komisi = $order['delivery_fee'] * ($platformFee / 100);
            } else {
                $komisi = $platformFee;
            }

            if ($order['payment_method'] == 'maipay') {
                // Tambah saldo driver (delivery_fee dikurangi komisi)
                $driverEarn = (float)$order['delivery_fee'] - $komisi;
                $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance + ' . $driverEarn, false)->update();

                if ($db->tableExists('transactions')) {
                    $db->table('transactions')->insert([
                        'driver_id'   => $payload['uid'],
                        'user_id'     => $order['user_id'],
                        'type'        => 'credit',
                        'amount'      => (int)round($driverEarn),
                        'description' => "Pendapatan Ongkir Food #{$orderId} (Ongkir Rp " . number_format($order['delivery_fee'], 0, ',', '.') . " - Fee Rp " . number_format($komisi, 0, ',', '.') . ")",
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }

                // Tambah saldo merchant (food_total utuh)
                $db->table('merchants')->where('id', $order['merchant_id'])->set('balance', 'balance + ' . $order['food_total'], false)->update();
            } else if ($order['payment_method'] == 'split_delivery_maipay') {
                // SPLIT PAYMENT:
                // Ongkir dibayar via MaiPay digital (sudah dipotong di awal dari user).
                // Makanan dibayar tunai oleh driver ke merchant, dan ditagih tunai oleh driver ke pelanggan.
                // Merchant TIDAK ditambah saldo digitalnya (karena sudah terima fisik tunai dari driver).
                // Driver mendapatkan: Ongkir Bersih ($deliveryFee - $komisi) + Subsidi Diskon Promo (jika ada).
                $driverEarn = (float)$order['delivery_fee'] - $komisi;
                $discountSubsidi = (float)($order['discount_amount'] ?? 0);
                $totalCredit = $driverEarn + $discountSubsidi;

                if ($totalCredit > 0) {
                    $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance + ' . $totalCredit, false)->update();

                    if ($db->tableExists('transactions')) {
                        $desc = "Pendapatan Ongkir Split Food #{$orderId} (Bersih Rp " . number_format($driverEarn, 0, ',', '.');
                        if ($discountSubsidi > 0) {
                            $desc .= " + Subsidi Diskon Rp " . number_format($discountSubsidi, 0, ',', '.');
                        }
                        $desc .= ")";

                        $db->table('transactions')->insert([
                            'driver_id'   => $payload['uid'],
                            'user_id'     => $order['user_id'],
                            'type'        => 'credit',
                            'amount'      => (int)round($totalCredit),
                            'description' => $desc,
                            'created_at'  => date('Y-m-d H:i:s'),
                            'updated_at'  => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            } else {
                // Tunai: Penumpang membayar tunai sebesar total_price (yang sudah terpotong discount_amount).
                // Driver menalangi kasir merchant sebesar food_total utuh, sehingga fisik uang tunai di tangan driver berkurang sebesar discount_amount.
                // Selisih subsidi promo (discount_amount) WAJIB diganti oleh platform ke saldo dompet driver.
                // Net penyesuaian saldo = discount_amount - komisi.
                $discountSubsidi = (float)($order['discount_amount'] ?? 0);
                $netAdjustment = $discountSubsidi - $komisi;
                if ($netAdjustment >= 0) {
                    $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance + ' . $netAdjustment, false)->update();
                    if ($netAdjustment > 0 && $db->tableExists('transactions')) {
                        $db->table('transactions')->insert([
                            'driver_id'   => $payload['uid'],
                            'user_id'     => $order['user_id'],
                            'type'        => 'credit',
                            'amount'      => (int)round($netAdjustment),
                            'description' => "Subsidi Promo Food #{$orderId} (Subsidi Rp " . number_format($discountSubsidi, 0, ',', '.') . " - Fee Rp " . number_format($komisi, 0, ',', '.') . ")",
                            'created_at'  => date('Y-m-d H:i:s'),
                            'updated_at'  => date('Y-m-d H:i:s'),
                        ]);
                    }
                } else {
                    $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance - ' . abs($netAdjustment), false)->update();
                    if ($db->tableExists('transactions')) {
                        $db->table('transactions')->insert([
                            'driver_id'   => $payload['uid'],
                            'user_id'     => $order['user_id'],
                            'type'        => 'debit',
                            'amount'      => (int)round(abs($netAdjustment)),
                            'description' => "Potongan Fee Food #{$orderId} (Fee Rp " . number_format($komisi, 0, ',', '.') . ($discountSubsidi > 0 ? " - Subsidi Rp " . number_format($discountSubsidi, 0, ',', '.') : "") . ")",
                            'created_at'  => date('Y-m-d H:i:s'),
                            'updated_at'  => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->respond(['status' => 500, 'message' => 'Terjadi kesalahan sistem saat memproses pembayaran'], 500);
            }

            try {
                $this->checkDailyReward($payload['uid']);
            } catch (\Throwable $th) {
                log_message('error', 'checkDailyReward error: ' . $th->getMessage());
            }

            try {
                $this->checkCoinCashback($order['user_id'], $payload['uid']);
            } catch (\Throwable $th) {
                log_message('error', 'checkCoinCashback error: ' . $th->getMessage());
            }

            // Kirim Notifikasi Pesanan Selesai ke Penumpang (Push + Kotak Masuk)
            $fcmService = new FcmService();
            $doneTitle = $isMart ? "Belanjaan Sudah Sampai! 🛍️" : "Makanan Sudah Sampai! 🍽️";
            $doneBody = $isMart
                ? "Pesanan MaiMart Anda telah selesai diantarkan oleh driver. Terima kasih telah berbelanja!"
                : "Pesanan MaiFood Anda telah selesai diantarkan oleh driver. Selamat menikmati hidangan Anda!";

            $fcmService->sendToUserWithInbox(
                $order['user_id'],
                $doneTitle,
                $doneBody,
                ['type' => $isMart ? 'mart_status_update' : 'food_status_update', 'order_id' => $orderId, 'status' => 'completed']
            );

            // Beritahu Merchant bahwa pesanan selesai diantar
            $merchant = $db->table('merchants')->where('id', $order['merchant_id'])->get()->getRow();
            if ($merchant && !empty($merchant->fcm_token)) {
                $fcmService->sendPushNotification(
                    $merchant->fcm_token,
                    $isMart ? "Pesanan Belanja Selesai! ✅" : "Pesanan Makanan Selesai! ✅",
                    "Pesanan #$orderId telah berhasil diantarkan oleh driver ke pembeli.",
                    ['type' => $isMart ? 'mart_status_update' : 'food_status_update', 'order_id' => (string)$orderId, 'status' => 'completed']
                );
            }

            return $this->respond([
                'status' => 200, 
                'message' => 'Status pesanan berhasil diubah dan pembayaran diselesaikan',
                'data' => ['id' => (int)$orderId, 'status' => 'completed']
            ]);
        } else if ($newStatus == 'cancelled') {
            $reason = $this->request->getVar('reason') ?? 'Dibatalkan oleh kurir';

            $db->transStart();

            // Batalkan pesanan makanan sepenuhnya di sistem
            $orderModel->update($orderId, [
                'status' => 'cancelled',
                'cancel_reason' => $reason
            ]);

            // REFUND SALDO MAI-PAY 100% SECARA ATOMIK JIKA PEMBAYARAN DIGITAL
            if ($order['payment_method'] === 'maipay' && (float)$order['total_price'] > 0) {
                $db->table('users')
                    ->where('id', $order['user_id'])
                    ->set('balance', 'balance + ' . (float)$order['total_price'], false)
                    ->update();

                if ($db->tableExists('transactions')) {
                    $db->table('transactions')->insert([
                        'user_id'     => $order['user_id'],
                        'type'        => 'credit',
                        'amount'      => (int)round($order['total_price']),
                        'description' => "Refund Pembatalan Pesanan Food #{$orderId} oleh Kurir",
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            } else if ($order['payment_method'] === 'split_delivery_maipay' && (float)$order['delivery_fee'] > 0) {
                $db->table('users')
                    ->where('id', $order['user_id'])
                    ->set('balance', 'balance + ' . (float)$order['delivery_fee'], false)
                    ->update();

                if ($db->tableExists('transactions')) {
                    $db->table('transactions')->insert([
                        'user_id'     => $order['user_id'],
                        'type'        => 'credit',
                        'amount'      => (int)round($order['delivery_fee']),
                        'description' => "Refund Ongkir Split Food #{$orderId} oleh Kurir",
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->respond(['status' => 500, 'message' => 'Gagal membatalkan pesanan sistem'], 500);
            }

            $fcmService = new FcmService();

            // Beritahu merchant bahwa pesanan dibatalkan
            $merchant = $db->table('merchants')->where('id', $order['merchant_id'])->get()->getRow();
            if ($merchant && !empty($merchant->fcm_token)) {
                $cancelTitle = $isMart ? "Pesanan Belanja Dibatalkan" : "Pesanan Makanan Dibatalkan";
                $fcmService->sendPushNotification(
                    $merchant->fcm_token,
                    $cancelTitle,
                    "Pesanan #$orderId telah dibatalkan oleh kurir. Alasan: $reason",
                    ['type' => $isMart ? 'mart_order_cancelled' : 'food_order_cancelled', 'order_id' => (string)$orderId]
                );
            }

            // Beritahu User bahwa pesanan dibatalkan (Push + Kotak Masuk)
            $refundMsg = ($order['payment_method'] === 'maipay') ? ' Saldo Mai-Pay Anda telah dikembalikan 100%.' : '';
            $userCancelTitle = $isMart ? "Pesanan MaiMart Dibatalkan ❌" : "Pesanan MaiFood Dibatalkan ❌";
            $fcmService->sendToUserWithInbox(
                $order['user_id'],
                $userCancelTitle,
                "Pesanan #$orderId dibatalkan oleh kurir. Alasan: $reason.$refundMsg",
                ['type' => $isMart ? 'mart_order_cancelled' : 'food_order_cancelled', 'order_id' => (string)$orderId, 'status' => 'cancelled']
            );
            return $this->respond([
                'status' => 200, 
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => ['id' => (int)$orderId, 'status' => 'cancelled']
            ]);
        }

        // Pastikan ENUM status di DB mendukung nilai ini secara aman
        try {
            $db->query("ALTER TABLE food_orders MODIFY COLUMN status ENUM('pending', 'accepted_by_merchant', 'processing', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress', 'delivered', 'completed', 'cancelled') DEFAULT 'pending'");
        } catch (\Throwable $e) {}

        $orderModel->update($orderId, ['status' => $newStatus]);
        $driverName = $payload['name'] ?? 'Driver';

        // Kirim Notifikasi FCM ke User (Push + Kotak Masuk)
        $fcmService = new FcmService();
        $title = $isMart ? "Status Pesanan MaiMart" : "Status Pesanan MaiFood";
        $body = "Pesanan Anda diperbarui.";
        if ($newStatus == 'driver_at_merchant') {
            $title = $isMart ? "Driver Tiba di Toko 📍" : "Driver Tiba di Restoran 📍";
            $body = $isMart
                ? "Driver $driverName telah sampai di toko dan sedang menunggu belanjaan Anda diserahkan."
                : "Driver $driverName telah sampai di restoran dan sedang menunggu pesanan Anda disiapkan.";
        } else if ($newStatus == 'delivering') {
            $title = $isMart ? "Belanjaan Sedang Diantar! 🚀" : "Makanan Sedang Diantar! 🚀";
            $body = $isMart
                ? "Driver $driverName telah mengambil barang belanjaan Anda dan sedang dalam perjalanan mengantarkannya ke lokasi."
                : "Driver $driverName telah mengambil makanan Anda dan sedang dalam perjalanan mengantarkannya ke lokasi.";
        }

        $fcmService->sendToUserWithInbox(
            $order['user_id'],
            $title,
            $body,
            ['type' => $isMart ? 'mart_status_update' : 'food_status_update', 'order_id' => $orderId, 'status' => $newStatus]
        );

        // Kirim Notifikasi FCM ke Merchant agar kasir/resto langsung tahu kedatangan/keberangkatan driver!
        $merchant = $db->table('merchants')->where('id', $order['merchant_id'])->get()->getRow();
        if ($merchant && !empty($merchant->fcm_token)) {
            $mTitle = ($newStatus == 'driver_at_merchant')
                ? ($isMart ? "Kurir Tiba di Toko 📍" : "Kurir Tiba di Restoran 📍")
                : ($isMart ? "Belanjaan Sedang Diantar 🚀" : "Makanan Sedang Diantar 🚀");
            $mBody = ($newStatus == 'driver_at_merchant')
                ? "Kurir $driverName sudah sampai di lokasi Anda untuk mengambil pesanan #$orderId."
                : "Pesanan #$orderId telah diambil oleh kurir $driverName dan sedang diantar ke pembeli.";

            $fcmService->sendPushNotification($merchant->fcm_token, $mTitle, $mBody, [
                'type' => $isMart ? 'mart_status_update' : 'food_status_update',
                'order_id' => (string)$orderId,
                'status' => $newStatus
            ]);
        }

        return $this->respond([
            'status' => 200, 
            'message' => 'Status pesanan berhasil diubah',
            'data' => ['id' => (int)$orderId, 'status' => $newStatus]
        ]);
    }

    private function checkDailyReward($driverId)
    {
        $db = \Config\Database::connect();
        $settingModel = new \App\Models\SettingModel();

        $thresholdSetting = $settingModel->where('setting_key', 'driver_reward_threshold')->first();
        $amountSetting = $settingModel->where('setting_key', 'driver_reward_amount')->first();

        $threshold = $thresholdSetting ? (int)$thresholdSetting['setting_value'] : 10;
        $amount = $amountSetting ? (int)$amountSetting['setting_value'] : 10000;

        if ($threshold > 0 && $amount > 0) {
            $todayStr = date('Y-m-d');

            // Hitung total order hari ini (Orders + FoodOrders)
            $countRide = $db->table('orders')->where('driver_id', $driverId)->where('status', 'completed')->where('DATE(created_at)', $todayStr)->countAllResults();
            $countFood = $db->table('food_orders')->where('driver_id', $driverId)->where('status', 'completed')->where('DATE(created_at)', $todayStr)->countAllResults();
            $totalToday = $countRide + $countFood;

            // Jika tepat mencapai threshold, berikan reward
            if ($totalToday == $threshold) {
                // Tambah saldo pengemudi
                $db->table('drivers')->where('id', $driverId)->set('balance', 'balance + ' . $amount, false)->update();

                // Catat transaksi wallet
                $db->table('wallet_transactions')->insert([
                    'user_id' => $driverId,
                    'type' => 'topup',
                    'amount' => $amount,
                    'description' => 'Bonus Target Harian',
                    'status' => 'completed',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Kirim Notifikasi
                $driver = $db->table('drivers')->where('id', $driverId)->get()->getRow();
                if ($driver && !empty($driver->fcm_token)) {
                    $fcmService = new \App\Libraries\FcmService();
                    $fcmService->sendPushNotification(
                        $driver->fcm_token,
                        "🎉 Target Harian Tercapai!",
                        "Selamat! Anda mendapatkan bonus Rp " . number_format($amount, 0, ',', '.') . " yang telah masuk ke Mai-Pay Anda.",
                        ['type' => 'bonus_reward']
                    );
                }
            }
        }
    }

    private function checkCoinCashback($userId, $driverId)
    {
        try {
            $db = \Config\Database::connect();

            // Settings
            $settingModel = new \App\Models\SettingModel();
            $uThRow = $settingModel->where('setting_key', 'user_coin_threshold')->first();
            $userThreshold = $uThRow ? (int)($uThRow['setting_value'] ?? 5) : 5;

            $uRwRow = $settingModel->where('setting_key', 'user_coin_reward')->first();
            $userReward = $uRwRow ? (int)($uRwRow['setting_value'] ?? 100) : 100;

            $dThRow = $settingModel->where('setting_key', 'driver_coin_threshold')->first();
            $driverThreshold = $dThRow ? (int)($dThRow['setting_value'] ?? 10) : 10;

            $dRwRow = $settingModel->where('setting_key', 'driver_coin_reward')->first();
            $driverReward = $dRwRow ? (int)($dRwRow['setting_value'] ?? 200) : 200;

            // -- User Cashback --
            if ($userThreshold > 0 && $userReward > 0 && $db->fieldExists('coins', 'users')) {
                $userRideCount = $db->table('orders')->where('user_id', $userId)->where('status', 'completed')->countAllResults();
                $userFoodCount = $db->table('food_orders')->where('user_id', $userId)->where('status', 'completed')->countAllResults();
                $totalUserOrders = $userRideCount + $userFoodCount;

                if ($totalUserOrders > 0 && $totalUserOrders % $userThreshold == 0) {
                    // Beri cashback koin ke user
                    $db->table('users')->where('id', $userId)->set('coins', 'coins + ' . $userReward, false)->update();
                }
            }

            // -- Driver Cashback --
            if ($driverThreshold > 0 && $driverReward > 0 && $db->fieldExists('coins', 'drivers')) {
                $driverRideCount = $db->table('orders')->where('driver_id', $driverId)->where('status', 'completed')->countAllResults();
                $driverFoodCount = $db->table('food_orders')->where('driver_id', $driverId)->where('status', 'completed')->countAllResults();
                $totalDriverOrders = $driverRideCount + $driverFoodCount;

                if ($totalDriverOrders > 0 && $totalDriverOrders % $driverThreshold == 0) {
                    // Beri cashback koin ke driver
                    $db->table('drivers')->where('id', $driverId)->set('coins', 'coins + ' . $driverReward, false)->update();
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'checkCoinCashback exception: ' . $e->getMessage());
        }
    }

    /**
     * Memperkaya data order makanan dengan rincian finansial yang transparan
     */
    private function enrichFoodFinancials(array &$order, float $platformFee, string $platformFeeType)
    {
        $deliveryFee = (float)($order['delivery_fee'] ?? 0);
        $discountSubsidi = (float)($order['discount_amount'] ?? 0);
        $foodTotal = (float)($order['food_total'] ?? 0);
        $totalPrice = (float)($order['total_price'] ?? ($foodTotal + $deliveryFee - $discountSubsidi));

        $komisi = 0;
        if ($platformFeeType === 'percent') {
            $komisi = round($deliveryFee * ($platformFee / 100));
        } else {
            $komisi = round($platformFee);
        }

        $driverNet = max(0, $deliveryFee - $komisi);
        $netAdjustment = $discountSubsidi - $komisi;

        $order['original_delivery_fee'] = $deliveryFee;
        $order['platform_fee_percent'] = $platformFee;
        $order['platform_fee_amount'] = $komisi;
        $order['driver_net_earnings'] = $driverNet;

        if ($order['payment_method'] === 'split_delivery_maipay') {
            $cashFood = max(0, $foodTotal - $discountSubsidi);
            $order['is_split_payment'] = true;
            $order['split_payment_type'] = 'ongkir_maipay';
            $order['payment_label'] = 'SPLIT (ONGKIR MAIPAY)';
            $order['cash_to_collect'] = $cashFood;
            $order['merchant_bailout'] = $foodTotal;
            $order['wallet_adjustment'] = $driverNet + $discountSubsidi;
            $order['split_instruction'] = 'Hanya tagih uang makanan Rp ' . number_format($cashFood, 0, ',', '.') . ' ke pelanggan. Ongkir Rp ' . number_format($deliveryFee, 0, ',', '.') . ' sudah dibayar digital dan otomatis masuk saldo Anda.';
        } else {
            $order['is_split_payment'] = false;
            $order['payment_label'] = ($order['payment_method'] === 'maipay') ? 'MAIPAY' : 'TUNAI';
            $order['split_instruction'] = '';
            $order['cash_to_collect'] = ($order['payment_method'] === 'cash') ? $totalPrice : 0;
            $order['merchant_bailout'] = ($order['payment_method'] === 'cash') ? $foodTotal : 0;
            $order['wallet_adjustment'] = $netAdjustment;
        }
    }
}
