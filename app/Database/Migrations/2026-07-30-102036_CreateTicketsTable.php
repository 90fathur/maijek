<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        // Tickets Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'driver_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'status' => ['type' => 'ENUM', 'constraint' => ['open', 'closed'], 'default' => 'open'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tickets');

        // Ticket Replies Table
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sender' => ['type' => 'ENUM', 'constraint' => ['admin', 'user', 'driver']],
            'message' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ticket_replies');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_replies');
        $this->forge->dropTable('tickets');
    }
}
