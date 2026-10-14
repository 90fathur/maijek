<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterFoodOrdersAddRating extends Migration
{
    public function up()
    {
        $fields = [
            'merchant_rating' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'driver_rating' => [
                'type'       => 'INT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'review_merchant' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'review_driver' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('food_orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('food_orders', 'merchant_rating');
        $this->forge->dropColumn('food_orders', 'driver_rating');
        $this->forge->dropColumn('food_orders', 'review_merchant');
        $this->forge->dropColumn('food_orders', 'review_driver');
    }
}
