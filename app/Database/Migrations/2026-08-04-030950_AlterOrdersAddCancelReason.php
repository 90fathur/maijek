<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersAddCancelReason extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'cancel_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'status'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'cancel_reason');
    }
}
