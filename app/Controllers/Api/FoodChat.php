<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FoodChatModel;
use App\Libraries\FcmService;

class FoodChat extends ResourceController
{
    protected $format = 'json';

    public function getMessages($orderId = null)
    {
        $payload = $_SERVER['jwt_payload'] ?? null;
        if (!$payload) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $db = \Config\Database::connect();
        $order = $db->table('food_orders')->where('id', $orderId)->get()->getRow();
        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan makanan tidak ditemukan'], 404);
        }

        $uid = (int)$payload['uid'];
        $role = $payload['role'] ?? '';
        $isUser = ($role === 'user' && (int)$order->user_id === $uid);
        $isDriver = ($role === 'driver' && (int)$order->driver_id === $uid);
        $isMerchant = ($role === 'merchant' && (int)$order->merchant_id === $uid);

        if (!$isUser && !$isDriver && !$isMerchant) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak. Anda bukan pihak dalam pesanan makanan ini.'], 403);
        }

        $chatModel = new FoodChatModel();
        $messages = $chatModel->where('food_order_id', $orderId)->orderBy('created_at', 'ASC')->findAll();

        $user = $db->table('users')->where('id', $order->user_id)->get()->getRow();
        $merchant = $db->table('merchants')->where('id', $order->merchant_id)->get()->getRow();
        $driver = !empty($order->driver_id) ? $db->table('drivers')->where('id', $order->driver_id)->get()->getRow() : null;

        foreach ($messages as &$msg) {
            $sType = $msg['sender_type'] ?? '';
            if ($sType === 'merchant') {
                $msg['sender_name'] = $merchant ? $merchant->name : 'Restoran / Toko';
                $msg['sender_role_label'] = 'Resto / Toko';
            } elseif ($sType === 'driver') {
                $msg['sender_name'] = $driver ? $driver->name : 'Kurir';
                $msg['sender_role_label'] = 'Kurir Pengantar';
            } else {
                $msg['sender_name'] = $user ? $user->name : 'Pelanggan';
                $msg['sender_role_label'] = 'Pelanggan';
            }
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Pesan Chat Food',
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

        $orderId = $json->food_order_id ?? $this->request->getVar('food_order_id');
        $message = trim((string)($json->message ?? $this->request->getVar('message')));

        if (!$orderId || empty($message)) {
            return $this->respond(['status' => 400, 'message' => 'Data pesan tidak lengkap'], 400);
        }

        $db = \Config\Database::connect();
        $order = $db->table('food_orders')->where('id', $orderId)->get()->getRow();
        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan makanan tidak ditemukan'], 404);
        }

        $uid = (int)$payload['uid'];
        $role = $payload['role'] ?? 'user';
        $isUser = ($role === 'user' && (int)$order->user_id === $uid);
        $isDriver = ($role === 'driver' && (int)$order->driver_id === $uid);
        $isMerchant = ($role === 'merchant' && (int)$order->merchant_id === $uid);

        if (!$isUser && !$isDriver && !$isMerchant) {
            return $this->respond(['status' => 403, 'message' => 'Akses ditolak. Anda bukan pihak dalam pesanan makanan ini.'], 403);
        }

        $chatModel = new FoodChatModel();
        $chatModel->insert([
            'food_order_id' => $orderId,
            'sender_type' => $role,
            'sender_id' => $uid,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Kirim notifikasi FCM ke pihak lainnya (Supir -> Penumpang & Merchant, dsb)
        $merchant = $db->table('merchants')->where('id', $order->merchant_id)->get()->getRow();
        $user = $db->table('users')->where('id', $order->user_id)->get()->getRow();
        $driver = !empty($order->driver_id) ? $db->table('drivers')->where('id', $order->driver_id)->get()->getRow() : null;

        $fcmService = new FcmService();
        $fcmPayload = ['type' => 'food_chat', 'order_id' => (string)$orderId, 'sender_type' => $role];

        if ($role === 'user') {
            $senderName = $user ? $user->name : 'Pelanggan';
            $title = "Pesan dari {$senderName}";
            if ($merchant && !empty($merchant->fcm_token)) {
                $fcmService->sendPushNotification($merchant->fcm_token, $title, $message, $fcmPayload);
            }
            if ($driver && !empty($driver->fcm_token)) {
                $fcmService->sendPushNotification($driver->fcm_token, $title, $message, $fcmPayload);
            }
        } elseif ($role === 'merchant') {
            $senderName = $merchant ? $merchant->name : 'Restoran';
            $title = "Pesan dari {$senderName}";
            if ($user && !empty($user->fcm_token)) {
                $fcmService->sendPushNotification($user->fcm_token, $title, $message, $fcmPayload);
            }
            if ($driver && !empty($driver->fcm_token)) {
                $fcmService->sendPushNotification($driver->fcm_token, $title, $message, $fcmPayload);
            }
        } elseif ($role === 'driver') {
            $senderName = $driver ? $driver->name : 'Kurir';
            $title = "Pesan dari Driver {$senderName}";
            if ($user && !empty($user->fcm_token)) {
                $fcmService->sendPushNotification($user->fcm_token, $title, $message, $fcmPayload);
            }
            if ($merchant && !empty($merchant->fcm_token)) {
                $fcmService->sendPushNotification($merchant->fcm_token, $title, $message, $fcmPayload);
            }
        }

        return $this->respond([
            'status' => 201,
            'message' => 'Pesan terkirim'
        ]);
    }
}
