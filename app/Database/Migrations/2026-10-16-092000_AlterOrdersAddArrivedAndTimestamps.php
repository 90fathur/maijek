<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersAddArrivedAndTimestamps extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'accepted', 'arrived', 'in_progress', 'on_trip', 'completed', 'cancelled') DEFAULT 'pending'");

        $fields = [];
        if (!$this->db->fieldExists('accepted_at', 'orders')) {
            $fields['accepted_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('arrived_at', 'orders')) {
            $fields['arrived_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('in_progress_at', 'orders')) {
            $fields['in_progress_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('completed_at', 'orders')) {
            $fields['completed_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('orders', $fields);
        }
    }

    public function down()
    {
        // Safe keep
    }
}
