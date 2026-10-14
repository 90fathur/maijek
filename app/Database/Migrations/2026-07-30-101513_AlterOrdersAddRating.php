<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterOrdersAddRating extends Migration
{
    public function up()
    {
        $fields = [
            'rating' => ['type' => 'INT', 'constraint' => 1, 'null' => true],
            'review_comment' => ['type' => 'TEXT', 'null' => true],
        ];
        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['rating', 'review_comment']);
    }
}
