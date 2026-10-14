<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterFoodOrdersEnum extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE food_orders MODIFY COLUMN status ENUM('pending', 'accepted_by_merchant', 'processing', 'ready', 'driver_assigned', 'accepted', 'driver_at_merchant', 'delivering', 'in_progress', 'delivered', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE food_orders MODIFY COLUMN status ENUM('pending', 'accepted_by_merchant', 'driver_assigned', 'driver_at_merchant', 'delivering', 'completed', 'cancelled') DEFAULT 'pending'");
    }
}
