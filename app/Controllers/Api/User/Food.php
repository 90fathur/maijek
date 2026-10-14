<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MerchantModel;
use App\Models\MerchantMenuModel;
use App\Models\FoodOrderModel;
use App\Models\FoodOrderItemModel;

class Food extends ResourceController
{
    protected $format = 'json';

    public function merchants()
    {
        $menuModel = new \App\Models\MerchantMenuModel();

        $lat = $this->request->getVar('lat');
        $lng = $this->request->getVar('lng');
        $q = trim($this->request->getVar('q') ?? '');

        $db = \Config\Database::connect();
        $builder = $db->table('merchants')->where('is_open', 1);

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
            $builder->select('merchants.*');
        }

        $merchants = $builder->get()->getResultArray();

        foreach ($merchants as &$m) {
            $photoPath = !empty($m['photo']) ? $m['photo'] : (!empty($m['business_image']) ? $m['business_image'] : '');
            if (!empty($photoPath) && strpos($photoPath, 'http') !== 0) {
                $m['photo'] = base_url($photoPath);
            }
            unset($m['password']);
            unset($m['fcm_token']);

            // Ambil 1 menu andalan
            $topMenu = $menuModel->where('merchant_id', $m['id'])->where('is_available', 1)->first();
            $m['top_menu'] = $topMenu ? $topMenu['name'] : 'Menu Lengkap';
        }

        return $this->respond(['status' => 200, 'message' => 'Daftar Restoran', 'data' => $merchants]);
    }

    // Endpoint Pencarian Makanan & Resto Cerdas
    public function search()
    {
        $q = trim($this->request->getVar('q') ?? '');
        $category = trim($this->request->getVar('category') ?? '');
        $lat = $this->request->getVar('lat');
        $lng = $this->request->getVar('lng');

        $db = \Config\Database::connect();

        // 1. Cari Menu Makanan yang cocok
        $menuBuilder = $db->table('merchant_menus')
            ->select('merchant_menus.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.photo as merchant_photo, merchants.latitude, merchants.longitude, merchants.is_open')
            ->join('merchants', 'merchants.id = merchant_menus.merchant_id')
            ->where('merchant_menus.is_available', 1)
            ->where('merchants.is_open', 1);

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

        if (!empty($category) && strtolower($category) != 'semua') {
            if (in_array(strtolower($category), ['menu baru', 'baru', 'menu_baru', 'new'])) {
                // Tampilkan menu baru yang diunggah
                $menuBuilder->orderBy('merchant_menus.id', 'DESC');
            } else {
                $menuBuilder->groupStart()
                    ->like('merchant_menus.category', $category)
                    ->orLike('merchant_menus.name', $category)
                    ->groupEnd();
            }
        }

        if (in_array(strtolower($category), ['menu baru', 'baru', 'menu_baru', 'new'])) {
            $menuBuilder->orderBy('merchant_menus.id', 'DESC');
        } elseif ($lat && $lng) {
            $menuBuilder->orderBy('distance', 'ASC');
        } else {
            $menuBuilder->orderBy('merchant_menus.id', 'DESC');
        }

        $menus = $menuBuilder->limit(40)->get()->getResultArray();

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
        }

        // 2. Cari Restoran yang cocok
        $merchantBuilder = $db->table('merchants')->where('is_open', 1);

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

        if ($lat && $lng) {
            $merchantBuilder->orderBy('distance', 'ASC');
        } else {
            $merchantBuilder->orderBy('id', 'DESC');
        }

        $merchants = $merchantBuilder->limit(20)->get()->getResultArray();
        $menuModel = new \App\Models\MerchantMenuModel();

        foreach ($merchants as &$m) {
            $photoPath = !empty($m['photo']) ? $m['photo'] : (!empty($m['business_image']) ? $m['business_image'] : '');
            if (!empty($photoPath) && strpos($photoPath, 'http') !== 0) {
                $m['photo'] = base_url($photoPath);
            }
            unset($m['password']);
            unset($m['fcm_token']);

            // Top menu
            $topMenu = $menuModel->where('merchant_id', $m['id'])->where('is_available', 1)->first();
            $m['top_menu'] = $topMenu ? $topMenu['name'] : 'Menu Lengkap';
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Hasil Pencarian',
            'data' => [
                'menus' => $menus,
                'merchants' => $merchants
            ]
        ]);
    }

    // Endpoint Menu Baru yang Baru Diunggah oleh Mitra Restoran
    public function latestMenus()
    {
        $limit = (int)($this->request->getVar('limit') ?? 20);
        if ($limit <= 0 || $limit > 50) $limit = 20;

        $db = \Config\Database::connect();
        $menus = $db->table('merchant_menus')
            ->select('merchant_menus.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.photo as merchant_photo, merchants.latitude, merchants.longitude, merchants.is_open')
            ->join('merchants', 'merchants.id = merchant_menus.merchant_id')
            ->where('merchant_menus.is_available', 1)
            ->where('merchants.is_open', 1)
            ->orderBy('merchant_menus.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

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
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Menu Baru Terupload',
            'data' => $menus
        ]);
    }

    // Mendapatkan detail restoran dan menunya
    public function merchantDetail($id = null)
    {
        $merchantModel = new MerchantModel();
        $menuModel = new MerchantMenuModel();

        $merchant = $merchantModel->find($id);
        if (!$merchant) return $this->respond(['status' => 404, 'message' => 'Restoran tidak ditemukan'], 404);

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
        }

        // Helper teks hari operasional (Mendukung Mode Harian Fleksibel / Pilihan A & Mode Reguler)
        $dayNames = [
            '1' => 'Senin',
            '2' => 'Selasa',
            '3' => 'Rabu',
            '4' => 'Kamis',
            '5' => 'Jumat',
            '6' => 'Sabtu',
            '7' => 'Minggu',
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

                $activeCount = 0;
                foreach ($daysMap as $d) {
                    if (!empty($d['is_open'])) $activeCount++;
                }

                if ($todayCfg) {
                    if (empty($todayCfg['is_open'])) {
                        $isOperatingToday = false;
                        $opDaysText = 'Hari ini Libur (' . ($todayCfg['name'] ?? 'Hari Ini') . ')';
                    } else {
                        $isOperatingToday = true;
                        $todayOpen = $todayCfg['open'] ?? '08:00';
                        $todayClose = $todayCfg['close'] ?? '21:00';
                        $opDaysText = 'Hari ini: ' . $todayOpen . ' - ' . $todayClose;
                        $merchant['opening_time'] = $todayOpen;
                        $merchant['closing_time'] = $todayClose;
                    }
                } else {
                    $opDaysText = "Jadwal Fleksibel ($activeCount Hari Aktif)";
                }
                $merchant['schedule_mode'] = 'daily';
                $merchant['daily_schedule'] = $daysMap;
            }
        } else {
            $daysArr = !empty($rawOpDays) ? explode(',', $rawOpDays) : ['1', '2', '3', '4', '5', '6', '7'];
            if (count($daysArr) >= 7) {
                $opDaysText = 'Setiap Hari (Senin - Minggu)';
            } elseif ($rawOpDays === '1,2,3,4,5,6') {
                $opDaysText = 'Senin - Sabtu';
            } elseif ($rawOpDays === '1,2,3,4,5') {
                $opDaysText = 'Senin - Jumat';
            } else {
                $names = [];
                foreach ($daysArr as $d) {
                    if (isset($dayNames[$d])) $names[] = $dayNames[$d];
                }
                $opDaysText = !empty($names) ? implode(', ', $names) : 'Setiap Hari';
            }
            $isOperatingToday = in_array($today, $daysArr);
            $merchant['schedule_mode'] = 'same';
        }

        $merchant['operating_days_text'] = $opDaysText;
        $merchant['is_operating_today'] = $isOperatingToday;

        $merchant['menus'] = $menus;

        return $this->respond(['status' => 200, 'message' => 'Detail Restoran', 'data' => $merchant]);
    }

    // Membuat order makanan baru
    public function order()
    {
        $payload = $_SERVER['jwt_payload'];
        $rules = [
            'merchant_id' => 'required',
            'delivery_address' => 'required',
            'delivery_lat' => 'required',
            'delivery_lng' => 'required',
            'items' => 'required' // JSON string array of items: [{menu_id: 1, quantity: 2, price: 15000}, ...]
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Validasi gagal'], 400);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
        if ($user && isset($user->status) && $user->status === 'suspended') {
            return $this->respond(['status' => 403, 'message' => 'Akun Anda sedang disuspend oleh Admin karena pelanggaran kebijakan.'], 403);
        }

        $items = json_decode($this->request->getVar('items'), true);
        if (empty($items)) {
            return $this->respond(['status' => 400, 'message' => 'Keranjang kosong'], 400);
        }

        // Anti-Cheat: Kalkulasi harga makanan langsung dari Database, BUKAN dari request
        $foodTotal = 0;
        $db = \Config\Database::connect();
        $merchantMenuModel = new \App\Models\MerchantMenuModel();

        $merchantId = $this->request->getVar('merchant_id');

        // Cek status merchant (apakah tutup?)
        $merchant = $db->table('merchants')->where('id', $merchantId)->get()->getRow();
        if (!$merchant) {
            return $this->respond(['status' => 404, 'message' => 'Restoran tidak ditemukan'], 404);
        }
        if ($merchant->is_open == 0) {
            return $this->respond(['status' => 400, 'message' => 'Restoran sedang tutup'], 400);
        }

        // Cek Hari & Jam Operasional Toko (Mendukung Pilihan A: Mode Harian Fleksibel & Mode Reguler)
        $today = (string)date('N'); // 1 (Senin) s/d 7 (Minggu)
        $now = date('H:i:s');
        $isStoreOpenToday = true;
        $effectiveOpen = $merchant->opening_time ?? null;
        $effectiveClose = $merchant->closing_time ?? null;

        if (!empty($merchant->operating_days)) {
            $rawDays = trim($merchant->operating_days);
            if (strpos($rawDays, '{') === 0) {
                $decoded = json_decode($rawDays, true);
                if (isset($decoded['mode']) && $decoded['mode'] === 'daily' && isset($decoded['days'][$today])) {
                    $todayCfg = $decoded['days'][$today];
                    if (empty($todayCfg['is_open'])) {
                        $isStoreOpenToday = false;
                    } else {
                        if (!empty($todayCfg['open'])) $effectiveOpen = $todayCfg['open'];
                        if (!empty($todayCfg['close'])) $effectiveClose = $todayCfg['close'];
                    }
                }
            } else {
                $allowedDays = array_map('trim', explode(',', $rawDays));
                if (!in_array($today, $allowedDays)) {
                    $isStoreOpenToday = false;
                }
            }
        }

        if (!$isStoreOpenToday) {
            return $this->respond(['status' => 400, 'message' => 'Mohon maaf, restoran libur hari ini sesuai jadwal operasional.'], 400);
        }

        // Cek Jam Operasional Toko
        if (!empty($effectiveOpen) && !empty($effectiveClose)) {
            $isOpenNow = false;
            if ($effectiveOpen <= $effectiveClose) {
                $isOpenNow = ($now >= $effectiveOpen && $now <= $effectiveClose);
            } else {
                $isOpenNow = ($now >= $effectiveOpen || $now <= $effectiveClose);
            }
            if (!$isOpenNow) {
                $openDisplay = substr($effectiveOpen, 0, 5);
                $closeDisplay = substr($effectiveClose, 0, 5);
                return $this->respond(['status' => 400, 'message' => "Mohon maaf, restoran saat ini di luar jam operasional (Buka pukul $openDisplay - $closeDisplay)."], 400);
            }
        }

        $validItems = [];
        foreach ($items as $item) {
            $menu = $merchantMenuModel->find($item['menu_id']);

            if (!$menu || $menu['merchant_id'] != $merchantId) {
                return $this->respond(['status' => 400, 'message' => 'Item menu tidak valid'], 400);
            }
            if ($menu['is_available'] == 0) {
                return $this->respond(['status' => 400, 'message' => "Mohon maaf, menu '{$menu['name']}' sedang habis"], 400);
            }

            // Gunakan harga diskon jika sedang ada promo harga coret
            $realPrice = (!empty($menu['discount_price']) && (float)$menu['discount_price'] > 0 && (float)$menu['discount_price'] < (float)$menu['price'])
                ? (float)$menu['discount_price']
                : (float)$menu['price'];

            $foodTotal += ($realPrice * $item['quantity']);

            // Simpan harga asli untuk dimasukkan ke FoodOrderItem
            $item['price'] = $realPrice;
            $validItems[] = $item;
        }
        $items = $validItems;

        $vehicleType = strtolower($this->request->getVar('vehicle_type') ?? 'motor');
        if (!in_array($vehicleType, ['motor', 'mobil', 'car'])) {
            $vehicleType = 'motor';
        }

        // Mengambil pengaturan tarif ongkos kirim Mai-Food
        $settingModel = new \App\Models\SettingModel();
        if (in_array($vehicleType, ['mobil', 'car'])) {
            $baseFareSetting = $settingModel->where('setting_key', 'food_car_base_fare')->first();
            $perKmFareSetting = $settingModel->where('setting_key', 'food_car_per_km_fare')->first();
            $baseDistanceSetting = $settingModel->where('setting_key', 'food_car_base_fare_distance')->first();

            $foodBaseFare = $baseFareSetting ? (float)$baseFareSetting['setting_value'] : 12000;
            $foodPerKmFare = $perKmFareSetting ? (float)$perKmFareSetting['setting_value'] : 4000;
            $baseDistance = $baseDistanceSetting ? (float)$baseDistanceSetting['setting_value'] : 3.0;
        } else {
            $baseFareSetting = $settingModel->where('setting_key', 'food_base_fare')->first();
            $perKmFareSetting = $settingModel->where('setting_key', 'food_per_km_fare')->first();
            $baseDistanceSetting = $settingModel->where('setting_key', 'food_base_fare_distance')->first();

            $foodBaseFare = $baseFareSetting ? (float)$baseFareSetting['setting_value'] : 5000;
            $foodPerKmFare = $perKmFareSetting ? (float)$perKmFareSetting['setting_value'] : 2000;
            $baseDistance = $baseDistanceSetting ? (float)$baseDistanceSetting['setting_value'] : 3.0;
        }

        $distanceRaw = $this->request->getVar('distance') ?? 1.5;
        $distance = round((float)$distanceRaw, 1);

        if ($distance <= $baseDistance) {
            $deliveryFee = $foodBaseFare;
        } else {
            $extraDistance = $distance - $baseDistance;
            $deliveryFee = $foodBaseFare + round($extraDistance * $foodPerKmFare, -2); // Dibulatkan ke kelipatan ratusan terdekat
        }

        $orderModel = new FoodOrderModel();

        // 🎟️ PROMO & DISKON PINTAR (Pengguna Baru, Kode Kupon, Gratis Ongkir)
        $promoCode = strtoupper(trim($this->request->getVar('promo_code') ?? ''));
        $discountAmount = 0;

        if (!empty($promoCode)) {
            if ($promoCode === 'MAIPERTAMA') {
                // Verifikasi apakah benar pesanan pertama pengguna di Mai-Food
                $pastOrders = $orderModel->where('user_id', $payload['uid'])->countAllResults();
                if ($pastOrders === 0) {
                    // Diskon 50% hingga maksimal Rp 15.000
                    $discountAmount = min(round($foodTotal * 0.5), 15000);
                }
            } elseif ($promoCode === 'GRATISONGKIR') {
                // Potongan ongkos kirim hingga Rp 8.000
                $discountAmount = min($deliveryFee, 8000);
            } elseif ($promoCode === 'HEMAT30') {
                // Diskon 30% hingga maksimal Rp 12.000
                $discountAmount = min(round($foodTotal * 0.3), 12000);
            } else {
                $promoModel = new \App\Models\PromoModel();
                $promo = $promoModel->where('code', $promoCode)->where('status', 'active')->first();
                if ($promo) {
                    if ($promo['discount_type'] === 'percent') {
                        $discountAmount = round($foodTotal * ($promo['discount_amount'] / 100));
                    } else {
                        $discountAmount = (float)$promo['discount_amount'];
                    }
                }
            }
        }

        $totalPrice = max(0, ($foodTotal + $deliveryFee) - $discountAmount);
        $paymentMethod = $this->request->getVar('payment_method') ?? 'cash';

        // Validasi Saldo Mai-Pay (Penuh atau Split Ongkir)
        $db = \Config\Database::connect();
        if ($paymentMethod == 'maipay') {
            $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
            if (!$user || $user->balance < $totalPrice) {
                return $this->respond(['status' => 400, 'message' => 'Saldo Mai-Pay tidak mencukupi. Sisa saldo Anda: Rp ' . number_format($user ? $user->balance : 0, 0, ',', '.')], 400);
            }
        } else if ($paymentMethod == 'split_delivery_maipay') {
            $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
            if (!$user || $user->balance < $deliveryFee) {
                return $this->respond(['status' => 400, 'message' => 'Saldo Mai-Pay tidak mencukupi untuk bayar ongkir (Rp ' . number_format($deliveryFee, 0, ',', '.') . '). Sisa saldo Anda: Rp ' . number_format($user ? $user->balance : 0, 0, ',', '.')], 400);
            }
        }

        // Pastikan kolom discount_amount, promo_code, dan estimated_time ada secara otomatis di database
        try {
            if (!$db->fieldExists('discount_amount', 'food_orders')) {
                $db->query("ALTER TABLE food_orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER delivery_fee");
            }
            if (!$db->fieldExists('promo_code', 'food_orders')) {
                $db->query("ALTER TABLE food_orders ADD COLUMN promo_code VARCHAR(50) NULL AFTER discount_amount");
            }
            if (!$db->fieldExists('estimated_time', 'food_orders')) {
                $db->query("ALTER TABLE food_orders ADD COLUMN estimated_time VARCHAR(100) NULL AFTER distance");
            }
        } catch (\Throwable $e) {
        }

        // Kalkulasi Waktu Estimasi Cerdas Mai-Food
        $estTrip = in_array($vehicleType, ['mobil', 'car']) ? round($distance * 3.0 + 5) : round($distance * 2.4 + 3);
        if ($estTrip < 4) $estTrip = 4;
        $estPrep = 15;
        $estTotal = $estPrep + $estTrip;
        $estArrival = date('H:i', strtotime("+$estTotal minutes"));
        $estRange = max(3, $estTotal - 3) . '-' . ($estTotal + 5) . ' mnt';
        $estTimeText = "$estRange (Tiba ~$estArrival)";

        $orderData = [
            'user_id' => $payload['uid'],
            'merchant_id' => $this->request->getVar('merchant_id'),
            'delivery_address' => $this->request->getVar('delivery_address'),
            'delivery_lat' => $this->request->getVar('delivery_lat'),
            'delivery_lng' => $this->request->getVar('delivery_lng'),
            'distance' => $distance,
            'estimated_time' => $estTimeText,
            'food_total' => $foodTotal,
            'delivery_fee' => $deliveryFee,
            'discount_amount' => $discountAmount,
            'promo_code' => !empty($promoCode) ? $promoCode : null,
            'total_price' => $totalPrice,
            'payment_method' => $paymentMethod,
            'vehicle_type' => in_array($vehicleType, ['mobil', 'car']) ? 'mobil' : 'motor',
            'status' => 'pending'
        ];

        $orderId = $orderModel->insert($orderData);

        // POTONG SALDO DI AWAL (Sistem Escrow)
        if ($paymentMethod == 'maipay') {
            $db->table('users')->where('id', $payload['uid'])->set('balance', 'balance - ' . $totalPrice, false)->update();

            if ($db->tableExists('transactions')) {
                $db->table('transactions')->insert([
                    'user_id'     => $payload['uid'],
                    'type'        => 'debit',
                    'amount'      => (int)round($totalPrice),
                    'description' => "Pembayaran Pesanan MaiFood #{$orderId}",
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        } else if ($paymentMethod == 'split_delivery_maipay') {
            $db->table('users')->where('id', $payload['uid'])->set('balance', 'balance - ' . $deliveryFee, false)->update();

            if ($db->tableExists('transactions')) {
                $db->table('transactions')->insert([
                    'user_id'     => $payload['uid'],
                    'type'        => 'debit',
                    'amount'      => (int)round($deliveryFee),
                    'description' => "Pembayaran Ongkir Split MaiFood #{$orderId}",
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Masukkan items
        $itemModel = new FoodOrderItemModel();
        foreach ($items as $item) {
            $itemModel->insert([
                'food_order_id' => $orderId,
                'menu_id' => $item['menu_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'notes' => $item['notes'] ?? null
            ]);
        }

        // Kirim Notifikasi FCM ke Merchant bahwa ada pesanan baru!
        if ($merchant && !empty($merchant->fcm_token)) {
            try {
                $fcmService = new \App\Libraries\FcmService();
                $fcmService->sendPushNotification(
                    $merchant->fcm_token,
                    "Pesanan Makanan Baru!",
                    "Ada pesanan MaiFood baru masuk, segera periksa!",
                    ['type' => 'new_food_order', 'order_id' => (string)$orderId]
                );
            } catch (\Throwable $e) {
                log_message('error', 'FCM Food order notification error: ' . $e->getMessage());
            }
        }

        return $this->respond([
            'status' => 201,
            'message' => 'Pesanan berhasil dibuat',
            'data' => [
                'order_id' => $orderId,
                'estimated_time' => $estTimeText,
                'estimated_minutes' => $estTotal,
                'estimated_arrival' => $estArrival,
                'prep_minutes' => $estPrep,
                'trip_minutes' => $estTrip
            ]
        ]);
    }

    // Mendapatkan pesanan makanan yang sedang aktif
    public function active()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new FoodOrderModel();

        $builder = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.phone as merchant_phone, merchants.address as merchant_address, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, merchants.photo as merchant_photo, drivers.name as driver_name, drivers.vehicle_plate, drivers.latitude as driver_lat, drivers.longitude as driver_lng, drivers.photo as driver_photo, drivers.phone as driver_phone, drivers.qris_image as driver_qris_image, drivers.qris_merchant_name as driver_qris_merchant_name, drivers.qris_is_active as driver_qris_is_active')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('user_id', $payload['uid'])
            ->whereIn('food_orders.status', ['pending', 'accepted_by_merchant', 'processing', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress'])
            ->orderBy('food_orders.created_at', 'DESC');

        if ($this->request->getGet('order_id')) {
            $builder->where('food_orders.id', $this->request->getGet('order_id'));
        }

        $order = $builder->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Tidak ada pesanan aktif', 'data' => null], 404);
        }

        if (!empty($order['merchant_photo']) && strpos($order['merchant_photo'], 'http') !== 0) {
            $order['merchant_photo'] = base_url($order['merchant_photo']);
        }
        if (!empty($order['driver_photo']) && strpos($order['driver_photo'], 'http') !== 0) {
            $order['driver_photo'] = base_url($order['driver_photo']);
        }
        if (!empty($order['driver_qris_image']) && strpos($order['driver_qris_image'], 'http') !== 0) {
            $order['driver_qris_image_url'] = base_url($order['driver_qris_image']);
        } else {
            $order['driver_qris_image_url'] = !empty($order['driver_qris_image']) ? $order['driver_qris_image'] : null;
        }

        // Ambil item pesanan
        $db = \Config\Database::connect();
        $order['items'] = $db->table('food_order_items')
            ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
            ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
            ->where('food_order_id', $order['id'])
            ->get()
            ->getResultArray();

        // Hitung unread chat
        $order['unread_count'] = 0;
        try {
            if ($db->tableExists('food_chats') && $db->fieldExists('is_read', 'food_chats')) {
                $order['unread_count'] = $db->table('food_chats')
                    ->where('food_order_id', $order['id'])
                    ->where('sender_type !=', 'user')
                    ->where('is_read', 0)
                    ->countAllResults();
            }
        } catch (\Throwable $e) {
        }

        // Hitung estimasi waktu tiba cerdas Mai-Food
        $dist = (float)($order['distance'] ?? 0);
        $vType = strtolower($order['vehicle_type'] ?? 'motor');
        $tripMin = in_array($vType, ['mobil', 'car']) ? round($dist * 3.0 + 5) : round($dist * 2.4 + 3);
        if ($tripMin < 4) $tripMin = 4;
        $prepMin = 15;

        $elapsed = 0;
        if (!empty($order['created_at'])) {
            $createdTs = strtotime($order['created_at']);
            $elapsed = max(0, round((time() - $createdTs) / 60));
        }
        $remainingPrep = max(3, $prepMin - $elapsed);
        $totalRemaining = $remainingPrep + $tripMin;

        $order['prep_estimated_minutes'] = $remainingPrep;
        $order['delivery_estimated_minutes'] = $tripMin;
        $order['estimated_minutes'] = $totalRemaining;
        $order['estimated_range'] = max(3, $totalRemaining - 3) . '-' . ($totalRemaining + 5) . ' mnt';
        $order['estimated_arrival_clock'] = date('H:i', strtotime("+$totalRemaining minutes"));
        if (empty($order['estimated_time'])) {
            $order['estimated_time'] = $order['estimated_range'] . ' (Tiba ~' . $order['estimated_arrival_clock'] . ')';
        }

        $this->enrichUserFoodPayment($order);

        return $this->respond([
            'status' => 200,
            'message' => 'Pesanan aktif ditemukan',
            'data' => $order
        ]);
    }

    // Mendapatkan detail pesanan makanan berdasarkan ID (aktif maupun riwayat)
    public function detail($id = null)
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new FoodOrderModel();

        $order = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.phone as merchant_phone, merchants.address as merchant_address, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, merchants.photo as merchant_photo, drivers.name as driver_name, drivers.vehicle_plate, drivers.latitude as driver_lat, drivers.longitude as driver_lng, drivers.photo as driver_photo, drivers.phone as driver_phone, drivers.qris_image as driver_qris_image, drivers.qris_merchant_name as driver_qris_merchant_name, drivers.qris_is_active as driver_qris_is_active')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('food_orders.id', $id)
            ->where('food_orders.user_id', $payload['uid'])
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan', 'data' => null], 404);
        }

        if (!empty($order['merchant_photo']) && strpos($order['merchant_photo'], 'http') !== 0) {
            $order['merchant_photo'] = base_url($order['merchant_photo']);
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
        $order['items'] = $db->table('food_order_items')
            ->select('food_order_items.*, merchant_menus.name as menu_name, merchant_menus.photo as menu_photo')
            ->join('merchant_menus', 'merchant_menus.id = food_order_items.menu_id', 'left')
            ->where('food_order_id', $order['id'])
            ->get()
            ->getResultArray();

        $order['unread_count'] = 0;
        try {
            if ($db->tableExists('food_chats') && $db->fieldExists('is_read', 'food_chats')) {
                $order['unread_count'] = $db->table('food_chats')
                    ->where('food_order_id', $order['id'])
                    ->where('sender_type !=', 'user')
                    ->where('is_read', 0)
                    ->countAllResults();
            }
        } catch (\Throwable $e) {
        }

        // Hitung estimasi waktu tiba cerdas Mai-Food
        $dist = (float)($order['distance'] ?? 0);
        $vType = strtolower($order['vehicle_type'] ?? 'motor');
        $tripMin = in_array($vType, ['mobil', 'car']) ? round($dist * 3.0 + 5) : round($dist * 2.4 + 3);
        if ($tripMin < 4) $tripMin = 4;
        $prepMin = 15;

        $elapsed = 0;
        if (!empty($order['created_at'])) {
            $createdTs = strtotime($order['created_at']);
            $elapsed = max(0, round((time() - $createdTs) / 60));
        }
        $remainingPrep = max(3, $prepMin - $elapsed);
        $totalRemaining = $remainingPrep + $tripMin;

        $order['prep_estimated_minutes'] = $remainingPrep;
        $order['delivery_estimated_minutes'] = $tripMin;
        $order['estimated_minutes'] = $totalRemaining;
        $order['estimated_range'] = max(3, $totalRemaining - 3) . '-' . ($totalRemaining + 5) . ' mnt';
        $order['estimated_arrival_clock'] = date('H:i', strtotime("+$totalRemaining minutes"));
        if (empty($order['estimated_time'])) {
            $order['estimated_time'] = $order['estimated_range'] . ' (Tiba ~' . $order['estimated_arrival_clock'] . ')';
        }

        $this->enrichUserFoodPayment($order);

        return $this->respond([
            'status' => 200,
            'message' => 'Detail pesanan ditemukan',
            'data' => $order
        ]);
    }

    // Mendapatkan daftar riwayat pesanan (aktif / history)
    public function history()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderModel = new FoodOrderModel();

        $orders = $orderModel->select('food_orders.*, merchants.name as merchant_name, merchants.phone as merchant_phone, merchants.photo as merchant_photo, merchants.latitude as merchant_lat, merchants.longitude as merchant_lng, drivers.name as driver_name, drivers.phone as driver_phone, drivers.latitude as driver_lat, drivers.longitude as driver_lng, drivers.vehicle_plate as driver_plate, drivers.photo as driver_photo')
            ->join('merchants', 'merchants.id = food_orders.merchant_id')
            ->join('drivers', 'drivers.id = food_orders.driver_id', 'left')
            ->where('user_id', $payload['uid'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($orders as &$ord) {
            $this->enrichUserFoodPayment($ord);
        }

        return $this->respond(['status' => 200, 'message' => 'Riwayat pesanan makanan', 'data' => $orders]);
    }

    public function rate()
    {
        $payload = $_SERVER['jwt_payload'];
        $orderId = $this->request->getVar('order_id');
        $merchantRating = $this->request->getVar('merchant_rating');
        $driverRating = $this->request->getVar('driver_rating');
        $reviewMerchant = $this->request->getVar('review_merchant');
        $reviewDriver = $this->request->getVar('review_driver');
        $tip = $this->request->getVar('tip');

        if (!$orderId) {
            return $this->respond(['status' => 400, 'message' => 'Order ID diperlukan'], 400);
        }

        $orderModel = new FoodOrderModel();
        $order = $orderModel->where('user_id', $payload['uid'])->find($orderId);

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($order['status'] != 'completed') {
            return $this->respond(['status' => 400, 'message' => 'Hanya pesanan selesai yang dapat diberi rating'], 400);
        }

        $updateData = [];
        if ($merchantRating) $updateData['merchant_rating'] = $merchantRating;
        if ($driverRating) $updateData['driver_rating'] = $driverRating;
        if ($reviewMerchant) $updateData['review_merchant'] = $reviewMerchant;
        if ($reviewDriver) $updateData['review_driver'] = $reviewDriver;

        if (!empty($updateData)) {
            $orderModel->update($orderId, $updateData);
        }

        // Tip Driver
        if ($tip > 0) {
            $db = \Config\Database::connect();
            $db->transStart();

            $user = $db->table('users')->where('id', $payload['uid'])->get()->getRow();
            if ($user && $user->balance >= $tip) {
                $db->table('users')->where('id', $payload['uid'])->set('balance', 'balance - ' . $tip, false)->update();
                $db->table('drivers')->where('id', $order['driver_id'])->set('balance', 'balance + ' . $tip, false)->update();
            }

            $db->transComplete();
        }

        return $this->respond(['status' => 200, 'message' => 'Terima kasih atas ulasan Anda!']);
    }

    public function cancel()
    {
        $payload = $_SERVER['jwt_payload'];
        $rawInput = file_get_contents('php://input');
        $json = json_decode($rawInput);

        $orderId = $json->order_id ?? $this->request->getVar('order_id');
        $reason = $json->reason ?? $this->request->getVar('reason') ?? 'Dibatalkan oleh pelanggan';

        $orderModel = new FoodOrderModel();
        $order = $orderModel->where('user_id', $payload['uid'])->where('id', $orderId)->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan makanan tidak ditemukan'], 404);
        }

        if ($order['status'] === 'cancelled') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan ini sudah dibatalkan sebelumnya.'], 400);
        }

        if ($order['status'] === 'completed' || $order['status'] === 'delivered') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan sudah selesai dan tidak dapat dibatalkan.'], 400);
        }

        if ($order['status'] === 'delivering' || $order['status'] === 'in_progress') {
            return $this->respond(['status' => 400, 'message' => 'Pesanan sedang diantar oleh kurir ke lokasi Anda dan tidak dapat dibatalkan.'], 400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $orderModel->update($orderId, [
            'status' => 'cancelled',
            'cancel_reason' => $reason
        ]);

        // REFUND SALDO MAI-PAY 100% SECARA ATOMIK
        if ($order['payment_method'] === 'maipay' && (float)$order['total_price'] > 0) {
            $db->table('users')
                ->where('id', $payload['uid'])
                ->set('balance', 'balance + ' . (float)$order['total_price'], false)
                ->update();

            if ($db->tableExists('transactions')) {
                $db->table('transactions')->insert([
                    'user_id'     => $payload['uid'],
                    'type'        => 'credit',
                    'amount'      => (int)round($order['total_price']),
                    'description' => "Refund Pembatalan Pesanan MaiFood #{$orderId}",
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        } else if ($order['payment_method'] === 'split_delivery_maipay' && (float)$order['delivery_fee'] > 0) {
            $db->table('users')
                ->where('id', $payload['uid'])
                ->set('balance', 'balance + ' . (float)$order['delivery_fee'], false)
                ->update();

            if ($db->tableExists('transactions')) {
                $db->table('transactions')->insert([
                    'user_id'     => $payload['uid'],
                    'type'        => 'credit',
                    'amount'      => (int)round($order['delivery_fee']),
                    'description' => "Refund Ongkir Split MaiFood #{$orderId}",
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(['status' => 500, 'message' => 'Gagal membatalkan pesanan sistem'], 500);
        }

        // Kirim Notifikasi FCM
        $fcmService = new \App\Libraries\FcmService();

        // 1. Notif ke Restoran
        $merchant = $db->table('merchants')->where('id', $order['merchant_id'])->get()->getRow();
        if ($merchant && !empty($merchant->fcm_token)) {
            $fcmService->sendPushNotification(
                $merchant->fcm_token,
                'Pesanan Dibatalkan Pelanggan',
                'Pesanan #' . $orderId . ' telah dibatalkan oleh pelanggan. Alasan: ' . $reason,
                ['type' => 'food_order_cancelled', 'order_id' => (string)$orderId]
            );
        }

        // 2. Notif ke Driver jika sudah ada driver yang ditugaskan
        if (!empty($order['driver_id'])) {
            $driver = $db->table('drivers')->where('id', $order['driver_id'])->get()->getRow();
            if ($driver && !empty($driver->fcm_token)) {
                $fcmService->sendPushNotification(
                    $driver->fcm_token,
                    'Pesanan Makanan Dibatalkan',
                    'Pelanggan telah membatalkan pesanan #' . $orderId . '.',
                    ['type' => 'food_order_cancelled', 'order_id' => (string)$orderId]
                );
            }
        }

        $refundText = '.';
        if ($order['payment_method'] === 'maipay') {
            $refundText = ' dan saldo Mai-Pay telah dikembalikan 100%.';
        } else if ($order['payment_method'] === 'split_delivery_maipay') {
            $refundText = ' dan saldo ongkir Mai-Pay telah dikembalikan.';
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Pesanan berhasil dibatalkan' . $refundText
        ], 200);
    }

    /**
     * Memperkaya order dengan rincian pembayaran split/maipay/cash untuk user
     */
    private function enrichUserFoodPayment(array &$order)
    {
        $foodTotal = (float)($order['food_total'] ?? 0);
        $deliveryFee = (float)($order['delivery_fee'] ?? 0);
        $discountAmount = (float)($order['discount_amount'] ?? 0);
        $totalPrice = (float)($order['total_price'] ?? max(0, $foodTotal + $deliveryFee - $discountAmount));

        if (($order['payment_method'] ?? 'cash') === 'split_delivery_maipay') {
            $cashToDriver = max(0, $foodTotal - $discountAmount);
            $order['is_split_payment'] = true;
            $order['payment_label'] = 'Split (Ongkir MaiPay + Makanan Tunai)';
            $order['pay_digital'] = $deliveryFee;
            $order['pay_cash_to_driver'] = $cashToDriver;
            $order['payment_instruction'] = 'Ongkir Rp ' . number_format($deliveryFee, 0, ',', '.') . ' sudah lunas via MaiPay. Siapkan uang tunai pas Rp ' . number_format($cashToDriver, 0, ',', '.') . ' untuk driver saat pesanan tiba.';
        } else if (($order['payment_method'] ?? 'cash') === 'maipay') {
            $order['is_split_payment'] = false;
            $order['payment_label'] = 'MaiPay (Saldo Penuh)';
            $order['pay_digital'] = $totalPrice;
            $order['pay_cash_to_driver'] = 0.0;
            $order['payment_instruction'] = 'Seluruh pesanan sudah lunas via MaiPay. Anda tidak perlu membayar apapun ke driver.';
        } else {
            $order['is_split_payment'] = false;
            $order['payment_label'] = 'Tunai (Cash)';
            $order['pay_digital'] = 0.0;
            $order['pay_cash_to_driver'] = $totalPrice;
            $order['payment_instruction'] = 'Siapkan uang tunai Rp ' . number_format($totalPrice, 0, ',', '.') . ' untuk dibayarkan ke driver saat pesanan tiba.';
        }
    }
}

