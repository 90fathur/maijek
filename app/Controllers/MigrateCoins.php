<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MigrateCoins extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();

        try {
            $db->query("ALTER TABLE `users` ADD COLUMN `coins` INT DEFAULT 0 AFTER `balance`");
            echo "Users table altered.<br>";
        } catch (\Exception $e) {
            echo "Users table might already have coins column.<br>";
        }

        try {
            $db->query("ALTER TABLE `drivers` ADD COLUMN `coins` INT DEFAULT 0 AFTER `balance`");
            echo "Drivers table altered.<br>";
        } catch (\Exception $e) {
            echo "Drivers table might already have coins column.<br>";
        }

        echo "Migration finished.";
    }
}
