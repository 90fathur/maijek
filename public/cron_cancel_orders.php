<?php
// cron_cancel_orders.php
// Script ini dijalankan setiap menit via Cron Job cPanel/Linux atau CLI
// Berfungsi untuk membatalkan pesanan yang nyangkut (pending > 10 menit) agar tidak menumpuk di dashboard admin

// Security check: only CLI or valid CRON_TOKEN
$isCli = (php_sapi_name() === 'cli' || defined('STDIN'));
$cronToken = getenv('CRON_TOKEN') ?: 'maijek_cron_secret_token_default';
$providedToken = $_GET['token'] ?? '';

if (!$isCli && (!hash_equals($cronToken, (string)$providedToken))) {
    http_response_code(403);
    exit("Access Denied: CLI or valid cron token required.\n");
}

require_once __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/Boot.php';
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', getenv('CI_ENVIRONMENT') ?: 'production');
}
\CodeIgniter\Boot::bootConsole($paths);

try {
    date_default_timezone_set('Asia/Makassar');

    $db = \Config\Database::connect();
    $limitTime = date('Y-m-d H:i:s', strtotime('-10 minutes'));

    $builder = $db->table('orders');
    $builder->where('status', 'pending');
    $builder->where('created_at <=', $limitTime);
    $builder->update([
        'status' => 'cancelled',
        'cancel_reason' => 'Dibatalkan sistem: Tidak ada driver yang menerima pesanan dalam waktu 10 menit'
    ]);

    $affected = $db->affectedRows();
    echo "[" . date('Y-m-d H:i:s') . "] Cron Cancel Orders: Berhasil membatalkan $affected pesanan yang nyangkut.\n";
} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Cron Cancel Orders Error: " . $e->getMessage() . "\n";
}
