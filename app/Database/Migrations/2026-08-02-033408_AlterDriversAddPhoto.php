<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterDriversAddPhoto extends Migration
{
    public function up()
    {
        $this->forge->addColumn('drivers', [
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('drivers', 'photo');
    }
}
