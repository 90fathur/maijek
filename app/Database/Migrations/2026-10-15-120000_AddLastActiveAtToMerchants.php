<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastActiveAtToMerchants extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('last_active_at', 'merchants')) {
            $this->forge->addColumn('merchants', [
                'last_active_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'is_open',
                ],
            ]);
            $this->db->query('ALTER TABLE merchants ADD INDEX idx_merchant_last_active (last_active_at)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('last_active_at', 'merchants')) {
            $this->forge->dropColumn('merchants', 'last_active_at');
        }
    }
}
