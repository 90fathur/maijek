<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        $db = $this->db;

        // Helper untuk menambah index dengan aman tanpa error jika index sudah ada
        $addIndexSafely = function ($table, $indexName, $columnsSql) use ($db) {
            if (!$db->tableExists($table)) {
                return;
            }
            $existing = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'")->getResultArray();
            if (empty($existing)) {
                $db->query("ALTER TABLE `$table` ADD INDEX `$indexName` ($columnsSql)");
            }
        };

        // 1. Tabel orders
        $addIndexSafely('orders', 'idx_orders_status_service', '`status`, `service_type`');
        $addIndexSafely('orders', 'idx_orders_driver_status', '`driver_id`, `status`');
        $addIndexSafely('orders', 'idx_orders_user_status', '`user_id`, `status`');
        $addIndexSafely('orders', 'idx_orders_created', '`created_at`');

        // 2. Tabel drivers
        $addIndexSafely('drivers', 'idx_drivers_radar', '`is_online`, `status`, `verification_status`, `vehicle_type`');
        $addIndexSafely('drivers', 'idx_drivers_coords', '`latitude`, `longitude`');

        // 3. Tabel food_orders
        $addIndexSafely('food_orders', 'idx_food_driver_status', '`driver_id`, `status`');
        $addIndexSafely('food_orders', 'idx_food_user_status', '`user_id`, `status`');
        $addIndexSafely('food_orders', 'idx_food_merchant_status', '`merchant_id`, `status`');
        $addIndexSafely('food_orders', 'idx_food_status', '`status`');
        $addIndexSafely('food_orders', 'idx_food_created', '`created_at`');

        // 4. Tabel order_chats
        $addIndexSafely('order_chats', 'idx_chats_order_unread', '`order_id`, `is_read`, `sender_type`');
        $addIndexSafely('order_chats', 'idx_chats_created', '`created_at`');
    }

    public function down()
    {
        $db = $this->db;
        $dropIndexSafely = function ($table, $indexName) use ($db) {
            if (!$db->tableExists($table)) {
                return;
            }
            $existing = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'")->getResultArray();
            if (!empty($existing)) {
                $db->query("ALTER TABLE `$table` DROP INDEX `$indexName`");
            }
        };

        $dropIndexSafely('orders', 'idx_orders_status_service');
        $dropIndexSafely('orders', 'idx_orders_driver_status');
        $dropIndexSafely('orders', 'idx_orders_user_status');
        $dropIndexSafely('orders', 'idx_orders_created');

        $dropIndexSafely('drivers', 'idx_drivers_radar');
        $dropIndexSafely('drivers', 'idx_drivers_coords');

        $dropIndexSafely('food_orders', 'idx_food_driver_status');
        $dropIndexSafely('food_orders', 'idx_food_user_status');
        $dropIndexSafely('food_orders', 'idx_food_merchant_status');
        $dropIndexSafely('food_orders', 'idx_food_status');
        $dropIndexSafely('food_orders', 'idx_food_created');

        $dropIndexSafely('order_chats', 'idx_chats_order_unread');
        $dropIndexSafely('order_chats', 'idx_chats_created');
    }
}
