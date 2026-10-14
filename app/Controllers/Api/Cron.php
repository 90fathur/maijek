<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class Cron extends ResourceController
{
    protected $format = 'json';

    // Dipanggil setiap menit oleh Cron Job Server
    public function cleanupGhostOrders()
    {
        $db = \Config\Database::connect();

        // Waktu 5 menit yang lalu
        $fiveMinsAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        // ----------------------------------------------------
        // 1. CLEANUP MAIRIDE (OJEK)
        // ----------------------------------------------------
        $builder = $db->table('orders');
        $ghostOrders = $builder->where('status', 'pending')
            ->where('created_at <=', $fiveMinsAgo)
            ->get()
            ->getResult();

        foreach ($ghostOrders as $order) {
            // Batalkan pesanan
            $db->table('orders')->where('id', $order->id)->update(['status' => 'cancelled']);

            // Refund jika pakai Mai-Pay
            if ($order->payment_method == 'maipay') {
                $db->table('users')->where('id', $order->user_id)->set('balance', 'balance + ' . $order->price, false)->update();
            }

            // Kirim notifikasi FCM ke user
            $user = $db->table('users')->where('id', $order->user_id)->get()->getRow();
            if ($user && !empty($user->fcm_token)) {
                $fcmService = new \App\Libraries\FcmService();
                $fcmService->sendPushNotification(
                    $user->fcm_token,
                    "Pesanan Dibatalkan Otomatis",
                    "Maaf, tidak ada supir yang mengambil pesanan Anda. " . ($order->payment_method == 'maipay' ? "Saldo telah dikembalikan." : ""),
                    ['type' => 'order_cancelled', 'order_id' => $order->id]
                );
            }
        }

        // ----------------------------------------------------
        // 2. CLEANUP MAIFOOD (MAKANAN) - Jika driver tidak ada yang ambil
        // ----------------------------------------------------
        // Catatan: Untuk makanan, pending = belum direspon resto. ready = belum direspon driver.
        // Kita auto-cancel yang pending (resto tidak respon) lebih dari 10 menit
        $tenMinsAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));

        $foodBuilder = $db->table('food_orders');
        $ghostFoodOrders = $foodBuilder->where('status', 'pending')
            ->where('created_at <=', $tenMinsAgo)
            ->get()
            ->getResult();

        foreach ($ghostFoodOrders as $order) {
            $db->table('food_orders')->where('id', $order->id)->update(['status' => 'cancelled']);

            if ($order->payment_method == 'maipay') {
                $db->table('users')->where('id', $order->user_id)->set('balance', 'balance + ' . $order->total_price, false)->update();
            }

            $user = $db->table('users')->where('id', $order->user_id)->get()->getRow();
            if ($user && !empty($user->fcm_token)) {
                $fcmService = new \App\Libraries\FcmService();
                $fcmService->sendPushNotification(
                    $user->fcm_token,
                    "Pesanan Makanan Dibatalkan",
                    "Maaf, restoran tidak merespon pesanan Anda. " . ($order->payment_method == 'maipay' ? "Saldo telah dikembalikan." : ""),
                    ['type' => 'food_cancelled', 'order_id' => $order->id]
                );
            }
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Cleanup Ghost Orders Selesai',
            'mairide_cancelled' => count($ghostOrders),
            'maifood_cancelled' => count($ghostFoodOrders)
        ]);
    }
}
