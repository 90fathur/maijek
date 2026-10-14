<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentMethodToOrders extends Migration
{
    public function up()
    {
        $fields = [
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'maipay'],
                'default'    => 'cash',
                'after'      => 'price' // Posisikan setelah kolom price
            ]
        ];
        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'payment_method');
    }
}
