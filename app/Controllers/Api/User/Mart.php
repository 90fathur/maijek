<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MerchantModel;
use App\Models\MerchantMenuModel;
use App\Models\FoodOrderModel;
use App\Models\FoodOrderItemModel;

class Mart extends ResourceController
{
    protected $format = 'json';

    // 8 Kategori Utama MaiMart
    public static $mainCategories = [
        [
            'id' => 'apotek',
            'name' => 'Apotek & Kesehatan',
            'icon' => 'medical_services',
            'emoji' => '💊',
            'color' => '#EF4444',
            'bg_color' => '#FEE2E2',
            'description' => 'Obat-obatan, vitamin, suplemen & P3K'
        ],
        [
            'id' => 'sayur_buah',
            'name' => 'Sayur & Buah',
            'icon' => 'eco',
            'emoji' => '🥬',
            'color' => '#10B981',
            'bg_color' => '#D1FAE5',
            'description' => 'Sayuran segar, buah-buahan & bumbu dapur'
        ],
        [
            'id' => 'daging_ayam',
            'name' => 'Daging & Ayam',
            'icon' => 'kebab_dining',
            'emoji' => '🍗',
            'color' => '#F59E0B',
            'bg_color' => '#FEF3C7',
            'description' => 'Daging sapi, ayam potong & ikan segar'
        ],
        [
            'id' => 'sembako',
            'name' => 'Sembako',
            'icon' => 'inventory_2',
            'emoji' => '🥚',
            'color' => '#D97706',
            'bg_color' => '#FDE68A',
            'description' => 'Beras, minyak, gula, telur & mie instan'
        ],
        [
            'id' => 'bayi',
            'name' => 'Kebutuhan Bayi',
            'icon' => 'child_care',
            'emoji' => '🍼',
            'color' => '#3B82F6',
            'bg_color' => '#DBEAFE',
            'description' => 'Popok, susu formula, bubur & minyak telon'
        ],
        [
            'id' => 'pet_shop',
            'name' => 'Pet Shop',
            'icon' => 'pets',
            'emoji' => '🐾',
            'color' => '#8B5CF6',
            'bg_color' => '#EDE9FE',
            'description' => 'Makanan kucing, anjing & perawatan hewan'
        ],
        [
            'id' => 'rumah_tangga',
            'name' => 'Rumah Tangga',
            'icon' => 'cleaning_services',
            'emoji' => '🧴',
            'color' => '#06B6D4',
            'bg_color' => '#CFFAFE',
            'description' => 'Sabun, deterjen, pewangi & pembersih'
        ],
        [
            'id' => 'lainnya',
            'name' => 'Lainnya',
            'icon' => 'shopping_cart',
            'emoji' => '🛒',
            'color' => '#6B7280',
            'bg_color' => '#F3F4F6',
            'description' => 'Alat tulis, baterai & kebutuhan umum'
        ],
    ];

    // 1. Endpoint Daftar Kategori Utama MaiMart
    public function categories()
    {
        return $this->respond([
            'status' => 200,
            'message' => 'Daftar Kategori Utama MaiMart',
            'data' => self::$mainCategories
        ]);
    }

    // 2. Endpoint Daftar Toko / Merchant MaiMart
    public function merchants()
    {
        $menuModel = new MerchantMenuModel();

        $lat = $this->request->getVar('lat');
        $lng = $this->request->getVar('lng');
        $q = trim($this->request->getVar('q') ?? '');
        $category = trim($this->request->getVar('category') ?? '');

        $db = \Config\Database::connect();
        $builder = $db->table('merchants')->where('is_open', 1);

        // Filter toko mart: merchant_type = 'mart' atau 'all'
        // Jika kolom merchant_type ada, terapkan filter
        if ($db->fieldExists('merchant_type', 'merchants')) {
            $builder->whereIn('merchant_type', ['mart', 'all']);
        }

        // Filter kategori toko jika ada
        if (!empty($category) && strtolower($category) !== 'semua') {
            if ($db->fieldExists('category', 'merchants')) {
                $builder->groupStart()
                    ->like('merchants.category', $category)
                    ->orLike('merchants.name', $category)
                    ->groupEnd();
            } else {
                $builder->like('merchants.name', $category);
            }
        }

        // Pencarian nama toko / alamat
        if (!empty($q)) {
            $builder->groupStart()
                ->like('merchants.name', $q)
                ->orLike('merchants.address', $q)
                ->groupEnd();
        }

        if ($lat && $lng) {
            $builder->select('merchants.*, (
                6371 * acos(
                    cos(radians(' . $db->escape($lat) . ')) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(' . $db->escape($lng) . ')) + 
                    sin(radians(' . $db->escape($lat) . ')) * 
                    sin(radians(latitude))
                )
            ) AS distance')
                ->orderBy('distance', 'ASC');
        } else {
            $builder->select('merchants.*')->orderBy('id', 'DESC');
        }

        $merchants = $builder->get()->getResultArray();

        // Fallback: Jika belum ada toko yang diset merchant_type = 'mart' di database lokal/baru,
        // tampilkan toko aktif yang ada agar antarmuka tidak kosong saat awal rilis/testing
        if (empty($merchants) && empty($q) && (empty($category) || strtolower($category) === 'semua')) {
            $fallbackBuilder = $db->table('merchants')->where('is_open', 1);
            if ($lat && $lng) {
                $fallbackBuilder->select('merchants.*, (
                    6371 * acos(
                        cos(radians(' . $db->escape($lat) . ')) * 
                        cos(radians(latitude)) * 
                        cos(radians(longitude) - radians(' . $db->escape($lng) . ')) + 
                        sin(radians(' . $db->escape($lat) . ')) * 
                        sin(radians(latitude))
                    )
                ) AS distance')->orderBy('distance', 'ASC');
            } else {
                $fallbackBuilder->select('merchants.*')->orderBy('id', 'DESC');
            }
            $merchants = $fallbackBuilder->limit(10)->get()->getResultArray();
        }

        foreach ($merchants as &$m) {
            $photoPath = !empty($m['photo']) ? $m['photo'] : (!empty($m['business_image']) ? $m['business_image'] : '');
            if (!empty($photoPath) && strpos($photoPath, 'http') !== 0) {
                $m['photo'] = base_url($photoPath);
            }
            unset($m['password']);
            unset($m['fcm_token']);

            // Produk unggulan
            $topMenu = $menuModel->where('merchant_id', $m['id'])->where('is_available', 1)->first();
            $m['top_product'] = $topMenu ? $topMenu['name'] : 'Produk Lengkap';
            $m['category_label'] = !empty($m['category']) ? $m['category'] : 'Toko Kebutuhan';
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Daftar Toko MaiMart',
            'data' => $merchants
        ]);
    }

    // 3. Endpoint Pencarian Produk & Toko MaiMart
    public function search()
    {
        $q = trim($this->request->getVar('q') ?? '');
        $category = trim($this->request->getVar('category') ?? '');
        $lat = $this->request->getVar('lat');
        $lng = $this->request->getVar('lng');

        $db = \Config\Database::connect();

        // 1. Cari Produk yang cocok
        $menuBuilder = $db->table('merchant_menus')
            ->select('merchant_menus.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.photo as merchant_photo, merchants.latitude, merchants.longitude, merchants.is_open')
            ->join('merchants', 'merchants.id = merchant_menus.merchant_id')
            ->where('merchant_menus.is_available', 1)
            ->where('merchants.is_open', 1);

        if ($db->fieldExists('merchant_type', 'merchants')) {
            $menuBuilder->whereIn('merchants.merchant_type', ['mart', 'all']);
        }

        if ($lat && $lng) {
            $menuBuilder->select('(
                6371 * acos(
                    cos(radians(' . $db->escape($lat) . ')) * 
                    cos(radians(merchants.latitude)) * 
                    cos(radians(merchants.longitude) - radians(' . $db->escape($lng) . ')) + 
                    sin(radians(' . $db->escape($lat) . ')) * 
                    sin(radians(merchants.latitude))
                )
            ) AS distance');
        }

        if (!empty($q)) {
            $menuBuilder->groupStart()
                ->like('merchant_menus.name', $q)
                ->orLike('merchant_menus.description', $q)
                ->orLike('merchants.name', $q)
                ->groupEnd();
        }

        if (!empty($category) && strtolower($category) !== 'semua') {
            $menuBuilder->groupStart();
            if ($db->fieldExists('category', 'merchant_menus')) {
                $menuBuilder->like('merchant_menus.category', $category);
            }
            $menuBuilder->orLike('merchant_menus.name', $category)
                ->groupEnd();
        }

        if ($lat && $lng) {
            $menuBuilder->orderBy('distance', 'ASC');
        } else {
            $menuBuilder->orderBy('merchant_menus.id', 'DESC');
        }

        $menus = $menuBuilder->limit(50)->get()->getResultArray();

        foreach ($menus as &$menu) {
            if (!empty($menu['photo']) && strpos($menu['photo'], 'http') !== 0) {
                $menu['photo'] = base_url($menu['photo']);
            }
            if (!empty($menu['merchant_photo']) && strpos($menu['merchant_photo'], 'http') !== 0) {
                $menu['merchant_photo'] = base_url($menu['merchant_photo']);
            }
            if (!empty($menu['discount_price']) && (float)$menu['discount_price'] > 0 && (float)$menu['discount_price'] < (float)$menu['price']) {
                $menu['discount_percent'] = round((((float)$menu['price'] - (float)$menu['discount_price']) / (float)$menu['price']) * 100);
            } else {
                $menu['discount_percent'] = 0;
            }
            $menu['unit'] = $menu['unit'] ?? 'pcs';
        }

        // 2. Cari Toko yang cocok
        $merchantBuilder = $db->table('merchants')->where('is_open', 1);

        if ($db->fieldExists('merchant_type', 'merchants')) {
            $merchantBuilder->whereIn('merchant_type', ['mart', 'all']);
        }

        if ($lat && $lng) {
            $merchantBuilder->select('merchants.*, (
                6371 * acos(
                    cos(radians(' . $db->escape($lat) . ')) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(' . $db->escape($lng) . ')) + 
                    sin(radians(' . $db->escape($lat) . ')) * 
                    sin(radians(latitude))
                )
            ) AS distance');
        } else {
            $merchantBuilder->select('merchants.*');
        }

        if (!empty($q)) {
            $merchantBuilder->groupStart()
                ->like('merchants.name', $q)
                ->orLike('merchants.address', $q)
                ->groupEnd();
        }

        if (!empty($category) && strtolower($category) !== 'semua' && $db->fieldExists('category', 'merchants')) {
            $merchantBuilder->like('merchants.category', $category);
        }

        if ($lat && $lng) {
            $merchantBuilder->orderBy('distance', 'ASC');
        } else {
            $merchantBuilder->orderBy('id', 'DESC');
        }

        $merchants = $merchantBuilder->limit(20)->get()->getResultArray();
        $menuModel = new MerchantMenuModel();

        foreach ($merchants as &$m) {
            $photoPath = !empty($m['photo']) ? $m['photo'] : (!empty($m['business_image']) ? $m['business_image'] : '');
            if (!empty($photoPath) && strpos($photoPath, 'http') !== 0) {
                $m['photo'] = base_url($photoPath);
            }
            unset($m['password']);
            unset($m['fcm_token']);

            $topMenu = $menuModel->where('merchant_id', $m['id'])->where('is_available', 1)->first();
            $m['top_product'] = $topMenu ? $topMenu['name'] : 'Produk Lengkap';
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Hasil Pencarian MaiMart',
            'data' => [
                'products' => $menus,
                'merchants' => $merchants
            ]
        ]);
    }

    // 4. Detail Toko Mart & Produknya
    public function merchantDetail($id)
    {
        $merchantModel = new MerchantModel();
        $menuModel = new MerchantMenuModel();

        $merchant = $merchantModel->find($id);
        if (!$merchant) {
            return $this->respond(['status' => 404, 'message' => 'Toko MaiMart tidak ditemukan'], 404);
        }

        $photoPath = !empty($merchant['photo']) ? $merchant['photo'] : (!empty($merchant['business_image']) ? $merchant['business_image'] : '');
        if (!empty($photoPath) && strpos($photoPath, 'http') !== 0) {
            $merchant['photo'] = base_url($photoPath);
        }
        unset($merchant['password']);

        $menus = $menuModel->where('merchant_id', $id)->where('is_available', 1)->findAll();
        foreach ($menus as &$menu) {
            if (!empty($menu['photo']) && strpos($menu['photo'], 'http') !== 0) {
                $menu['photo'] = base_url($menu['photo']);
            }
            if (!empty($menu['discount_price']) && (float)$menu['discount_price'] > 0 && (float)$menu['discount_price'] < (float)$menu['price']) {
                $menu['discount_percent'] = round((((float)$menu['price'] - (float)$menu['discount_price']) / (float)$menu['price']) * 100);
            } else {
                $menu['discount_percent'] = 0;
            }
            $menu['unit'] = $menu['unit'] ?? 'pcs';
            $menu['category'] = $menu['category'] ?? 'Lainnya';
        }

        // Jadwal operasional
        $dayNames = [
            '1' => 'Senin', '2' => 'Selasa', '3' => 'Rabu',
            '4' => 'Kamis', '5' => 'Jumat', '6' => 'Sabtu', '7' => 'Minggu',
        ];
        $today = (string)date('N');
        $rawOpDays = trim($merchant['operating_days'] ?? '');
        $isOperatingToday = true;
        $opDaysText = 'Setiap Hari';

        if (!empty($rawOpDays) && strpos($rawOpDays, '{') === 0) {
            $decoded = json_decode($rawOpDays, true);
            if (isset($decoded['mode']) && $decoded['mode'] === 'daily' && isset($decoded['days'])) {
                $daysMap = $decoded['days'];
                $todayCfg = $daysMap[$today] ?? null;
                if ($todayCfg) {
                    $isOperatingToday = !empty($todayCfg['is_open']);
                    $todayOpen = $todayCfg['open'] ?? '08:00';
                    $todayClose = $todayCfg['close'] ?? '21:00';
                    $opDaysText = $isOperatingToday ? "Hari ini: $todayOpen - $todayClose" : "Hari ini Libur";
                    $merchant['opening_time'] = $todayOpen;
                    $merchant['closing_time'] = $todayClose;
                }
            }
        } else {
            $daysArr = !empty($rawOpDays) ? explode(',', $rawOpDays) : ['1', '2', '3', '4', '5', '6', '7'];
            $isOperatingToday = in_array($today, $daysArr);
        }

        $merchant['operating_days_text'] = $opDaysText;
        $merchant['is_operating_today'] = $isOperatingToday;
        $merchant['products'] = $menus;

        return $this->respond([
            'status' => 200,
            'message' => 'Detail Toko MaiMart',
            'data' => $merchant
        ]);
    }

    // 5. Pemesanan Belanja MaiMart Baru
    public function order()
    {
        $payload = $_SERVER['jwt_payload'];
        $rules = [
            'merchant_id' => 'required',
            'delivery_address' => 'required',
            'delivery_lat' => 'required',
            'delivery_lng' => 'required',
            'items' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi pemesanan gagal'], 400);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
        if ($user && isset($user->status) && $user->status === 'suspended') {
            return $this->respond(['status' => 403, 'message' => 'Akun Anda sedang ditangguhkan.'], 403);
        }

        $items = json_decode($this->request->getVar('items'), true);
        if (empty($items)) {
            return $this->respond(['status' => 400, 'message' => 'Keranjang belanja masih kosong'], 400);
        }

        $merchantId = $this->request->getVar('merchant_id');
        $merchant = $db->table('merchants')->where('id', $merchantId)->get()->getRow();
        if (!$merchant) {
            return $this->respond(['status' => 404, 'message' => 'Toko tidak ditemukan'], 404);
        }
        if ($merchant->is_open == 0) {
            return $this->respond(['status' => 400, 'message' => 'Toko sedang tutup'], 400);
        }

        // Anti-Cheat: Hitung total belanja dari database
        $merchantMenuModel = new MerchantMenuModel();
        $foodTotal = 0;
        $validItems = [];

        foreach ($items as $item) {
            $product = $merchantMenuModel->find($item['menu_id']);
            if (!$product || $product['merchant_id'] != $merchantId) {
                return $this->respond(['status' => 400, 'message' => 'Item produk tidak valid'], 400);
            }
            if ($product['is_available'] == 0) {
                return $this->respond(['status' => 400, 'message' => "Produk '{$product['name']}' sedang habis"], 400);
            }

            $realPrice = (!empty($product['discount_price']) && (float)$product['discount_price'] > 0 && (float)$product['discount_price'] < (float)$product['price'])
                ? (float)$product['discount_price']
                : (float)$product['price'];

            $foodTotal += ($realPrice * $item['quantity']);
            $item['price'] = $realPrice;
            $validItems[] = $item;
        }

        $vehicleType = strtolower($this->request->getVar('vehicle_type') ?? 'motor');
        if (!in_array($vehicleType, ['motor', 'mobil', 'car'])) {
            $vehicleType = 'motor';
        }

        // Hitung Ongkir
        $settingModel = new \App\Models\SettingModel();
        if (in_array($vehicleType, ['mobil', 'car'])) {
            $baseFareSetting = $settingModel->where('setting_key', 'food_car_base_fare')->first();
            $perKmFareSetting = $settingModel->where('setting_key', 'food_car_per_km_fare')->first();
            $baseDistanceSetting = $settingModel->where('setting_key', 'food_car_base_fare_distance')->first();

            $baseFare = $baseFareSetting ? (float)$baseFareSetting['setting_value'] : 12000;
            $perKmFare = $perKmFareSetting ? (float)$perKmFareSetting['setting_value'] : 4000;
            $baseDistance = $baseDistanceSetting ? (float)$baseDistanceSetting['setting_value'] : 3.0;
        } else {
            $baseFareSetting = $settingModel->where('setting_key', 'food_base_fare')->first();
            $perKmFareSetting = $settingModel->where('setting_key', 'food_per_km_fare')->first();
            $baseDistanceSetting = $settingModel->where('setting_key', 'food_base_fare_distance')->first();

            $baseFare = $baseFareSetting ? (float)$baseFareSetting['setting_value'] : 5000;
            $perKmFare = $perKmFareSetting ? (float)$perKmFareSetting['setting_value'] : 2000;
            $baseDistance = $baseDistanceSetting ? (float)$baseDistanceSetting['setting_value'] : 3.0;
        }

        $distanceRaw = $this->request->getVar('distance') ?? 1.5;
        $distance = round((float)$distanceRaw, 1);

        if ($distance <= $baseDistance) {
            $deliveryFee = $baseFare;
        } else {
            $extraDistance = $distance - $baseDistance;
            $deliveryFee = $baseFare + round($extraDistance * $perKmFare, -2);
        }

        // Kupon Promo
        $promoCode = strtoupper(trim($this->request->getVar('promo_code') ?? ''));
        $discountAmount = 0;
        if (!empty($promoCode)) {
            if ($promoCode === 'MAIMARTHEMAT') {
                $discountAmount = min(round($foodTotal * 0.2), 10000);
            } elseif ($promoCode === 'GRATISONGKIR') {
                $discountAmount = min($deliveryFee, 8000);
            }
        }

        $totalPrice = max(0, ($foodTotal + $deliveryFee) - $discountAmount);
        $paymentMethod = $this->request->getVar('payment_method') ?? 'cash';

        // Validasi Saldo Mai-Pay
        if ($paymentMethod === 'maipay') {
            if (!$user || $user->balance < $totalPrice) {
                return $this->respond(['status' => 400, 'message' => 'Saldo Mai-Pay tidak mencukupi'], 400);
            }
        }

        $orderModel = new FoodOrderModel();
        $orderData = [
            'user_id' => $payload['uid'],
            'merchant_id' => $merchantId,
            'order_type' => 'mart',
            'delivery_address' => $this->request->getVar('delivery_address'),
            'delivery_lat' => $this->request->getVar('delivery_lat'),
            'delivery_lng' => $this->request->getVar('delivery_lng'),
            'distance' => $distance,
            'food_total' => $foodTotal,
            'delivery_fee' => $deliveryFee,
            'discount_amount' => $discountAmount,
            'promo_code' => $promoCode,
            'total_price' => $totalPrice,
            'payment_method' => $paymentMethod,
            'vehicle_type' => $vehicleType,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $orderId = $orderModel->insert($orderData);
        if (!$orderId) {
            return $this->respond(['status' => 500, 'message' => 'Gagal membuat pesanan MaiMart'], 500);
        }

        // Insert Item Belanjaan
        $orderItemModel = new FoodOrderItemModel();
        foreach ($validItems as $item) {
            $orderItemModel->insert([
                'food_order_id' => $orderId,
                'menu_id' => $item['menu_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'notes' => $item['notes'] ?? null
            ]);
        }

        // Potong saldo jika bayar via Mai-Pay
        if ($paymentMethod === 'maipay') {
            $newBalance = $user->balance - $totalPrice;
            $db->table('users')->where('id', $payload['uid'])->update(['balance' => $newBalance]);

            $db->table('transactions')->insert([
                'user_id' => $payload['uid'],
                'amount' => $totalPrice,
                'type' => 'debit',
                'description' => "Pembayaran Belanja MaiMart #ORD-MART-$orderId",
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Kirim Notifikasi FCM ke Toko
        try {
            $fcm = new \App\Libraries\FcmService();
            if (!empty($merchant->fcm_token)) {
                $fcm->sendToToken(
                    $merchant->fcm_token,
                    '🛍️ Pesanan MaiMart Baru Masuk!',
                    "Ada pesanan belanja baru #ORD-MART-$orderId dengan total Rp " . number_format($totalPrice, 0, ',', '.'),
                    [
                        'type' => 'new_mart_order',
                        'order_id' => (string)$orderId,
                        'order_type' => 'mart',
                        'merchant_name' => $merchant->name
                    ]
                );
            }
        } catch (\Throwable $e) {
            log_message('error', 'FCM Mart error: ' . $e->getMessage());
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Pesanan MaiMart berhasil dibuat',
            'data' => [
                'order_id' => $orderId,
                'total_price' => $totalPrice,
                'payment_method' => $paymentMethod
            ]
        ]);
    }
}
