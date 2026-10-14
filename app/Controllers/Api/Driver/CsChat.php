<?php

namespace App\Controllers\Api\Driver;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CsChatModel;

class CsChat extends ResourceController
{
    public function send()
    {
        $payload = $_SERVER['jwt_payload'];
        $message = $this->request->getVar('message');

        if (empty($message)) {
            return $this->respond(['status' => 400, 'message' => 'Pesan tidak boleh kosong'], 400);
        }

        $model = new CsChatModel();
        $model->insert([
            'room_id' => 'driver_' . $payload['uid'], // For driver it's usually 'uid' or 'driver_id', need to check.
            'sender_type' => 'driver',
            'sender_id' => $payload['uid'],
            'message' => $message
        ]);

        return $this->respond(['status' => 200, 'message' => 'Pesan terkirim']);
    }

    public function messages()
    {
        $payload = $_SERVER['jwt_payload'];
        $model = new CsChatModel();

        $chats = $model->where('room_id', 'driver_' . $payload['uid'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $this->respond(['status' => 200, 'message' => 'Riwayat chat CS', 'data' => $chats]);
    }
}
