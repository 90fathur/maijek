<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrderChatsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'order_id'    => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'sender_type' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'driver'],
                'default'    => 'user',
            ],
            'message'     => [
                'type'       => 'TEXT',
            ],
            'is_read'     => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at'  => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at'  => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('order_chats');
    }

    public function down()
    {
        $this->forge->dropTable('order_chats');
    }
}
