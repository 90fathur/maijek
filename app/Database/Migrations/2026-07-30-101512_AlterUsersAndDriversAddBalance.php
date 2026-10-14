<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUsersAndDriversAddBalance extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'balance' => ['type' => 'INT', 'constraint' => 11, 'default' => 0]
        ]);
        
        $this->forge->addColumn('drivers', [
            'balance' => ['type' => 'INT', 'constraint' => 11, 'default' => 0]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'balance');
        $this->forge->dropColumn('drivers', 'balance');
    }
}
