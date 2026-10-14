<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CsChatModel;
use App\Models\UserModel;
use App\Models\DriverModel;
use App\Models\MerchantModel;

class CsChat extends BaseController
{
    public function index()
    {
        $model = new CsChatModel();

        // Dapatkan semua room yang unik, urutkan berdasarkan waktu pesan terakhir
        $db = \Config\Database::connect();
        $builder = $db->table('cs_chats');
        $builder->select('room_id, sender_type, MAX(created_at) as last_chat_time');
        $builder->groupBy('room_id, sender_type');
        $builder->orderBy('last_chat_time', 'DESC');
        $rooms = $builder->get()->getResultArray();

        $data['rooms'] = $rooms;
        $data['title'] = 'Layanan Pelanggan (CS)';
        return view('admin/cs_chat/index', $data);
    }

    public function detail($roomId)
    {
        $model = new CsChatModel();
        $chats = $model->where('room_id', $roomId)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Tandai sudah dibaca jika pesannya bukan dari admin
        $db = \Config\Database::connect();
        $db->table('cs_chats')
            ->where('room_id', $roomId)
            ->where('sender_type !=', 'admin')
            ->update(['is_read' => 1]);

        $data['title'] = 'Detail Chat CS';
        $data['chats'] = $chats;
        $data['room_id'] = $roomId;
        return view('admin/cs_chat/detail', $data);
    }

    public function send()
    {
        $roomId = $this->request->getPost('room_id');
        $message = $this->request->getPost('message');

        if (!empty($message)) {
            $model = new CsChatModel();
            $model->insert([
                'room_id' => $roomId,
                'sender_type' => 'admin',
                'sender_id' => 0,
                'message' => $message
            ]);
        }

        return redirect()->to(base_url('admin/cs-chat/' . $roomId));
    }

    public function resolve()
    {
        $roomId = $this->request->getPost('room_id');

        $model = new CsChatModel();
        $model->insert([
            'room_id' => $roomId,
            'sender_type' => 'system',
            'sender_id' => 0,
            'message' => 'CHAT_RESOLVED'
        ]);

        return redirect()->to(base_url('admin/cs-chat/' . $roomId))->with('success', 'Sesi chat telah ditandai selesai.');
    }
}
