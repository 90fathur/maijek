<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRatingToDrivers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('drivers', [
            'rating' => [
                'type'       => 'DECIMAL',
                'constraint' => '2,1',
                'default'    => 5.0,
                'after'      => 'verification_status'
            ],
            'rating_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'rating'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('drivers', 'rating');
        $this->forge->dropColumn('drivers', 'rating_count');
    }
}
