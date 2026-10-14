<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class MigrateNotifications extends ResourceController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $results = [];

        $columns = [
            'user_id' => "ALTER TABLE notifications ADD COLUMN user_id INT(11) UNSIGNED NULL DEFAULT NULL AFTER id;",
            'type' => "ALTER TABLE notifications ADD COLUMN type VARCHAR(50) DEFAULT 'system' AFTER target;",
            'is_read' => "ALTER TABLE notifications ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER type;"
        ];

        foreach ($columns as $name => $query) {
            try {
                // Check if column exists first
                if (!$db->fieldExists($name, 'notifications')) {
                    $db->query($query);
                    $results[] = "$name ditambahkan.";
                } else {
                    $results[] = "$name sudah ada.";
                }
            } catch (\Exception $e) {
                $results[] = "Error $name: " . $e->getMessage();
            }
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Migrasi selesai.',
            'details' => $results
        ], 200);
    }
}
