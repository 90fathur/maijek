<?php

namespace App\Controllers\Api\Driver;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrderModel;
use App\Models\DriverModel;

class Orders extends ResourceController
{
    protected $format = 'json';

    public function available()
    {
        $payload = $_SERVER['jwt_payload'];
        $driverModel = new DriverModel();
        $driver = $driverModel->find($payload['uid']);

        $lat = $driver['latitude'] ?? 0;
        $lng = $driver['longitude'] ?? 0;

        if ($lat == 0 || $lng == 0) {
            // Jika driver belum punya kordinat GPS, kembalikan array kosong
            return $this->respond(['status' => 200, 'message' => 'Pesanan tersedia', 'data' => []]);
        }

        // --- Auto-Migration & SISTEM HEARTBEAT ---
        // Karena fungsi ini di-ping setiap 5 detik oleh HP Driver, kita update detak jantungnya
        $db = \Config\Database::connect();
        try {
            if ($db->fieldExists('last_heartbeat_at', 'drivers')) {
                $db->table('drivers')->where('id', $payload['uid'])->update([
                    'last_heartbeat_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Throwable $e) {
        }
        // ------------------------

        $builder = $db->table('orders');

        // Rumus Haversine untuk mencari jarak (dalam KM) antara Driver dan Lokasi Jemput Penumpang
        $builder->select('orders.*, users.name as user_name, users.phone as user_phone, users.photo as user_photo, (
            6371 * acos(
                LEAST(1.0, cos(radians(' . $db->escape($lat) . ')) * 
                cos(radians(pickup_lat)) * 
                cos(radians(pickup_lng) - radians(' . $db->escape($lng) . ')) + 
                sin(radians(' . $db->escape($lat) . ')) * 
                sin(radians(pickup_lat)))
            )
        ) AS distance_to_pickup');

        $builder->join('users', 'users.id = orders.user_id');
        $builder->where('orders.status', 'pending');

        // Filter berdasarkan jenis kendaraan supir (Motor vs Mobil)
        $driverVehicleType = $driver['vehicle_type'] ?? 'motor';
        if (strtolower($driverVehicleType) == 'motor') {
            $builder->whereIn('orders.service_type', ['motor', 'mai_send_motor', 'mai_send', 'send', 'mai_titip', 'mai_titip_motor', 'titip']);
        } else {
            // Jika mobil, bisa terima mobil, car, car_premium, mai_send_mobil, atau mai_titip_mobil
            $builder->whereIn('orders.service_type', ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        }

        // Urutkan dari yang terdekat
        $builder->orderBy('distance_to_pickup', 'ASC');
        $builder->limit(10); // Ambil 10 terdekat

        $query = $builder->get();
        $orders = $query->getResultArray();

        // Saring hanya pesanan yang jarak jemputnya kurang dari 5 KM
        $filteredOrders = array_filter($orders, function ($o) {
            return $o['distance_to_pickup'] <= 5;
        });

        // Re-index array setelah filter
        $filteredOrders = array_values($filteredOrders);

        $settingModel = new \App\Models\SettingModel();
        $feeSetting = $settingModel->where('setting_key', 'platform_fee')->first();
        $feeTypeSetting = $settingModel->where('setting_key', 'platform_fee_type')->first();
        $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
        $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

        foreach ($filteredOrders as &$o) {
            $this->enrichOrderFinancials($o, $platformFee, $platformFeeType);

            if (!empty($o['user_photo'])) {
                $o['user_photo'] = (strpos($o['user_photo'], 'http') === 0)
                    ? $o['user_photo']
                    : base_url('uploads/profiles/' . $o['user_photo']);
            } else {
                $o['user_photo'] = '';
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Pesanan tersedia', 'data' => $filteredOrders]);
    }

    public function active()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new OrderModel();

        $activeOrders = $orderModel->select('orders.*, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('users', 'users.id = orders.user_id')
            ->where('driver_id', $payload['uid'])
            ->whereIn('orders.status', ['accepted', 'in_progress'])
            ->findAll();

        if (empty($activeOrders)) {
            return $this->respond(['status' => 404, 'message' => 'Tidak ada pesanan aktif', 'data' => []], 404);
        }

        $db = \Config\Database::connect();

        $settingModel = new \App\Models\SettingModel();
        $feeSetting = $settingModel->where('setting_key', 'platform_fee')->first();
        $feeTypeSetting = $settingModel->where('setting_key', 'platform_fee_type')->first();
        $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
        $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

        // Cek unread message untuk setiap order
        foreach ($activeOrders as &$order) {
            $this->enrichOrderFinancials($order, $platformFee, $platformFeeType);

            $unreadCount = 0;
            try {
                if ($db->tableExists('order_chats')) {
                    $unreadCount = $db->table('order_chats')
                        ->where('order_id', $order['id'])
                        ->where('sender_type', 'user')
                        ->where('is_read', 0)
                        ->countAllResults();
                }
            } catch (\Throwable $e) {
            }

            $order['unread_count'] = $unreadCount;

            if (!empty($order['user_photo'])) {
                $order['user_photo'] = (strpos($order['user_photo'], 'http') === 0)
                    ? $order['user_photo']
                    : base_url('uploads/profiles/' . $order['user_photo']);
            } else {
                $order['user_photo'] = '';
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Pesanan aktif ditemukan', 'data' => $activeOrders]);
    }

    public function detail($id = null)
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new OrderModel();

        $order = $orderModel->select('orders.*, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('users', 'users.id = orders.user_id')
            ->where('orders.id', $id)
            ->where('orders.driver_id', $payload['uid'])
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $settingModel = new \App\Models\SettingModel();
        $feeSetting = $settingModel->where('setting_key', 'platform_fee')->first();
        $feeTypeSetting = $settingModel->where('setting_key', 'platform_fee_type')->first();
        $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
        $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

        $this->enrichOrderFinancials($order, $platformFee, $platformFeeType);

        if (!empty($order['user_photo'])) {
            $order['user_photo'] = (strpos($order['user_photo'], 'http') === 0)
                ? $order['user_photo']
                : base_url('uploads/profiles/' . $order['user_photo']);
        }

        return $this->respond(['status' => 200, 'message' => 'Detail pesanan', 'data' => $order]);
    }

    public function accept()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getVar('order_id');

        $orderModel = new OrderModel();
        $driverModel = new DriverModel();

        $driver = $driverModel->find($payload['uid']);
        if ($driver['is_online'] != 1) {
            return $this->respond(['status' => 400, 'message' => 'Anda harus berstatus online untuk menerima pesanan'], 400);
        }

        $order = $orderModel->find($orderId);
        if (!$order || $order['status'] != 'pending') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan sudah diambil atau tidak valid'], 400);
        }

        // Cek Komisi & Saldo untuk metode Tunai
        if (isset($order['payment_method']) && $order['payment_method'] == 'cash') {
            $settingModel = new \App\Models\SettingModel();
            $feeSetting = $settingModel->where('setting_key', 'platform_fee')->first();
            $feeTypeSetting = $settingModel->where('setting_key', 'platform_fee_type')->first();

            $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
            $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

            $discountSubsidi = (float)($order['discount_amount'] ?? 0);
            $originalFare = (float)$order['price'] + $discountSubsidi;

            $komisi = 0;
            if ($platformFeeType == 'percent') {
                $komisi = round($originalFare * ($platformFee / 100));
            } else {
                $komisi = round($platformFee);
            }

            // Beban potong saldo komisi dikurangi subsidi promo
            // Jika Subsidi Promo >= Komisi, driver akan menerima tambahan uang/cashback ke saldo (netDeduction <= 0)
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

        // Fitur Sekali Jalan: Mengizinkan multi-order
        // $activeOrder = $orderModel->where('driver_id', $payload['uid'])
        //     ->whereIn('status', ['accepted', 'in_progress'])
        //     ->first();
        // if ($activeOrder) {
        //     return $this->respond(['status' => 400, 'message' => 'Selesaikan pesanan Anda saat ini terlebih dahulu'], 400);
        // }

        $db = \Config\Database::connect();
        $db->transStart();

        // Anti-Race Condition: Kunci pesanan hanya jika statusnya benar-benar masih 'pending' dan belum memiliki supir di Database detik ini
        $db->table('orders')
            ->where('id', $orderId)
            ->where('status', 'pending')
            ->groupStart()
            ->where('driver_id', null)
            ->orWhere('driver_id', 0)
            ->orWhere('driver_id', '')
            ->groupEnd()
            ->update([
                'driver_id' => $payload['uid'],
                'status' => 'accepted',
                'accepted_at' => date('Y-m-d H:i:s')
            ]);

        // Cek apakah update berhasil (artinya pesanan belum diambil orang lain)
        if ($db->affectedRows() == 0) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Maaf, pesanan ini baru saja diambil oleh supir lain'], 400);
        }

        $db->transComplete();

        // Kirim Notifikasi ke Penumpang secara Cerdas sesuai Layanan
        $fcmService = new \App\Libraries\FcmService();
        $serviceType = strtolower($order['service_type'] ?? 'motor');
        $driverName = $driver['name'] ?? 'Driver';
        $plate = !empty($driver['vehicle_plate']) ? " ({$driver['vehicle_plate']})" : "";

        if (in_array($serviceType, ['mai_send_motor', 'mai_send_mobil', 'mai_send', 'send'])) {
            $notifTitle = "Kurir Ditemukan! 📦";
            $notifBody = "Kurir $driverName$plate sedang menuju lokasi penjemputan paket Anda.";
        } else if (in_array($serviceType, ['mai_titip_motor', 'mai_titip_mobil', 'mai_titip', 'titip'])) {
            $notifTitle = "Driver Titip Ditemukan! 🛍️";
            $notifBody = "Driver $driverName$plate siap membelikan pesanan titipan Anda.";
        } else if (in_array($serviceType, ['mobil', 'car', 'car_premium'])) {
            $notifTitle = "Driver Mobil Ditemukan! 🚗";
            $notifBody = "Driver $driverName$plate sedang menuju lokasi penjemputan Anda.";
        } else {
            $notifTitle = "Driver Motor Ditemukan! 🛵";
            $notifBody = "Driver $driverName$plate sedang menuju lokasi penjemputan Anda.";
        }

        $fcmService->sendToUserWithInbox(
            $order['user_id'],
            $notifTitle,
            $notifBody,
            ['type' => 'order_status', 'order_id' => $orderId, 'status' => 'accepted']
        );

        // Ambil data pesanan terbaru secara lengkap untuk langsung dipakai frontend Driver
        $updatedOrder = $orderModel->select('orders.*, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('users', 'users.id = orders.user_id')
            ->where('orders.id', $orderId)
            ->first();

        if ($updatedOrder && !empty($updatedOrder['user_photo'])) {
            $updatedOrder['user_photo'] = (strpos($updatedOrder['user_photo'], 'http') === 0)
                ? $updatedOrder['user_photo']
                : base_url('uploads/profiles/' . $updatedOrder['user_photo']);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengambil pesanan',
            'data' => $updatedOrder ?: ['id' => $orderId, 'status' => 'accepted']
        ]);
    }

    public function skip()
    {
        $payload = $_SERVER['jwt_payload'];
        $db = \Config\Database::connect();

        $driver = $db->table('drivers')->where('id', $payload['uid'])->get()->getRow();
        if (!$driver) return $this->respond(['status' => 404, 'message' => 'Driver not found'], 404);

        $todayStr = date('Y-m-d');
        if ($driver->last_skip_date != $todayStr) {
            $newCount = 1;
        } else {
            $newCount = $driver->daily_skip_count + 1;
        }

        $db->table('drivers')->where('id', $payload['uid'])->update([
            'daily_skip_count' => $newCount,
            'last_skip_date' => $todayStr
        ]);

        return $this->respond(['status' => 200, 'message' => 'Pesanan dilewati, penalti dicatat']);
    }

    public function update($id = null)
    {
        return $this->updateStatus();
    }

    public function status($id = null)
    {
        return $this->updateStatus();
    }

    public function updateStatus()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getVar('order_id');
        $newStatus = $this->request->getVar('status'); // 'arrived', 'in_progress', 'completed', 'cancelled'

        $orderModel = new OrderModel();
        $order = $orderModel->find($orderId);

        if (!$order || $order['driver_id'] != $payload['uid']) {
            return $this->respond(['status' => 403, 'message' => 'Tidak punya akses ke pesanan ini'], 403);
        }

        // Anti-Duplicate & Anti-Tampering Proteksi
        if ($order['status'] === 'completed') {
            return $this->respond(['status' => 200, 'message' => 'Pesanan ini sudah diselesaikan sebelumnya.']);
        }
        if ($order['status'] === 'cancelled') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan ini sudah dibatalkan sebelumnya.'], 400);
        }

        // Validasi Alur Status: Selesai hanya boleh jika status saat ini aktif (arrived / in_progress)
        if ($newStatus == 'completed' && !in_array($order['status'], ['accepted', 'arrived', 'in_progress'])) {
            return $this->respond(['status' => 400, 'message' => 'Status pesanan tidak valid untuk diselesaikan.'], 400);
        }

        $updateData = ['status' => $newStatus];
        $reason = $this->request->getVar('reason');

        if ($newStatus == 'arrived') {
            $updateData['arrived_at'] = date('Y-m-d H:i:s');
        } elseif ($newStatus == 'in_progress') {
            $updateData['in_progress_at'] = date('Y-m-d H:i:s');
        } elseif ($newStatus == 'completed') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');

            // Khusus Mai-Titip, jika driver memasukkan total struk belanja
            $titipCost = $this->request->getVar('titip_total_cost');
            if ($titipCost) {
                $updateData['titip_total_cost'] = $titipCost;
            }
        } elseif ($newStatus == 'cancelled' && $reason) {
            $updateData['cancel_reason'] = $reason;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Kunci baris status order secara atomik (Anti-Race Condition)
        $affected = $db->table('orders')
            ->where('id', $orderId)
            ->where('driver_id', $payload['uid'])
            ->where('status !=', 'completed')
            ->where('status !=', 'cancelled')
            ->update($updateData);

        if ($affected === 0) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Pesanan telah diproses oleh transaksi lain.'], 400);
        }

        // Jika selesai atau dibatalkan, update saldo secara atomik
        if ($newStatus == 'completed' || $newStatus == 'cancelled') {
            if (isset($order['payment_method'])) {
                if ($newStatus == 'completed') {
                    // Hitung Komisi & Hak Bersih Kurir
                    $settingModel = new \App\Models\SettingModel();
                    $feeSetting = $settingModel->where('setting_key', 'platform_fee')->first();
                    $feeTypeSetting = $settingModel->where('setting_key', 'platform_fee_type')->first();

                    $platformFee = $feeSetting ? (float)$feeSetting['setting_value'] : 8;
                    $platformFeeType = $feeTypeSetting ? $feeTypeSetting['setting_value'] : 'percent';

                    $discountSubsidi = (float)($order['discount_amount'] ?? 0);
                    $originalFare = (float)$order['price'] + $discountSubsidi;

                    $komisi = 0;
                    if ($platformFeeType == 'percent') {
                        $komisi = round($originalFare * ($platformFee / 100));
                    } else {
                        $komisi = round($platformFee);
                    }

                    if ($order['payment_method'] == 'maipay') {
                        // Penumpang bayar digital: supir berhak menerima tarif normal penuh dikurangi komisi secara atomik
                        $driverEarn = (float)($originalFare - $komisi);
                        $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance + ' . (float)$driverEarn, false)->update();

                        if ($db->tableExists('transactions')) {
                            $db->table('transactions')->insert([
                                'driver_id'   => $payload['uid'],
                                'user_id'     => $order['user_id'],
                                'type'        => 'credit',
                                'amount'      => (int)round($driverEarn),
                                'description' => "Pendapatan Order #{$orderId} (Tarif Rp " . number_format($originalFare, 0, ',', '.') . " - Fee Rp " . number_format($komisi, 0, ',', '.') . ")",
                                'created_at'  => date('Y-m-d H:i:s'),
                                'updated_at'  => date('Y-m-d H:i:s'),
                            ]);
                        }
                    } else if ($order['payment_method'] == 'cash') {
                        // Penumpang bayar tunai terdiskon (price). Supir seharusnya menerima (originalFare - komisi).
                        // Karena supir memegang uang fisik 'price', selisih subsidi (discountSubsidi - komisi) disesuaikan secara atomik
                        $netAdjustment = (float)($discountSubsidi - $komisi);
                        if ($netAdjustment >= 0) {
                            $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance + ' . (float)$netAdjustment, false)->update();
                            if ($netAdjustment > 0 && $db->tableExists('transactions')) {
                                $db->table('transactions')->insert([
                                    'driver_id'   => $payload['uid'],
                                    'user_id'     => $order['user_id'],
                                    'type'        => 'credit',
                                    'amount'      => (int)round($netAdjustment),
                                    'description' => "Subsidi Promo Order #{$orderId} (Subsidi Rp " . number_format($discountSubsidi, 0, ',', '.') . " - Fee Rp " . number_format($komisi, 0, ',', '.') . ")",
                                    'created_at'  => date('Y-m-d H:i:s'),
                                    'updated_at'  => date('Y-m-d H:i:s'),
                                ]);
                            }
                        } else {
                            $db->table('drivers')->where('id', $payload['uid'])->set('balance', 'balance - ' . abs((float)$netAdjustment), false)->update();
                            if ($db->tableExists('transactions')) {
                                $db->table('transactions')->insert([
                                    'driver_id'   => $payload['uid'],
                                    'user_id'     => $order['user_id'],
                                    'type'        => 'debit',
                                    'amount'      => (int)round(abs($netAdjustment)),
                                    'description' => "Potongan Fee Order #{$orderId} (Fee Rp " . number_format($komisi, 0, ',', '.') . ($discountSubsidi > 0 ? " - Subsidi Rp " . number_format($discountSubsidi, 0, ',', '.') : "") . ")",
                                    'created_at'  => date('Y-m-d H:i:s'),
                                    'updated_at'  => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                } else if ($newStatus == 'cancelled' && $order['payment_method'] == 'maipay') {
                    // Batal oleh driver dan pakai Mai-Pay, kembalikan uang penumpang secara atomik
                    $db->table('users')->where('id', $order['user_id'])->set('balance', 'balance + ' . (float)$order['price'], false)->update();
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(['status' => 500, 'message' => 'Gagal memproses pembaruan status pesanan.'], 500);
        }

        if ($newStatus == 'completed') {
            try {
                $this->checkDailyReward($payload['uid']);
            } catch (\Throwable $th) {
                log_message('error', 'Orders checkDailyReward error: ' . $th->getMessage());
            }

            try {
                $this->checkCoinCashback($order['user_id'], $payload['uid']);
            } catch (\Throwable $th) {
                log_message('error', 'Orders checkCoinCashback error: ' . $th->getMessage());
            }
        }

        // Kirim Notifikasi ke Penumpang secara Cerdas sesuai Layanan
        $fcmService = new \App\Libraries\FcmService();
        $serviceType = strtolower($order['service_type'] ?? 'motor');
        $driver = $db->table('drivers')->where('id', $payload['uid'])->get()->getRowArray();
        $driverName = $driver['name'] ?? 'Driver';
        $plate = !empty($driver['vehicle_plate']) ? " ({$driver['vehicle_plate']})" : "";

        $title = "Status Pesanan Berubah";
        $body = "Pesanan Anda kini: " . strtoupper($newStatus);

        if ($newStatus == 'arrived') {
            if (in_array($serviceType, ['mai_send_motor', 'mai_send_mobil', 'mai_send', 'send'])) {
                $title = "Kurir Sudah Sampai! 📍";
                $body = "Kurir $driverName telah tiba di lokasi penjemputan paket.";
            } else if (in_array($serviceType, ['mai_titip_motor', 'mai_titip_mobil', 'mai_titip', 'titip'])) {
                $title = "Driver Tiba di Toko! 🏪";
                $body = "Driver $driverName telah tiba di lokasi belanja titipan Anda.";
            } else {
                $title = "Driver Sudah Sampai! 📍";
                $body = "Driver $driverName$plate telah tiba di titik penjemputan. Silakan temui driver Anda.";
            }
        } elseif ($newStatus == 'in_progress') {
            if (in_array($serviceType, ['mai_send_motor', 'mai_send_mobil', 'mai_send', 'send'])) {
                $title = "Paket Sedang Diantar! 🚚";
                $body = "Kurir telah mengambil paket dan sedang dalam perjalanan menuju alamat penerima.";
            } else if (in_array($serviceType, ['mai_titip_motor', 'mai_titip_mobil', 'mai_titip', 'titip'])) {
                $title = "Barang Belanja Sedang Diantar! 🛵";
                $body = "Driver telah selesai berbelanja dan sedang mengantarkan pesanan ke lokasi Anda.";
            } else if (in_array($serviceType, ['mobil', 'car', 'car_premium'])) {
                $title = "Perjalanan Mai-Car Dimulai 🚀";
                $body = "Anda sedang dalam perjalanan aman dan nyaman bersama $driverName.";
            } else {
                $title = "Perjalanan Dimulai 🚀";
                $body = "Anda sedang dalam perjalanan menuju tujuan bersama $driverName.";
            }
        } elseif ($newStatus == 'completed') {
            if (in_array($serviceType, ['mai_send_motor', 'mai_send_mobil', 'mai_send', 'send'])) {
                $title = "Paket Berhasil Terkirim! ✅";
                $body = "Paket Anda telah berhasil diantarkan ke penerima. Terima kasih telah menggunakan Mai-Send!";
            } else if (in_array($serviceType, ['mai_titip_motor', 'mai_titip_mobil', 'mai_titip', 'titip'])) {
                $title = "Titipan Belanja Selesai! ✅";
                $body = "Barang belanjaan Anda telah sampai. Terima kasih telah menggunakan Mai-Titip!";
            } else {
                $title = "Perjalanan Selesai! 🎉";
                $body = "Anda telah tiba di tujuan. Terima kasih telah menggunakan layanan Maijek!";
            }
        } elseif ($newStatus == 'cancelled') {
            $title = "Pesanan Dibatalkan ❌";
            $body = "Mohon maaf, driver membatalkan pesanan Anda: " . ($reason ?: 'Kendala operasional.');
        }

        try {
            $fcmService->sendToUserWithInbox(
                $order['user_id'],
                $title,
                $body,
                ['type' => 'order_status', 'order_id' => $orderId, 'status' => $newStatus]
            );
        } catch (\Exception $e) {
            log_message('error', 'Gagal kirim notifikasi order_status ke penumpang: ' . $e->getMessage());
        }

        return $this->respond(['status' => 200, 'message' => 'Status berhasil diubah']);
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

    public function history()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new OrderModel();

        $dateFilter = $this->request->getVar('date'); // Format: YYYY-MM-DD

        $builder = $orderModel->select('orders.*, users.name as user_name, users.phone as user_phone, users.photo as user_photo')
            ->join('users', 'users.id = orders.user_id')
            ->where('driver_id', $payload['uid'])
            ->whereIn('orders.status', ['completed', 'cancelled']);

        if ($dateFilter) {
            $builder->where('DATE(orders.created_at)', $dateFilter);
        }

        $history = $builder->orderBy('orders.created_at', 'DESC')
            ->findAll();

        foreach ($history as &$h) {
            if (!empty($h['user_photo'])) {
                $h['user_photo'] = (strpos($h['user_photo'], 'http') === 0)
                    ? $h['user_photo']
                    : base_url('uploads/profiles/' . $h['user_photo']);
            } else {
                $h['user_photo'] = '';
            }
        }

        // Ambil data Food Orders
        $foodOrderModel = new \App\Models\FoodOrderModel();
        $foodBuilder = $foodOrderModel->select('food_orders.*, users.name as user_name, users.phone as user_phone, users.photo as user_photo, merchants.name as merchant_name')
            ->join('users', 'users.id = food_orders.user_id')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->where('food_orders.driver_id', $payload['uid'])
            ->whereIn('food_orders.status', ['completed', 'cancelled']);

        if ($dateFilter) {
            $foodBuilder->where('DATE(food_orders.created_at)', $dateFilter);
        }

        $foodOrdersRaw = $foodBuilder->orderBy('food_orders.created_at', 'DESC')->findAll();

        $foodOrders = [];
        $db = \Config\Database::connect();
        foreach ($foodOrdersRaw as $fo) {
            $items = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_id', $fo['id'])
                ->get()
                ->getResultArray();

            $foodOrders[] = [
                'id' => $fo['id'],
                'user_id' => $fo['user_id'],
                'driver_id' => $fo['driver_id'],
                'type' => 'food',
                'pickup_address' => $fo['merchant_name'],
                'dropoff_address' => $fo['delivery_address'],
                'distance' => $fo['distance'] ?? 0,
                'price' => $fo['delivery_fee'],
                'delivery_fee' => $fo['delivery_fee'],
                'food_total' => $fo['food_total'] ?? 0,
                'total_price' => $fo['total_price'] ?? 0,
                'payment_method' => $fo['payment_method'] ?? 'cash',
                'status' => $fo['status'],
                'created_at' => $fo['created_at'],
                'user_name' => $fo['user_name'],
                'user_phone' => $fo['user_phone'],
                'user_photo' => !empty($fo['user_photo']) ? ((strpos($fo['user_photo'], 'http') === 0) ? $fo['user_photo'] : base_url('uploads/profiles/' . $fo['user_photo'])) : '',
                'rating' => isset($fo['driver_rating']) ? (int)$fo['driver_rating'] : 0,
                'review_comment' => $fo['review_driver'] ?? null,
                'items' => $items,
            ];
        }

        // Gabungkan dan urutkan
        $allHistory = array_merge($history, $foodOrders);
        usort($allHistory, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Hitung Summary (Pendapatan & Order) untuk filter ini
        $totalEarnings = 0;
        $totalOrders = 0;
        foreach ($allHistory as $order) {
            if ($order['status'] == 'completed') {
                $totalEarnings += (int)$order['price'];
                $totalOrders++;
            }
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Riwayat pesanan ditemukan',
            'data' => [
                'history' => $allHistory,
                'summary' => [
                    'total_earnings' => $totalEarnings,
                    'total_orders' => $totalOrders
                ]
            ]
        ]);
    }

    public function uploadProof()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getPost('order_id');
        $type = $this->request->getPost('type'); // pickup, delivery, receipt

        $orderModel = new OrderModel();
        $order = $orderModel->find($orderId);

        if (!$order || $order['driver_id'] != $payload['uid']) {
            return $this->respond(['status' => 403, 'message' => 'Pesanan tidak valid atau bukan milik Anda'], 403);
        }

        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid()) {
            return $this->respond(['status' => 400, 'message' => 'File gambar tidak valid'], 400);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->respond(['status' => 400, 'message' => 'Ukuran file bukti melebihi 10MB.'], 400);
        }

        $safeExt = function_exists('validate_uploaded_file_ext')
            ? validate_uploaded_file_ext($file, ['jpg', 'jpeg', 'png', 'webp'])
            : null;

        if (!$safeExt) {
            return $this->respond(['status' => 400, 'message' => 'Format file bukti tidak valid. Hanya JPG, PNG, atau WEBP.'], 400);
        }

        if (!is_dir(FCPATH . 'uploads/orders')) {
            mkdir(FCPATH . 'uploads/orders', 0755, true);
        }

        $newName = uniqid('order_proof_') . '.' . $safeExt;
        $file->move(FCPATH . 'uploads/orders', $newName);
        $imageUrl = 'uploads/orders/' . $newName;

        $updateData = [];
        if ($type == 'pickup') {
            $updateData['proof_of_pickup'] = $imageUrl;
        } elseif ($type == 'delivery') {
            $updateData['proof_of_delivery'] = $imageUrl;
        } elseif ($type == 'receipt') {
            $updateData['titip_receipt_img'] = $imageUrl;
        } else {
            return $this->respond(['status' => 400, 'message' => 'Tipe upload tidak dikenali'], 400);
        }

        $titipCost = $this->request->getPost('titip_total_cost');
        if ($titipCost) {
            $updateData['titip_total_cost'] = $titipCost;
        }

        $orderModel->update($orderId, $updateData);

        return $this->respond([
            'status' => 200,
            'message' => 'Berhasil mengunggah bukti',
            'data' => ['url' => base_url($imageUrl)]
        ]);
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
            log_message('error', 'Orders checkCoinCashback exception: ' . $e->getMessage());
        }
    }

    /**
     * Memperkaya data order dengan rincian finansial yang transparan
     */
    private function enrichOrderFinancials(array &$order, float $platformFee, string $platformFeeType)
    {
        $discountSubsidi = (float)($order['discount_amount'] ?? 0);
        $originalFare = (float)($order['price'] ?? 0) + $discountSubsidi;

        $komisi = 0;
        if ($platformFeeType === 'percent') {
            $komisi = round($originalFare * ($platformFee / 100));
        } else {
            $komisi = round($platformFee);
        }

        $driverNet = max(0, $originalFare - $komisi);
        $netAdjustment = $discountSubsidi - $komisi;

        $order['original_fare'] = $originalFare;
        $order['platform_fee_percent'] = $platformFee;
        $order['platform_fee_amount'] = $komisi;
        $order['driver_net_earnings'] = $driverNet;
        $order['wallet_adjustment'] = $netAdjustment;
        $order['cash_to_collect'] = ($order['payment_method'] === 'cash') ? (float)$order['price'] : 0;
    }
}
