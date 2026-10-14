<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToWithdrawals extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('user_id', 'withdrawals')) {
            $this->forge->addColumn('withdrawals', [
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'driver_id'
                ]
            ]);
        }

        if (!$db->fieldExists('user_type', 'withdrawals')) {
            $this->forge->addColumn('withdrawals', [
                'user_type' => [
                    'type' => 'ENUM',
                    'constraint' => ['driver', 'user', 'merchant'],
                    'default' => 'driver',
                    'after' => 'user_id'
                ]
            ]);
        }

        // Jadikan driver_id nullable
        $db->query("ALTER TABLE `withdrawals` MODIFY `driver_id` INT(11) UNSIGNED NULL");
    }

    public function down()
    {
        if ($this->db->fieldExists('user_id', 'withdrawals')) {
            $this->forge->dropColumn('withdrawals', 'user_id');
        }
        if ($this->db->fieldExists('user_type', 'withdrawals')) {
            $this->forge->dropColumn('withdrawals', 'user_type');
        }
    }
}
