<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersAddTimestamps extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'accepted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'in_progress_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['accepted_at', 'in_progress_at', 'completed_at']);
    }
}
