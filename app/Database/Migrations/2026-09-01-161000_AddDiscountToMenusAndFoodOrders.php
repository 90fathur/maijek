<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountToMenusAndFoodOrders extends Migration
{
    public function up()
    {
        // 1. Tambah discount_price di merchant_menus
        if (!$this->db->fieldExists('discount_price', 'merchant_menus')) {
            $this->forge->addColumn('merchant_menus', [
                'discount_price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'after'      => 'price'
                ]
            ]);
        }

        // 2. Tambah discount_amount dan promo_code di food_orders
        if (!$this->db->fieldExists('discount_amount', 'food_orders')) {
            $this->forge->addColumn('food_orders', [
                'discount_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => '0.00',
                    'after'      => 'delivery_fee'
                ]
            ]);
        }

        if (!$this->db->fieldExists('promo_code', 'food_orders')) {
            $this->forge->addColumn('food_orders', [
                'promo_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'discount_amount'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('discount_price', 'merchant_menus')) {
            $this->forge->dropColumn('merchant_menus', 'discount_price');
        }
        if ($this->db->fieldExists('discount_amount', 'food_orders')) {
            $this->forge->dropColumn('food_orders', 'discount_amount');
        }
        if ($this->db->fieldExists('promo_code', 'food_orders')) {
            $this->forge->dropColumn('food_orders', 'promo_code');
        }
    }
}
