<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVehicleTypeToFoodOrders extends Migration
{
    public function up()
    {
        $fields = [
            'vehicle_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'motor',
                'after'      => 'payment_method'
            ],
        ];
        if (!$this->db->fieldExists('vehicle_type', 'food_orders')) {
            $this->forge->addColumn('food_orders', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('vehicle_type', 'food_orders')) {
            $this->forge->dropColumn('food_orders', 'vehicle_type');
        }
    }
}
