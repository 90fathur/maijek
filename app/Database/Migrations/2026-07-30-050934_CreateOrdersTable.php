<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrdersTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'driver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'pickup_address' => [
                'type' => 'TEXT',
            ],
            'pickup_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
            ],
            'pickup_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
            ],
            'dropoff_address' => [
                'type' => 'TEXT',
            ],
            'dropoff_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
            ],
            'dropoff_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
            ],
            'distance' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'price' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'accepted', 'on_trip', 'completed', 'cancelled'],
                'default'    => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('driver_id', 'drivers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('orders');
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}
