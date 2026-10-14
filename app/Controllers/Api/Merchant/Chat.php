<?php

namespace App\Controllers\Api\Merchant;

use CodeIgniter\RESTful\ResourceController;

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

        // Ambil pesan
        $messages = $db->table('food_chats')
            ->where('food_order_id', $order_id)
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        return $this->respond(['status' => 200, 'message' => 'Daftar pesan', 'data' => $messages]);
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
        $message_text = $this->request->getVar('message');

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

        return $this->respond(['status' => 200, 'message' => 'Pesan terkirim', 'data' => $data]);
    }
}
