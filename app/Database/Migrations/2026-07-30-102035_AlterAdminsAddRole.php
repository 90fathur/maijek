<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAdminsAddRole extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('admins', [
            'role' => [
                'name' => 'role',
                'type' => 'ENUM', 
                'constraint' => ['superadmin', 'cs', 'finance'], 
                'default' => 'superadmin'
            ]
        ]);
        
        $db = \Config\Database::connect();
        $db->query("UPDATE admins SET role = 'superadmin'");
    }

    public function down()
    {
        $this->forge->modifyColumn('admins', [
            'role' => [
                'name' => 'role',
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'admin'
            ]
        ]);
    }
}
