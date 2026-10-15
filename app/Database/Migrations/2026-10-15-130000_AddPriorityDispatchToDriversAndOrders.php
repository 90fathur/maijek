<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPriorityDispatchToDriversAndOrders extends Migration
{
    public function up()
    {
        // 1. Kolom is_priority pada drivers
        if (!$this->db->fieldExists('is_priority', 'drivers')) {
            $this->forge->addColumn('drivers', [
                'is_priority' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'null'       => false,
                    'after'      => 'status',
                ],
            ]);
            try {
                $this->db->query('ALTER TABLE drivers ADD INDEX idx_drivers_priority (is_priority, is_online, status)');
            } catch (\Throwable $e) {}
        }

        // 2. Kolom priority_dispatched_at dan fallback_dispatched_at pada orders
        if (!$this->db->fieldExists('priority_dispatched_at', 'orders')) {
            $this->forge->addColumn('orders', [
                'priority_dispatched_at' => [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'status',
                ],
            ]);
        }
        if (!$this->db->fieldExists('fallback_dispatched_at', 'orders')) {
            $this->forge->addColumn('orders', [
                'fallback_dispatched_at' => [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'priority_dispatched_at',
                ],
            ]);
        }

        // 3. Kolom priority_dispatched_at dan fallback_dispatched_at pada food_orders
        if (!$this->db->fieldExists('priority_dispatched_at', 'food_orders')) {
            $this->forge->addColumn('food_orders', [
                'priority_dispatched_at' => [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'status',
                ],
            ]);
        }
        if (!$this->db->fieldExists('fallback_dispatched_at', 'food_orders')) {
            $this->forge->addColumn('food_orders', [
                'fallback_dispatched_at' => [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'priority_dispatched_at',
                ],
            ]);
        }

        // 4. Inisialisasi default settings
        $defaults = [
            'priority_dispatch_enabled' => '1',
            'priority_dispatch_timeout' => '10',
        ];
        foreach ($defaults as $k => $v) {
            $exist = $this->db->table('settings')->where('setting_key', $k)->countAllResults();
            if ($exist == 0) {
                $this->db->table('settings')->insert([
                    'setting_key'   => $k,
                    'setting_value' => $v,
                    'description'   => 'Pengaturan Driver Prioritas Dinamis'
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_priority', 'drivers')) {
            $this->forge->dropColumn('drivers', 'is_priority');
        }
        if ($this->db->fieldExists('priority_dispatched_at', 'orders')) {
            $this->forge->dropColumn('orders', 'priority_dispatched_at');
        }
        if ($this->db->fieldExists('fallback_dispatched_at', 'orders')) {
            $this->forge->dropColumn('orders', 'fallback_dispatched_at');
        }
        if ($this->db->fieldExists('priority_dispatched_at', 'food_orders')) {
            $this->forge->dropColumn('food_orders', 'priority_dispatched_at');
        }
        if ($this->db->fieldExists('fallback_dispatched_at', 'food_orders')) {
            $this->forge->dropColumn('food_orders', 'fallback_dispatched_at');
        }
    }
}
