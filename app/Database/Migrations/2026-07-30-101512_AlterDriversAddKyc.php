<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterDriversAddKyc extends Migration
{
    public function up()
    {
        $fields = [
            'ktp_image' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'sim_image' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'stnk_image' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'verification_status' => [
                'type' => 'ENUM', 
                'constraint' => ['unverified', 'pending', 'approved', 'rejected'], 
                'default' => 'unverified'
            ],
        ];
        $this->forge->addColumn('drivers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('drivers', ['ktp_image', 'sim_image', 'stnk_image', 'verification_status']);
    }
}
