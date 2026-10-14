<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class Chat extends ResourceController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getMessages($order_id)
    {
        $payload = $_SERVER['jwt_payload'] ?? null;
        if (!$payload) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $order = $this->db->table('orders')->where('id', $order_id)->get()->getRow();
        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $uid = (int)$payload['uid'];
        $role = $payload['role'] ?? '';
        $isUser = ($role === 'user' && (int)$order->user_id === $uid);
        $isDriver = ($role === 'driver' && (int)$order->driver_id === $uid);

        if (!$isUser && !$isDriver) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak. Anda bukan pihak dalam pesanan ini.'], 403);
        }

        $requestURI = $_SERVER['REQUEST_URI'] ?? '';
        $viewerType = $isDriver ? 'driver' : 'user';
        $senderToMarkRead = $viewerType === 'driver' ? 'user' : 'driver';

        // Mark messages from the other person as read
        try {
            if ($this->db->fieldExists('is_read', 'order_chats')) {
                $this->db->table('order_chats')
                    ->where('order_id', $order_id)
                    ->where('sender_type', $senderToMarkRead)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);
            }
        } catch (\Exception $e) {
            // Abaikan jika kolom tidak ada
        }

        $messages = $this->db->table('order_chats')
            ->where('order_id', $order_id)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        return $this->respond([
            'status' => 200,
            'data' => $messages
        ]);
    }

    public function sendMessage()
    {
        $payload = $_SERVER['jwt_payload'] ?? null;
        if (!$payload) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $json = $this->request->getJSON();

        $orderId = $json->order_id ?? $this->request->getVar('order_id');
        $message = trim((string)($json->message ?? $this->request->getVar('message')));

        if (!$orderId || empty($message)) {
            return $this->respond(['status' => 400, 'message' => 'Data pesan tidak lengkap'], 400);
        }

        $order = $this->db->table('orders')->where('id', $orderId)->get()->getRow();
        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $uid = (int)$payload['uid'];
        $role = $payload['role'] ?? 'user';
        $isUser = ($role === 'user' && (int)$order->user_id === $uid);
        $isDriver = ($role === 'driver' && (int)$order->driver_id === $uid);

        if (!$isUser && !$isDriver) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak. Anda bukan pihak dalam pesanan ini.'], 403);
        }

        $senderType = $isDriver ? 'driver' : 'user';
        $senderId = $uid;

        $this->db->table('order_chats')->insert([
            'order_id' => $orderId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Send Push Notification
        $fcmService = new \App\Libraries\FcmService();
        $title = "Pesan Baru";

        if ($senderType === 'user') {
            // Notif ke Driver
            $driver = $this->db->table('drivers')->where('id', $order->driver_id)->get()->getRow();
            if ($driver && !empty($driver->fcm_token)) {
                $user = $this->db->table('users')->where('id', $senderId)->get()->getRow();
                $senderName = $user ? $user->name : "Penumpang";
                $fcmService->sendPushNotification($driver->fcm_token, "Pesan dari $senderName", $message, ['type' => 'chat', 'order_id' => $orderId]);
            }
        } else {
            // Notif ke User
            $user = $this->db->table('users')->where('id', $order->user_id)->get()->getRow();
            if ($user && !empty($user->fcm_token)) {
                $driver = $this->db->table('drivers')->where('id', $senderId)->get()->getRow();
                $senderName = $driver ? $driver->name : "Supir";
                $fcmService->sendPushNotification($user->fcm_token, "Pesan dari $senderName", $message, ['type' => 'chat', 'order_id' => $orderId]);
            }
        }

        return $this->respond([
            'status' => 201,
            'message' => 'Pesan terkirim'
        ], 201);
    }
}
