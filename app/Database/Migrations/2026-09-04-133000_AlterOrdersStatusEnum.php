<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersStatusEnum extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'accepted', 'in_progress', 'on_trip', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'accepted', 'on_trip', 'completed', 'cancelled') DEFAULT 'pending'");
    }
}
