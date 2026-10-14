<?php
require_once dirname(__DIR__) . '/app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootWeb($paths);

$db = \Config\Database::connect();
$db->table('merchants')->where('phone', '089876543210')->delete();
echo "Deleted test merchant: 089876543210\n";
@unlink(__FILE__);
