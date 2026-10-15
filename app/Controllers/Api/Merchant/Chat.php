<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;
use App\Libraries\FcmService;

class Chat extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $order_id = $this->request->getVar('order_id');
        if (!$order_id) {
            return $this->respond(['status' => 400, 'message' => 'Parameter order_id wajib diisi'], 400);
        }

        // Pastikan order tersebut milik merchant ini
        $db = \Config\Database::connect();
        $order = $db->table('food_orders')
            ->where('id', $order_id)
            ->where('merchant_id', $merchant_id)
            ->get()->getRowArray();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan atau bukan milik Anda'], 404);
        }

        // Ambil data user, merchant, dan driver untuk pelabelan
        $user = $db->table('users')->where('id', $order['user_id'])->get()->getRow();
        $merchant = $db->table('merchants')->where('id', $merchant_id)->get()->getRow();
        $driver = !empty($order['driver_id']) ? $db->table('drivers')->where('id', $order['driver_id'])->get()->getRow() : null;

        // Ambil pesan
        $messages = $db->table('food_chats')
            ->where('food_order_id', $order_id)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

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
            'message' => 'Daftar pesan', 
            'data' => $messages,
            'driver' => $driver ? [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'photo' => $driver->photo,
            ] : null,
            'customer' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'photo' => $user->photo,
            ] : null
        ]);
    }

    public function send()
    {
        $payload = $_SERVER['jwt_payload'];
        $merchant_id = $payload['uid'];

        $rules = [
            'order_id' => 'required|numeric',
            'message'  => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond(['status' => 400, 'message' => 'Data tidak valid'], 400);
        }

        $order_id = $this->request->getVar('order_id');
        $message_text = trim((string)$this->request->getVar('message'));

        $db = \Config\Database::connect();
        $order = $db->table('food_orders')
            ->where('id', $order_id)
            ->where('merchant_id', $merchant_id)
            ->get()->getRowArray();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $data = [
            'food_order_id' => $order_id,
            'sender_type'   => 'merchant',
            'sender_id'     => $merchant_id,
            'message'       => $message_text,
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $db->table('food_chats')->insert($data);
        $data['id'] = $db->insertID();

        // Kirim Notifikasi FCM ke Pelanggan dan Driver
        $merchant = $db->table('merchants')->where('id', $merchant_id)->get()->getRow();
        $merchantName = $merchant ? $merchant->name : 'Restoran';
        $title = "Pesan dari {$merchantName}";
        $fcmPayload = [
            'type' => 'food_chat',
            'order_id' => (string)$order_id,
            'sender_type' => 'merchant'
        ];

        $fcmService = new FcmService();

        // 1. Notif ke Pelanggan
        $user = $db->table('users')->where('id', $order['user_id'])->get()->getRow();
        if ($user && !empty($user->fcm_token)) {
            $fcmService->sendPushNotification($user->fcm_token, $title, $message_text, $fcmPayload);
        }

        // 2. Notif ke Driver (jika sudah ditugaskan)
        if (!empty($order['driver_id'])) {
            $driver = $db->table('drivers')->where('id', $order['driver_id'])->get()->getRow();
            if ($driver && !empty($driver->fcm_token)) {
                $fcmService->sendPushNotification($driver->fcm_token, $title, $message_text, $fcmPayload);
            }
        }

        return $this->respond(['status' => 200, 'message' => 'Pesan terkirim', 'data' => $data]);
    }
}
