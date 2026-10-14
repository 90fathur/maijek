<?php

/**
 * Script ini dijalankan via CronJob setiap hari (misalnya: 0 0 * * *).
 * Tujuan: Menutup secara otomatis sesi chat CS yang tidak ada aktivitas selama 1x24 jam.
 */

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
    $db = \Config\Database::connect();

    $builder = $db->table('cs_chats');

    // 1. Dapatkan daftar room beserta pesan terakhirnya
    $query = $db->query("
        SELECT room_id, MAX(created_at) as last_activity
        FROM cs_chats
        GROUP BY room_id
        HAVING last_activity < (NOW() - INTERVAL 1 DAY)
    ");

    $expiredRooms = $query->getResultArray();
    $resolvedCount = 0;

    foreach ($expiredRooms as $room) {
        // 2. Cek pesan terakhir di room tersebut
        $lastMsg = $db->table('cs_chats')
            ->where('room_id', $room['room_id'])
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC') // fallback jika created_at sama
            ->limit(1)
            ->get()
            ->getRowArray();

        // 3. Jika pesan terakhir bukan 'CHAT_RESOLVED', tutup sesinya
        if ($lastMsg && $lastMsg['message'] !== 'CHAT_RESOLVED') {
            $db->table('cs_chats')->insert([
                'room_id'     => $room['room_id'],
                'sender_type' => 'system',
                'sender_id'   => 0,
                'message'     => 'CHAT_RESOLVED',
                'created_at'  => date('Y-m-d H:i:s')
            ]);
            $resolvedCount++;
        }
    }

    echo "Proses Auto-Resolve CS Chat Selesai.\n";
    echo "Total sesi yang ditutup otomatis: $resolvedCount\n";
} catch (\Throwable $e) {
    echo "Auto-Resolve CS Chat Error: " . $e->getMessage() . "\n";
}
