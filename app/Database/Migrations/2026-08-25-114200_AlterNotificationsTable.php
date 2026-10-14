<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notifications', [
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'default' => null, // If null, it's a broadcast
                'after' => 'id'
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'system',
                'after' => 'target'
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'type'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('notifications', ['user_id', 'type', 'is_read']);
    }
}
