<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCsChatsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'room_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'sender_type' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'driver', 'merchant', 'admin'],
            ],
            'sender_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cs_chats');
    }

    public function down()
    {
        $this->forge->dropTable('cs_chats');
    }
}
