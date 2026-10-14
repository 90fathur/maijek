<?php

namespace App\Controllers\Api\User;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BannerModel;
use App\Models\PromoModel;

class Home extends ResourceController
{
    protected $format = 'json';

    public function banners()
    {
        $bannerModel = new BannerModel();
        // Ambil banner aktif langsung dari tabel banners (dikelola via Admin CMS /admin/banners)
        $banners = $bannerModel->where('status', 'active')->orderBy('created_at', 'DESC')->findAll();

        // Format URL gambar banner jika ada
        foreach ($banners as &$banner) {
            if (!empty($banner['image_path'])) {
                if (strpos($banner['image_path'], 'http') !== 0) {
                    $banner['image_path'] = base_url('uploads/banners/' . $banner['image_path']);
                }
            }
        }

        return $this->respond([
            'status' => 'success',
            'data' => $banners
        ]);
    }

    public function promos()
    {
        $promoModel = new PromoModel();
        // Ambil kupon promo aktif langsung dari tabel promos (dikelola via Admin CMS /admin/promos)
        $promos = $promoModel->where('status', 'active')->orderBy('created_at', 'DESC')->findAll();

        foreach ($promos as &$p) {
            $p['id'] = (int)$p['id'];
            $p['discount_amount'] = (float)$p['discount_amount'];
            if (empty($p['title'])) {
                $p['title'] = 'Kupon Promo ' . $p['code'];
            }
            if (empty($p['description'])) {
                $p['description'] = ($p['discount_type'] == 'percent')
                    ? 'Diskon ' . (int)$p['discount_amount'] . '% untuk pesanan kamu.'
                    : 'Potongan harga Rp ' . number_format($p['discount_amount'], 0, ',', '.') . ' untuk pesanan kamu.';
            }
            if (empty($p['badge'])) {
                $p['badge'] = ($p['discount_type'] == 'percent')
                    ? 'DISKON ' . (int)$p['discount_amount'] . '%'
                    : 'HEMAT RP ' . (int)$p['discount_amount'];
            }
        }

        return $this->respond([
            'status' => 'success',
            'data' => $promos
        ]);
    }

    // Endpoint Menu Diskon Spesial (HANYA menampilkan menu yang memang di-diskon oleh Restoran/Mart)
    public function flashSaleMenus()
    {
        $db = \Config\Database::connect();

        try {
            // Auto check kolom discount_price jika belum ada
            if (!$db->fieldExists('discount_price', 'merchant_menus')) {
                $db->query("ALTER TABLE merchant_menus ADD COLUMN discount_price DECIMAL(10,2) NULL AFTER price");
            }

            // HANYA query menu yang benar-benar memiliki harga diskon aktif dari merchant
            $builder = $db->table('merchant_menus')
                ->select('merchant_menus.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.is_open')
                ->join('merchants', 'merchants.id = merchant_menus.merchant_id')
                ->where('merchant_menus.is_available', 1)
                ->where('merchant_menus.discount_price IS NOT NULL')
                ->where('merchant_menus.discount_price >', 0)
                ->where('merchant_menus.discount_price < merchant_menus.price');

            if ($db->fieldExists('merchant_type', 'merchants')) {
                $builder->select('merchants.merchant_type');
            }

            $menus = $builder->orderBy('merchant_menus.id', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            foreach ($menus as &$m) {
                $orig = (float)$m['price'];
                $disc = (float)$m['discount_price'];
                $m['discount_percent'] = max(1, round((($orig - $disc) / max(1, $orig)) * 100));

                if (!empty($m['photo']) && strpos($m['photo'], 'http') !== 0) {
                    $m['photo'] = base_url($m['photo']);
                }
                $m['merchant_type'] = !empty($m['merchant_type']) ? $m['merchant_type'] : 'food';
                $m['unit'] = !empty($m['unit']) ? $m['unit'] : 'pcs';
            }

            return $this->respond([
                'status' => 'success',
                'data' => $menus
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => 'success',
                'data' => []
            ]);
        }
    }

    // Endpoint Menu & Produk Paling Laris (Best Seller)
    // Mendukung filter ?type=food atau ?type=mart
    public function bestSellerMenus()
    {
        $db = \Config\Database::connect();

        try {
            $type = trim($this->request->getVar('type') ?? '');

            // Query menu / produk terbaik berdasarkan total penjualan (completed orders)
            $builder = $db->table('merchant_menus')
                ->select('merchant_menus.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.is_open, COALESCE(SUM(food_order_items.quantity), 0) as total_sold')
                ->join('merchants', 'merchants.id = merchant_menus.merchant_id')
                ->join('food_order_items', 'food_order_items.menu_id = merchant_menus.id', 'left')
                ->join('food_orders', 'food_orders.id = food_order_items.food_order_id AND food_orders.status = "completed"', 'left')
                ->where('merchant_menus.is_available', 1);

            $hasTypeCol = $db->fieldExists('merchant_type', 'merchants');
            if ($hasTypeCol) {
                $builder->select('merchants.merchant_type, merchants.category as merchant_category');
                if ($type === 'food') {
                    $builder->groupStart()
                        ->whereIn('merchants.merchant_type', ['food', 'all'])
                        ->orWhere('merchants.merchant_type IS NULL')
                        ->groupEnd();
                } elseif ($type === 'mart') {
                    $builder->whereIn('merchants.merchant_type', ['mart', 'all']);
                }
            }

            $builder->groupBy('merchant_menus.id')
                ->orderBy('total_sold', 'DESC')
                ->orderBy('merchant_menus.id', 'DESC')
                ->limit(12);

            $menus = $builder->get()->getResultArray();

            // Fallback cerdas khusus Mart: Jika belum ada completed order untuk barang mart,
            // ambil produk yang tersedia dari merchant mart agar dashboard mart tidak kosong
            if ($type === 'mart' && empty($menus) && $hasTypeCol) {
                $fallbackBuilder = $db->table('merchant_menus')
                    ->select('merchant_menus.*, merchants.name as merchant_name, merchants.address as merchant_address, merchants.is_open, merchants.merchant_type, merchants.category as merchant_category, 0 as total_sold')
                    ->join('merchants', 'merchants.id = merchant_menus.merchant_id')
                    ->where('merchant_menus.is_available', 1)
                    ->whereIn('merchants.merchant_type', ['mart', 'all'])
                    ->orderBy('merchant_menus.id', 'DESC')
                    ->limit(12);
                $menus = $fallbackBuilder->get()->getResultArray();
            }

            foreach ($menus as &$m) {
                $orig = (float)$m['price'];
                $m['price'] = $orig;
                $disc = !empty($m['discount_price']) ? (float)$m['discount_price'] : 0;
                $m['discount_price'] = $disc;
                if ($disc > 0 && $disc < $orig) {
                    $m['discount_percent'] = max(1, round((($orig - $disc) / max(1, $orig)) * 100));
                } else {
                    $m['discount_percent'] = 0;
                }

                if (!empty($m['photo']) && strpos($m['photo'], 'http') !== 0) {
                    $m['photo'] = base_url($m['photo']);
                }

                $m['total_sold'] = (int)($m['total_sold'] ?? 0);
                $m['merchant_type'] = !empty($m['merchant_type']) ? $m['merchant_type'] : 'food';
                $m['unit'] = !empty($m['unit']) ? $m['unit'] : 'pcs';
            }

            return $this->respond([
                'status' => 'success',
                'data' => $menus
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => 'success',
                'data' => []
            ]);
        }
    }

    // Endpoint Mitra / Merchant yang Baru Bergabung
    public function newMerchants()
    {
        $db = \Config\Database::connect();
        $limit = (int)($this->request->getVar('limit') ?? 10);
        if ($limit <= 0 || $limit > 30) $limit = 10;

        $lat = $this->request->getVar('lat');
        $lng = $this->request->getVar('lng');

        $builder = $db->table('merchants')
            ->where('verification_status', 'approved');

        if ($lat && $lng) {
            $builder->select('merchants.*, (
                6371 * acos(
                    cos(radians(' . $db->escape($lat) . ')) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(' . $db->escape($lng) . ')) + 
                    sin(radians(' . $db->escape($lat) . ')) * 
                    sin(radians(latitude))
                )
            ) AS distance');
        } else {
            $builder->select('merchants.*');
        }

        $merchants = $builder->orderBy('merchants.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $menuModel = new \App\Models\MerchantMenuModel();

        foreach ($merchants as &$m) {
            $photoPath = !empty($m['photo']) ? $m['photo'] : (!empty($m['business_image']) ? $m['business_image'] : '');
            if (!empty($photoPath) && strpos($photoPath, 'http') !== 0) {
                $m['photo'] = base_url($photoPath);
            }
            unset($m['password']);
            unset($m['fcm_token']);

            // Top menu / menu andalan
            $topMenu = $menuModel->where('merchant_id', $m['id'])->where('is_available', 1)->first();
            $m['top_menu'] = $topMenu ? $topMenu['name'] : 'Menu Lengkap';
            $m['merchant_type'] = !empty($m['merchant_type']) ? $m['merchant_type'] : 'food';
        }

        return $this->respond([
            'status' => 'success',
            'data' => $merchants
        ]);
    }
}
