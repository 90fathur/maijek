<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true,
            ],
            'code' => [
                'type' => 'VARCHAR', 'constraint' => '20', 'unique' => true,
            ],
            'discount_amount' => [
                'type' => 'INT', 'constraint' => 11,
            ],
            'discount_type' => [
                'type' => 'VARCHAR', 'constraint' => '10', 'default' => 'fixed', // 'percent' or 'fixed'
            ],
            'valid_until' => [
                'type' => 'DATETIME', 'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR', 'constraint' => '10', 'default' => 'active', // 'active' or 'inactive'
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('promos');
    }

    public function down()
    {
        $this->forge->dropTable('promos');
    }
}
