<?php

namespace App\Controllers;

use App\Database\Migrations\AddDiscountToMenusAndFoodOrders;

class MigrateDiscounts extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        try {
            require_once APPPATH . 'Database/Migrations/2026-09-01-161000_AddDiscountToMenusAndFoodOrders.php';
            $migration = new AddDiscountToMenusAndFoodOrders($forge);
            $migration->up();
            echo "Kolom discount_price di merchant_menus & discount_amount di food_orders berhasil dibuat.<br>";
        } catch (\Throwable $e) {
            echo "Migration notice: " . $e->getMessage() . "<br>";
        }

        // 3. Pastikan Promo MAIPERTAMA & GRATISONGKIR ada di tabel promos
        try {
            $promoModel = new \App\Models\PromoModel();
            $p1 = $promoModel->where('code', 'MAIPERTAMA')->first();
            if (!$p1) {
                $promoModel->insert([
                    'code'            => 'MAIPERTAMA',
                    'discount_amount' => 50,
                    'discount_type'   => 'percent',
                    'valid_until'     => '2030-12-31 23:59:59',
                    'status'          => 'active'
                ]);
                echo "Promo MAIPERTAMA (Diskon 50% Pengguna Baru) berhasil dibuat.<br>";
            }

            $p2 = $promoModel->where('code', 'GRATISONGKIR')->first();
            if (!$p2) {
                $promoModel->insert([
                    'code'            => 'GRATISONGKIR',
                    'discount_amount' => 8000,
                    'discount_type'   => 'fixed',
                    'valid_until'     => '2030-12-31 23:59:59',
                    'status'          => 'active'
                ]);
                echo "Promo GRATISONGKIR (Potongan Ongkir Rp 8.000) berhasil dibuat.<br>";
            }
        } catch (\Throwable $e) {
            echo "Promo seed notice: " . $e->getMessage() . "<br>";
        }

        // 4. Update beberapa menu contoh dengan harga coret jika belum ada
        try {
            $menus = $db->table('merchant_menus')->where('discount_price IS NULL')->limit(5)->get()->getResultArray();
            foreach ($menus as $m) {
                // Beri diskon 20-30% sebagai harga promo contoh
                $originalPrice = (float)$m['price'];
                if ($originalPrice >= 15000) {
                    $discPrice = round(($originalPrice * 0.75) / 1000) * 1000;
                    $db->table('merchant_menus')->where('id', $m['id'])->update(['discount_price' => $discPrice]);
                }
            }
            echo "Contoh diskon menu makanan berhasil dipasang.<br>";
        } catch (\Throwable $e) {
            echo "Menu discount notice: " . $e->getMessage() . "<br>";
        }

        echo "<strong>Semua migrasi diskon selesai!</strong>";
    }
}
