<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrderModel;

class Orders extends ResourceController
{
    protected $format = 'json';

    public function create()
    {
        $payload = $_SERVER['jwt_payload'];
        $rules = [
            'pickup_lat'   => 'required',
            'pickup_lng'   => 'required',
            'dropoff_lat'  => 'required',
            'dropoff_lng'  => 'required',
            'pickup_address'  => 'required',
            'dropoff_address' => 'required',
            'distance'     => 'required|numeric',
            'price'        => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal', 'data' => $this->validator->getErrors()], 400);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
        if ($user && isset($user->status) && $user->status === 'suspended') {
            return $this->respond(['status' => 403, 'message' => 'Akun Anda sedang disuspend oleh Admin karena pelanggaran kebijakan.', 'data' => null], 403);
        }

        $orderModel = new OrderModel();

        // Validasi Geofence (Zona Operasional)
        $zoneModel = new \App\Models\ZoneModel();
        $activeZones = $zoneModel->where('status', 'active')->findAll();

        if (!empty($activeZones)) {
            $pickupLat = (float) $this->request->getVar('pickup_lat');
            $pickupLng = (float) $this->request->getVar('pickup_lng');
            $isInsideZone = false;

            foreach ($activeZones as $zone) {
                $polygon = json_decode($zone['coordinates'], true);
                if (is_array($polygon) && count($polygon) >= 3) {
                    if ($this->isPointInPolygon($pickupLat, $pickupLng, $polygon)) {
                        $isInsideZone = true;
                        break;
                    }
                }
            }

            if (!$isInsideZone) {
                return $this->respond(['status' => 400, 'message' => 'Layanan Maijek belum tersedia di area penjemputan Anda.', 'data' => null], 400);
            }
        }

        // Cek jika user masih punya pesanan aktif (pending/accepted/in_progress)
        $activeOrder = $orderModel->where('user_id', $payload['uid'])
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->first();
        if ($activeOrder) {
            return $this->respond(['status' => 400, 'message' => 'Anda masih memiliki pesanan aktif', 'data' => null], 400);
        }

        $paymentMethod = $this->request->getVar('payment_method') ?? 'cash';
        $price = (float)$this->request->getVar('price');
        $promoCode = $this->request->getVar('promo_code');
        $serviceType = $this->request->getVar('service_type') ?? 'motor';
        if ($serviceType === 'car' || $serviceType === 'car_premium') {
            $serviceType = 'mobil';
        }

        // Validasi Keamanan: Tarif tidak boleh nol, negatif, atau di bawah batas minimum
        if ($price < 3000) {
            return $this->respond(['status' => 400, 'message' => 'Tarif pesanan tidak valid (minimal Rp 3.000).', 'data' => null], 400);
        }

        $db = \Config\Database::connect();

        // Pastikan kolom discount_amount, promo_code & estimated_time ada secara otomatis di database orders
        try {
            if (!$db->fieldExists('discount_amount', 'orders')) {
                $db->query("ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER price");
            }
            if (!$db->fieldExists('promo_code', 'orders')) {
                $db->query("ALTER TABLE orders ADD COLUMN promo_code VARCHAR(50) NULL AFTER discount_amount");
            }
            if (!$db->fieldExists('estimated_time', 'orders')) {
                $db->query("ALTER TABLE orders ADD COLUMN estimated_time VARCHAR(100) NULL AFTER distance");
            }
            if (!$db->fieldExists('vehicle_brand', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_brand VARCHAR(100) NULL AFTER vehicle_plate");
            }
            if (!$db->fieldExists('vehicle_capacity', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_capacity INT DEFAULT 4 AFTER vehicle_brand");
            }
        } catch (\Throwable $e) {
        }

        // Kalkulasi Waktu Estimasi Cerdas
        $dist = (float)$this->request->getVar('distance');
        $isCar = in_array(strtolower($serviceType), ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        $isTitip = strpos(strtolower($serviceType), 'titip') !== false;
        $estMins = $isCar ? round($dist * 3.0 + 5) : round($dist * 2.4 + 3);
        if ($isTitip) $estMins += 12;
        if ($estMins < 3) $estMins = 3;
        $clockArr = date('H:i', strtotime("+$estMins minutes"));
        $estimatedTime = $this->request->getVar('estimated_time') ?? "$estMins mnt (Tiba ~$clockArr)";

        // Validasi & Hitung Nilai Diskon Promo
        $discountAmount = 0;
        if (!empty($promoCode)) {
            if ($promoCode === 'Promo Otomatis') {
                $settingModel = new \App\Models\SettingModel();
                $status = $settingModel->where('setting_key', 'promo_dynamic_status')->first();
                $day = $settingModel->where('setting_key', 'promo_dynamic_day')->first();
                $type = $settingModel->where('setting_key', 'promo_dynamic_type')->first();
                $amount = $settingModel->where('setting_key', 'promo_dynamic_amount')->first();

                if (($status['setting_value'] ?? '') === 'active' && strtolower($day['setting_value'] ?? '') === strtolower(date('l'))) {
                    $dynType = $type['setting_value'] ?? 'fixed';
                    $dynAmount = (float)($amount['setting_value'] ?? 0);
                    if ($dynType === 'percent' && $dynAmount > 0 && $dynAmount < 100) {
                        $discountAmount = round(($price / (1 - ($dynAmount / 100))) * ($dynAmount / 100));
                    } else {
                        $discountAmount = $dynAmount;
                    }
                }
            } else {
                $promoModel = new \App\Models\PromoModel();
                $promo = $promoModel->where('code', strtoupper($promoCode))->where('status', 'active')->first();
                if (!$promo || strtotime($promo['valid_until']) < time()) {
                    return $this->respond(['status' => 400, 'message' => 'Promo tidak valid atau kedaluwarsa', 'data' => null], 400);
                }

                if ($promo['discount_type'] === 'percent') {
                    $pct = (float)$promo['discount_amount'];
                    if ($pct > 0 && $pct < 100) {
                        $discountAmount = round(($price / (1 - ($pct / 100))) * ($pct / 100));
                    }
                } else {
                    $discountAmount = (float)$promo['discount_amount'];
                }
            }
        }

        // --- VALIDASI TARIF SISI SERVER (ANTI PRICE-TAMPERING) ---
        $settingModel = new \App\Models\SettingModel();
        $rawService = strtolower($this->request->getVar('service_type') ?? 'motor');
        $baseCost = 12000;
        $kmCost = 2500;
        $usedBaseDistance = 3.0;

        if ($rawService === 'car' || $rawService === 'car_premium' || $rawService === 'mobil') {
            $baseCost = (float)($settingModel->where('setting_key', 'ride_mobil_base_fare')->first()['setting_value'] ?? 15000);
            $kmCost = (float)($settingModel->where('setting_key', 'ride_mobil_per_km_fare')->first()['setting_value'] ?? 4000);
            $usedBaseDistance = (float)($settingModel->where('setting_key', 'ride_mobil_base_fare_distance')->first()['setting_value'] ?? 3.0);
        } else if ($rawService === 'mai_send_motor') {
            $baseCost = (float)($settingModel->where('setting_key', 'send_motor_base_fare')->first()['setting_value'] ?? 8000);
            $kmCost = (float)($settingModel->where('setting_key', 'send_motor_per_km')->first()['setting_value'] ?? 2000);
            $usedBaseDistance = (float)($settingModel->where('setting_key', 'send_motor_base_distance')->first()['setting_value'] ?? 3.0);
        } else if ($rawService === 'mai_send_mobil') {
            $baseCost = (float)($settingModel->where('setting_key', 'send_mobil_base_fare')->first()['setting_value'] ?? 20000);
            $kmCost = (float)($settingModel->where('setting_key', 'send_mobil_per_km')->first()['setting_value'] ?? 5000);
            $usedBaseDistance = (float)($settingModel->where('setting_key', 'send_mobil_base_distance')->first()['setting_value'] ?? 3.0);
        } else if ($rawService === 'mai_titip_motor') {
            $baseCost = (float)($settingModel->where('setting_key', 'titip_motor_base_fare')->first()['setting_value'] ?? 10000);
            $kmCost = (float)($settingModel->where('setting_key', 'titip_motor_per_km')->first()['setting_value'] ?? 2500);
            $usedBaseDistance = (float)($settingModel->where('setting_key', 'titip_motor_base_distance')->first()['setting_value'] ?? 3.0);
        } else if ($rawService === 'mai_titip_mobil') {
            $baseCost = (float)($settingModel->where('setting_key', 'titip_mobil_base_fare')->first()['setting_value'] ?? 25000);
            $kmCost = (float)($settingModel->where('setting_key', 'titip_mobil_per_km')->first()['setting_value'] ?? 6000);
            $usedBaseDistance = (float)($settingModel->where('setting_key', 'titip_mobil_base_distance')->first()['setting_value'] ?? 3.0);
        } else {
            $baseCost = (float)($settingModel->where('setting_key', 'base_fare')->first()['setting_value'] ?? 12000);
            $kmCost = (float)($settingModel->where('setting_key', 'per_km_fare')->first()['setting_value'] ?? 2500);
            $usedBaseDistance = (float)($settingModel->where('setting_key', 'base_fare_distance')->first()['setting_value'] ?? 3.0);
        }

        $checkDist = $dist > 0 ? $dist : 0.1;
        if ($checkDist <= $usedBaseDistance) {
            $serverGross = round($baseCost);
        } else {
            $extraDist = $checkDist - $usedBaseDistance;
            $serverGross = round($baseCost + ($extraDist * $kmCost));
        }

        $expectedPrice = max(3000, $serverGross - $discountAmount);

        // Jika klien mengirim harga yang jauh di bawah perhitungan resmi server (toleransi toleran fluktuasi rute 15% atau Rp 2.000)
        if ($price < ($expectedPrice * 0.85) - 2000) {
            $price = $expectedPrice; // Paksa gunakan tarif resmi server yang sah
        }

        // Validasi Saldo Mai-Pay
        if ($paymentMethod == 'maipay') {
            $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
            if (!$user || (float)$user->balance < $price) {
                return $this->respond(['status' => 400, 'message' => 'Saldo Mai-Pay tidak mencukupi', 'data' => null], 400);
            }
        }

        $db->transStart();

        $orderId = $orderModel->insert([
            'user_id' => $payload['uid'],
            'service_type' => $serviceType,
            'pickup_lat' => $this->request->getVar('pickup_lat'),
            'pickup_lng' => $this->request->getVar('pickup_lng'),
            'dropoff_lat' => $this->request->getVar('dropoff_lat'),
            'dropoff_lng' => $this->request->getVar('dropoff_lng'),
            'pickup_address' => $this->request->getVar('pickup_address'),
            'dropoff_address' => $this->request->getVar('dropoff_address'),
            'notes' => $this->request->getVar('notes'),
            'distance' => $this->request->getVar('distance'),
            'estimated_time' => $estimatedTime,
            'price' => $price,
            'discount_amount' => $discountAmount,
            'promo_code' => !empty($promoCode) ? $promoCode : null,
            'payment_method' => $paymentMethod,

            // Kolom baru untuk Mai-Send & Mai-Titip
            'recipient_name' => $this->request->getVar('recipient_name'),
            'recipient_phone' => $this->request->getVar('recipient_phone'),
            'item_description' => $this->request->getVar('item_description'),
            'item_weight' => $this->request->getVar('item_weight'),
            'titip_item_price' => $this->request->getVar('titip_item_price'),

            'status' => 'pending' // Menunggu driver
        ]);

        // POTONG SALDO DI AWAL SECARA ATOMIK (Anti Double-Spend / Race Condition)
        if ($paymentMethod == 'maipay') {
            $deducted = $db->table('users')
                ->where('id', $payload['uid'])
                ->where('balance >=', $price)
                ->set('balance', 'balance - ' . $price, false)
                ->update();

            if (!$deducted) {
                $db->transRollback();
                return $this->respond(['status' => 400, 'message' => 'Saldo Mai-Pay tidak mencukupi atau transaksi gagal.', 'data' => null], 400);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->respond(['status' => 500, 'message' => 'Gagal membuat pesanan', 'data' => null], 500);
        }

        // Send Push Notification to nearby online drivers ONLY (Radius: 3 KM)
        $pickupLat = $this->request->getVar('pickup_lat');
        $pickupLng = $this->request->getVar('pickup_lng');

        $radius = 3; // 3 KM

        $builder = $db->table('drivers');
        $builder->select('drivers.*, (
            6371 * acos(
                LEAST(1.0, cos(radians(' . $db->escape($pickupLat) . ')) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(' . $db->escape($pickupLng) . ')) + 
                sin(radians(' . $db->escape($pickupLat) . ')) * 
                sin(radians(latitude)))
            )
        ) AS distance');
        $builder->where('is_online', 1);
        $builder->where('status', 'active'); // Abaikan driver yang disuspend atau nonaktif
        $builder->where('verification_status', 'approved'); // Hanya driver yang sudah lolos verifikasi
        $builder->where('fcm_token !=', null);
        $builder->where('fcm_token !=', '');

        // --- Auto-Migration & ZOMBIE DRIVER FILTER ---
        try {
            if ($db->fieldExists('last_heartbeat_at', 'drivers')) {
                // Abaikan driver yang detaknya sudah mati > 5 menit yang lalu
                $builder->where('last_heartbeat_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')));
            }
        } catch (\Throwable $e) {
        }
        // -----------------------------

        // Filter kendaraan sesuai tipe (Semua jastip motor / motor / mai_send masuk ke armada motor)
        $isCar = in_array(strtolower($serviceType), ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        if ($isCar) {
            $builder->whereIn('vehicle_type', ['mobil', 'car', 'car_premium']);
        } else {
            $builder->where('vehicle_type', 'motor');
        }

        $builder->having('distance <=', 5); // Radius pencarian 5 KM (sinkron dengan radar driver)
        $builder->orderBy('distance', 'ASC');
        $builder->limit(20); // Kirim ke maksimal 20 driver terdekat

        $nearbyDrivers = $builder->get()->getResult();

        if (count($nearbyDrivers) > 0) {
            try {
                $fcmService = new \App\Libraries\FcmService();
                foreach ($nearbyDrivers as $driver) {
                    if (!empty($driver->fcm_token)) {
                        $fcmService->sendPushNotification(
                            $driver->fcm_token,
                            "Order Baru Masuk!",
                            "Ada penumpang mencari supir di sekitar Anda (" . round($driver->distance, 1) . " KM).",
                            ['type' => 'new_order', 'order_id' => $orderId]
                        );
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'FCM Broadcast Error: ' . $e->getMessage());
            }
        } else {
            // DIAGNOSTIK LOG: Catat ke file log /writable/logs/ mengapa tidak ada driver
            $debugMsg = "TIDAK ADA DRIVER DITEMUKAN untuk Order ID $orderId. Filter yang digunakan: " .
                "Service=$serviceType, IsCar=" . ($isCar ? 'Yes' : 'No') .
                ", Radius=5KM, Status=Active, Verifikasi=Approved, Online=1";
            log_message('error', $debugMsg);
        }

        return $this->respond([
            'status' => 201,
            'message' => 'Pesanan berhasil dibuat, sedang mencari pengemudi',
            'data' => [
                'order_id' => $orderId,
                'estimated_time' => $estimatedTime,
                'estimated_minutes' => $estMins
            ]
        ], 201);
    }

    public function active()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new OrderModel();

        $db = \Config\Database::connect();
        try {
            if (!$db->fieldExists('vehicle_brand', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_brand VARCHAR(100) NULL AFTER vehicle_plate");
            }
            if (!$db->fieldExists('vehicle_capacity', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_capacity INT DEFAULT 4 AFTER vehicle_brand");
            }
        } catch (\Throwable $e) {
        }

        $order = $orderModel->select('orders.*, drivers.name as driver_name, drivers.phone as driver_phone, drivers.vehicle_plate, drivers.vehicle_brand as driver_vehicle_brand, drivers.vehicle_capacity as driver_vehicle_capacity, drivers.vehicle_type as driver_vehicle_type, drivers.latitude as driver_lat, drivers.longitude as driver_lng, drivers.photo as driver_photo, drivers.qris_image as driver_qris_image, drivers.qris_merchant_name as driver_qris_merchant_name, drivers.qris_is_active as driver_qris_is_active')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left')
            ->where('orders.user_id', $payload['uid'])
            ->whereIn('orders.status', ['pending', 'accepted', 'arrived', 'in_progress'])
            ->orderBy('orders.id', 'DESC')
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Tidak ada pesanan aktif', 'data' => null], 404);
        }

        if (!empty($order['driver_name'])) {
            $isCarDriver = in_array(strtolower($order['driver_vehicle_type'] ?? ''), ['mobil', 'car', 'car_premium']);
            if (empty($order['driver_vehicle_brand'])) {
                $order['driver_vehicle_brand'] = $isCarDriver ? 'Toyota Avanza' : 'Honda Beat';
            }
            if (empty($order['driver_vehicle_capacity'])) {
                $order['driver_vehicle_capacity'] = $isCarDriver ? 4 : 1;
            }
        }

        if (!empty($order['driver_photo']) && strpos($order['driver_photo'], 'http') !== 0) {
            $order['driver_photo'] = base_url($order['driver_photo']);
        }

        if (!empty($order['driver_qris_image']) && strpos($order['driver_qris_image'], 'http') !== 0) {
            $order['driver_qris_image_url'] = base_url($order['driver_qris_image']);
        } else {
            $order['driver_qris_image_url'] = !empty($order['driver_qris_image']) ? $order['driver_qris_image'] : null;
        }

        // Hitung estimasi waktu tiba cerdas
        $dist = (float)($order['distance'] ?? 0);
        $sType = strtolower($order['service_type'] ?? 'motor');
        $isCar = in_array($sType, ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        $isTitip = strpos($sType, 'titip') !== false;
        $estMins = $isCar ? round($dist * 3.0 + 5) : round($dist * 2.4 + 3);
        if ($isTitip) $estMins += 12;
        if ($estMins < 3) $estMins = 3;
        $clockArr = date('H:i', strtotime("+$estMins minutes"));

        $order['estimated_minutes'] = $estMins;
        $order['estimated_arrival_clock'] = $clockArr;
        if (empty($order['estimated_time'])) {
            $order['estimated_time'] = "$estMins mnt (Tiba ~$clockArr)";
        }

        $this->enrichUserTitipOrder($order);

        return $this->respond([
            'status' => 200,
            'message' => 'Pesanan aktif ditemukan',
            'data' => $order
        ]);
    }

    public function history()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new OrderModel();

        $orders = $orderModel->select('orders.*, drivers.name as driver_name, drivers.vehicle_plate, drivers.photo as driver_photo')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left')
            ->where('orders.user_id', $payload['uid'])
            ->orderBy('orders.created_at', 'DESC')
            ->findAll(20);

        // Ambil data Food Orders
        $foodOrderModel = new \App\Models\FoodOrderModel();
        $foodOrdersRaw = $foodOrderModel->select('food_orders.*, merchants.name as merchant_name, drivers.name as driver_name, drivers.photo as driver_photo, drivers.vehicle_plate')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('food_orders.user_id', $payload['uid'])
            ->orderBy('food_orders.created_at', 'DESC')
            ->findAll(20);

        $db = \Config\Database::connect();
        $foodOrders = [];
        foreach ($foodOrdersRaw as $fo) {
            $items = $db->table('food_order_items')
                ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.unit, merchant_menus.price as menu_original_price, merchant_menus.photo as menu_photo')
                ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
                ->where('food_order_items.food_order_id', $fo['id'])
                ->get()
                ->getResultArray();

            foreach ($items as &$it) {
                $itQty = (int)($it['quantity'] ?? ($it['qty'] ?? 1));
                if ($itQty <= 0) $itQty = 1;
                $itPrice = (float)($it['price'] ?? 0);
                if ($itPrice <= 0 && !empty($it['menu_original_price'])) {
                    $itPrice = (float)$it['menu_original_price'];
                }
                if ($itPrice <= 0 && count($items) === 1 && !empty($fo['food_total'])) {
                    $itPrice = (float)$fo['food_total'] / $itQty;
                }
                $it['quantity'] = $itQty;
                $it['qty'] = $itQty;
                $it['price'] = $itPrice;
                $it['total_price'] = $itPrice * $itQty;
            }
            unset($it);

            $foodOrders[] = [
                'id' => $fo['id'],
                'user_id' => $fo['user_id'],
                'driver_id' => $fo['driver_id'],
                'type' => 'food',
                'order_type' => $fo['order_type'] ?? 'food',
                'pickup_address' => $fo['merchant_name'], // Tampilkan nama merchant sebagai pickup
                'dropoff_address' => $fo['delivery_address'],
                'distance' => $fo['distance'] ?? 0,
                'price' => $fo['total_price'],
                'food_total' => $fo['food_total'] ?? 0,
                'delivery_fee' => $fo['delivery_fee'] ?? 0,
                'discount_amount' => $fo['discount_amount'] ?? 0,
                'payment_method' => $fo['payment_method'] ?? 'cash',
                'status' => $fo['status'],
                'created_at' => $fo['created_at'],
                'updated_at' => $fo['updated_at'] ?? null,
                'completed_at' => $fo['completed_at'] ?? null,
                'rating' => $fo['driver_rating'], // for unified check
                'merchant_rating' => $fo['merchant_rating'],
                'driver_name' => $fo['driver_name'],
                'driver_photo' => $fo['driver_photo'],
                'vehicle_plate' => $fo['vehicle_plate'],
                'merchant_name' => $fo['merchant_name'],
                'cancel_reason' => $fo['cancel_reason'] ?? null,
                'items' => $items
            ];
        }

        // Gabungkan dan urutkan
        $allOrders = array_merge($orders, $foodOrders);
        usort($allOrders, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Batasi 20 terbaru
        $allOrders = array_slice($allOrders, 0, 20);

        return $this->respond([
            'status' => 200,
            'message' => 'Riwayat pesanan',
            'data' => $allOrders
        ]);
    }

    public function track($id)
    {
        $payload = $_SERVER['jwt_payload'];
        $db = \Config\Database::connect();
        try {
            if (!$db->fieldExists('vehicle_brand', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_brand VARCHAR(100) NULL AFTER vehicle_plate");
            }
            if (!$db->fieldExists('vehicle_capacity', 'drivers')) {
                $db->query("ALTER TABLE drivers ADD COLUMN vehicle_capacity INT DEFAULT 4 AFTER vehicle_brand");
            }
        } catch (\Throwable $e) {
        }

        $orderModel = new OrderModel();
        // Gabungkan tabel orders dengan drivers untuk mendapat info driver dan lokasinya
        $order = $orderModel->select('orders.*, orders.id as id, drivers.name as driver_name, drivers.vehicle_plate, drivers.vehicle_brand as driver_vehicle_brand, drivers.vehicle_capacity as driver_vehicle_capacity, drivers.vehicle_type as driver_vehicle_type, drivers.phone as driver_phone, drivers.latitude as driver_lat, drivers.longitude as driver_lng, drivers.photo as driver_photo, drivers.rating as driver_rating, drivers.rating_count as driver_rating_count, drivers.qris_image as driver_qris_image, drivers.qris_merchant_name as driver_qris_merchant_name, drivers.qris_is_active as driver_qris_is_active')
            ->join('drivers', 'drivers.id = orders.driver_id', 'left')
            ->where('orders.user_id', $payload['uid']) // Pastikan pesanan milik user ini
            ->where('orders.id', $id)
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan atau bukan milik Anda'], 404);
        }

        if (!empty($order['driver_name'])) {
            $isCarDriver = in_array(strtolower($order['driver_vehicle_type'] ?? ''), ['mobil', 'car', 'car_premium']);
            if (empty($order['driver_vehicle_brand'])) {
                $order['driver_vehicle_brand'] = $isCarDriver ? 'Toyota Avanza' : 'Honda Beat';
            }
            if (empty($order['driver_vehicle_capacity'])) {
                $order['driver_vehicle_capacity'] = $isCarDriver ? 4 : 1;
            }
        }

        if (!empty($order['driver_photo']) && strpos($order['driver_photo'], 'http') !== 0) {
            $order['driver_photo'] = base_url($order['driver_photo']);
        }

        if (!empty($order['driver_qris_image']) && strpos($order['driver_qris_image'], 'http') !== 0) {
            $order['driver_qris_image_url'] = base_url($order['driver_qris_image']);
        } else {
            $order['driver_qris_image_url'] = !empty($order['driver_qris_image']) ? $order['driver_qris_image'] : null;
        }

        $db = \Config\Database::connect();
        $unreadCount = 0;
        try {
            if ($db->tableExists('order_chats')) {
                $unreadCount = $db->table('order_chats')
                    ->where('order_id', $id)
                    ->where('sender_type', 'driver')
                    ->where('is_read', 0)
                    ->countAllResults();
            }
        } catch (\Throwable $e) {
            // Abaikan error jika kolom is_read belum ada di database live
        }

        $order['unread_count'] = $unreadCount;

        // Hitung estimasi waktu tiba cerdas
        $dist = (float)($order['distance'] ?? 0);
        $sType = strtolower($order['service_type'] ?? 'motor');
        $isCar = in_array($sType, ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        $isTitip = strpos($sType, 'titip') !== false;
        $estMins = $isCar ? round($dist * 3.0 + 5) : round($dist * 2.4 + 3);
        if ($isTitip) $estMins += 12;
        if ($estMins < 3) $estMins = 3;
        $clockArr = date('H:i', strtotime("+$estMins minutes"));

        $order['estimated_minutes'] = $estMins;
        $order['estimated_arrival_clock'] = $clockArr;
        if (empty($order['estimated_time'])) {
            $order['estimated_time'] = "$estMins mnt (Tiba ~$clockArr)";
        }

        $this->enrichUserTitipOrder($order);

        return $this->respond(['status' => 200, 'message' => 'Detail pesanan', 'data' => $order]);
    }

    public function cancel()
    {
        $payload = $_SERVER['jwt_payload'];

        $rawInput = file_get_contents('php://input');
        $json = json_decode($rawInput);

        $orderId = $json->order_id ?? $this->request->getVar('order_id');

        $orderModel = new OrderModel();
        $order = $orderModel->where('user_id', $payload['uid'])->where('id', $orderId)->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan (ID: ' . ($orderId ?: 'kosong') . ')'], 404);
        }

        // KEAMANAN KRITIS: Cegah eksploitasi pembatalan ganda / refund berulang
        if ($order['status'] === 'cancelled') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan ini sudah dibatalkan sebelumnya.'], 400);
        }

        if ($order['status'] === 'completed') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan sudah selesai dan tidak dapat dibatalkan.'], 400);
        }

        if ($order['status'] === 'in_progress') {
            return $this->respond(['status' => 400, 'message' => 'Perjalanan sedang berlangsung dan tidak dapat dibatalkan.'], 400);
        }

        $reason = $json->reason ?? $this->request->getVar('reason');

        $updateData = ['status' => 'cancelled'];
        if ($reason) {
            $updateData['cancel_reason'] = $reason;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Kunci baris status agar tidak terjadi race condition
        $affected = $db->table('orders')
            ->where('id', $orderId)
            ->where('user_id', $payload['uid'])
            ->whereIn('status', ['pending', 'accepted'])
            ->update($updateData);

        if (!$affected) {
            $db->transRollback();
            return $this->respond(['status' => 400, 'message' => 'Pesanan tidak dapat dibatalkan saat ini.'], 400);
        }

        // REFUND SALDO HANYA SEKALI JIKA STATUS BERHASIL DIUBAH
        if ($order['payment_method'] == 'maipay' && (float)$order['price'] > 0) {
            $db->table('users')->where('id', $payload['uid'])->set('balance', 'balance + ' . (float)$order['price'], false)->update();
        }

        $db->transComplete();

        return $this->respond(['status' => 200, 'message' => 'Pesanan berhasil dibatalkan dan dana telah dikembalikan (jika menggunakan Mai-Pay)']);
    }

    public function rate()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getVar('order_id');
        $rating = $this->request->getVar('rating');
        $review = $this->request->getVar('review');
        $tip = $this->request->getVar('tip'); // Opsional

        if (!$orderId || !$rating) {
            return $this->respond(['status' => 400, 'message' => 'Data tidak lengkap'], 400);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->where('user_id', $payload['uid'])->find($orderId);

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($order['status'] != 'completed') {
            return $this->respond(['status' => 400, 'message' => 'Hanya pesanan selesai yang dapat diberi rating'], 400);
        }

        // Update rating pesanan
        $updateData = ['rating' => $rating];
        if ($review) {
            $updateData['notes'] = $order['notes'] . " | Ulasan: " . $review;
        }

        $orderModel->update($orderId, $updateData);

        // Jika ada tip, potong saldo user dan tambahkan ke saldo driver
        $driverModel = new \App\Models\DriverModel();

        if ($tip > 0) {
            $userModel = new \App\Models\UserModel();

            $user = $userModel->find($payload['uid']);
            if ($user['balance'] >= $tip) {
                // Potong saldo user
                $userModel->update($payload['uid'], ['balance' => $user['balance'] - $tip]);

                // Tambah saldo driver
                $driver = $driverModel->find($order['driver_id']);
                if ($driver) {
                    $driverModel->update($order['driver_id'], ['balance' => $driver['balance'] + $tip]);
                }
            }
        }

        // Hitung ulang rata-rata rating driver
        $driver = $driverModel->find($order['driver_id']);
        if ($driver) {
            $currentRating = (float)($driver['rating'] ?? 5.0);
            $currentCount = (int)($driver['rating_count'] ?? 0);

            $newCount = $currentCount + 1;
            $newRating = (($currentRating * $currentCount) + $rating) / $newCount;

            // Format rating menjadi 1 angka desimal
            $newRating = number_format($newRating, 1, '.', '');

            $driverModel->update($order['driver_id'], [
                'rating' => $newRating,
                'rating_count' => $newCount
            ]);
        }

        return $this->respond(['status' => 200, 'message' => 'Penilaian berhasil disimpan']);
    }

    private function isPointInPolygon($lat, $lng, $polygon)
    {
        $vertices_count = count($polygon);
        $inside = false;
        for ($i = 0, $j = $vertices_count - 1; $i < $vertices_count; $j = $i++) {
            // Asumsi format GeoJSON: [lat, lng]
            $xi = (float)$polygon[$i][0];
            $yi = (float)$polygon[$i][1];
            $xj = (float)$polygon[$j][0];
            $yj = (float)$polygon[$j][1];

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }

    private function enrichUserTitipOrder(&$order)
    {
        $sType = strtolower($order['service_type'] ?? 'motor');
        $isTitip = strpos($sType, 'titip') !== false;
        if (!$isTitip) return;

        $order['is_titip'] = true;
        $titipCost = !empty($order['titip_total_cost']) ? (float)$order['titip_total_cost'] : (float)($order['titip_item_price'] ?? 0);
        $order['titip_spending_cost'] = $titipCost;
        $order['cash_to_pay_driver'] = ($order['payment_method'] === 'cash')
            ? ((float)$order['price'] + $titipCost)
            : $titipCost;

        if (!empty($order['titip_receipt_img']) && strpos($order['titip_receipt_img'], 'http') !== 0) {
            $order['titip_receipt_img_url'] = base_url($order['titip_receipt_img']);
        } else {
            $order['titip_receipt_img_url'] = !empty($order['titip_receipt_img']) ? $order['titip_receipt_img'] : null;
        }

        if (!empty($order['proof_of_pickup']) && strpos($order['proof_of_pickup'], 'http') !== 0) {
            $order['proof_of_pickup_url'] = base_url($order['proof_of_pickup']);
        }
        if (!empty($order['proof_of_delivery']) && strpos($order['proof_of_delivery'], 'http') !== 0) {
            $order['proof_of_delivery_url'] = base_url($order['proof_of_delivery']);
        }

        $st = $order['status'] ?? 'pending';
        if ($st === 'pending') {
            $order['titip_status_title'] = 'Mencari Driver Mai-Titip...';
            $order['titip_status_desc'] = 'Menghubungkan ke driver terdekat untuk berbelanja.';
            $order['titip_step'] = 1;
        } elseif ($st === 'accepted') {
            $order['titip_status_title'] = 'Driver Menuju Lokasi Belanja 🏪';
            $order['titip_status_desc'] = 'Driver sedang menuju toko/lokasi belanja untuk membelikan barang Anda.';
            $order['titip_step'] = 2;
        } elseif ($st === 'arrived') {
            $order['titip_status_title'] = 'Driver Sedang Berbelanja 🛍️';
            $order['titip_status_desc'] = 'Driver telah tiba di toko dan sedang menyiapkan pesanan belanja Anda.';
            $order['titip_step'] = 3;
        } elseif ($st === 'in_progress') {
            $order['titip_status_title'] = 'Belanja Selesai! Sedang Diantar 🛵';
            $order['titip_status_desc'] = 'Driver telah selesai berbelanja dan sedang mengantarkan belanjaan ke alamat Anda.';
            $order['titip_step'] = 4;
        } elseif ($st === 'completed') {
            $order['titip_status_title'] = 'Pesanan Belanja Selesai 🎉';
            $order['titip_status_desc'] = 'Belanjaan telah diterima dengan baik.';
            $order['titip_step'] = 5;
        }
    }
}
