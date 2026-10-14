<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehicleClassesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'type' => ['type' => 'ENUM', 'constraint' => ['motor', 'mobil'], 'default' => 'motor'],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'inactive'], 'default' => 'active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('vehicle_classes');
    }

    public function down()
    {
        $this->forge->dropTable('vehicle_classes');
    }
}
