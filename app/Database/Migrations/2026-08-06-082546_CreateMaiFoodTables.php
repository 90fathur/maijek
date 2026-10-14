<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaiFoodTables extends Migration
{
    public function up()
    {
        // 1. Table merchants
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'phone'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'password'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'address'        => ['type' => 'TEXT', 'null' => true],
            'latitude'       => ['type' => 'DOUBLE', 'null' => true],
            'longitude'      => ['type' => 'DOUBLE', 'null' => true],
            'photo'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_open'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'balance'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'fcm_token'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('merchants', true);

        // 2. Table merchant_menus
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'merchant_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'photo'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_available'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('merchant_menus', true);

        // 3. Table food_orders
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'driver_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'merchant_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'delivery_address'=> ['type' => 'TEXT', 'null' => true],
            'delivery_lat'   => ['type' => 'DOUBLE', 'null' => true],
            'delivery_lng'   => ['type' => 'DOUBLE', 'null' => true],
            'distance'       => ['type' => 'DOUBLE', 'null' => true],
            'food_total'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'delivery_fee'   => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'total_price'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'payment_method' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'cash'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted_by_merchant', 'driver_assigned', 'driver_at_merchant', 'delivering', 'completed', 'cancelled'], 'default' => 'pending'],
            'cancel_reason'  => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('driver_id', 'drivers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('food_orders', true);

        // 4. Table food_order_items
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'food_order_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'menu_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity'       => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'notes'          => ['type' => 'TEXT', 'null' => true]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('food_order_id', 'food_orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('menu_id', 'merchant_menus', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('food_order_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('food_order_items', true);
        $this->forge->dropTable('food_orders', true);
        $this->forge->dropTable('merchant_menus', true);
        $this->forge->dropTable('merchants', true);
    }
}
