<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestProfile extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:profile';
    protected $description = 'Test Driver Profile Stats';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        // Find driver id 1 or latest active driver
        $driver = $db->table('users')->where('role', 'driver')->orderBy('id', 'DESC')->get()->getRow();
        if (!$driver) {
            CLI::write('No driver found!', 'red');
            return;
        }

        $uid = $driver->id;
        CLI::write("Testing for Driver ID: $uid", 'yellow');

        $todayStr = date('Y-m-d');
        CLI::write("Today String: $todayStr", 'yellow');

        // Test Query 1: Old way DATE(created_at)
        $oldQuery = $db->table('orders')->selectSum('price', 'earnings')->selectCount('id', 'count')->where('driver_id', $uid)->where('status', 'completed')->where('DATE(created_at)', $todayStr)->get()->getRow();
        CLI::write("Old Query (DATE created_at) Earnings: " . ($oldQuery->earnings ?? 0) . " Count: " . ($oldQuery->count ?? 0), 'cyan');

        // Test Query 2: LIKE updated_at
        $newQuery = $db->table('orders')->selectSum('price', 'earnings')->selectCount('id', 'count')->where('driver_id', $uid)->where('status', 'completed')->like('updated_at', $todayStr, 'after')->get()->getRow();
        CLI::write("New Query (LIKE updated_at) Earnings: " . ($newQuery->earnings ?? 0) . " Count: " . ($newQuery->count ?? 0), 'green');

        // Test Query 3: Manual like string
        $q3 = $db->query("SELECT SUM(price) as earnings, COUNT(id) as count FROM orders WHERE driver_id = $uid AND status = 'completed' AND updated_at LIKE '{$todayStr}%'")->getRow();
        CLI::write("Raw SQL LIKE Earnings: " . ($q3->earnings ?? 0) . " Count: " . ($q3->count ?? 0), 'magenta');

        // Dump latest completed order for this driver
        $latest = $db->query("SELECT id, status, created_at, updated_at, completed_at FROM orders WHERE driver_id = $uid AND status = 'completed' ORDER BY id DESC LIMIT 1")->getRow();
        if ($latest) {
            CLI::write("Latest Completed Order: ID={$latest->id}, created_at={$latest->created_at}, updated_at={$latest->updated_at}, completed_at={$latest->completed_at}", 'white');
        } else {
            CLI::write("No completed orders found for this driver.", 'red');
        }
    }
}
