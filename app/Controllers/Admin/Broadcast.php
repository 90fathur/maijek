<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotificationModel;

class Broadcast extends BaseController
{
    public function index()
    {
        $notificationModel = new NotificationModel();
        $data = [
            'title' => 'Broadcast Pesan | Maijek Admin',
            'notifications' => $notificationModel->orderBy('created_at', 'DESC')->findAll()
        ];
        return view('admin/broadcast/index', $data);
    }

    public function send()
    {
        $notificationModel = new NotificationModel();
        $fcmService = new \App\Libraries\FcmService();

        $data = [
            'title' => $this->request->getPost('title'),
            'message' => $this->request->getPost('message'),
            'target' => $this->request->getPost('target'), // all, users, drivers
            'type' => $this->request->getPost('type') ?? 'system',
        ];

        // 1. Simpan ke database (Notifikasi Kotak Masuk Penumpang)
        $notificationModel->insert($data);

        // 2. Kirim ke Topic Firebase (Instan 1 request langsung ke seluruh perangkat)
        $topicTarget = ($data['target'] === 'users') ? 'users' : (($data['target'] === 'drivers') ? 'drivers' : 'all');
        $fcmService->sendToTopic($topicTarget, $data['title'], $data['message'], [
            'type' => $data['type'],
            'target' => $data['target']
        ]);

        // 3. Kirim ke token individual secara PARALEL BATCH (Super cepat, 30 token/detik)
        $db = \Config\Database::connect();
        $successCount = 0;
        $cleanedTokens = 0;

        if ($data['target'] == 'users' || $data['target'] == 'all') {
            $users = $db->table('users')->select('id, fcm_token')->where('fcm_token !=', null)->where('fcm_token !=', '')->get()->getResult();
            $tokens = [];
            foreach ($users as $u) { $tokens[$u->id] = $u->fcm_token; }
            if (!empty($tokens)) {
                $res = $fcmService->sendPushNotificationBatch($tokens, $data['title'], $data['message'], ['type' => $data['type'], 'target' => 'user']);
                $successCount += $res['success'];
                if (!empty($res['invalid_ids'])) {
                    $db->table('users')->whereIn('id', $res['invalid_ids'])->update(['fcm_token' => null]);
                    $cleanedTokens += count($res['invalid_ids']);
                }
            }
        }

        if ($data['target'] == 'drivers' || $data['target'] == 'all') {
            $drivers = $db->table('drivers')->select('id, fcm_token')->where('fcm_token !=', null)->where('fcm_token !=', '')->get()->getResult();
            $tokens = [];
            foreach ($drivers as $d) { $tokens[$d->id] = $d->fcm_token; }
            if (!empty($tokens)) {
                $res = $fcmService->sendPushNotificationBatch($tokens, $data['title'], $data['message'], ['type' => $data['type'], 'target' => 'driver']);
                $successCount += $res['success'];
                if (!empty($res['invalid_ids'])) {
                    $db->table('drivers')->whereIn('id', $res['invalid_ids'])->update(['fcm_token' => null]);
                    $cleanedTokens += count($res['invalid_ids']);
                }
            }
        }

        $infoMsg = "Pesan Broadcast berhasil disimpan dan dipancarkan ke aplikasi!";
        if ($cleanedTokens > 0) {
            $infoMsg .= " ($cleanedTokens token lawas yang tidak aktif telah dibersihkan otomatis).";
        }

        session()->setFlashdata('success', $infoMsg);

        return redirect()->to(base_url('admin/broadcast'));
    }
}
