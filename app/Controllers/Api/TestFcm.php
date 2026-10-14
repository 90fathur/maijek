<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class TestFcm extends ResourceController
{
    public function index()
    {
        $db = \Config\Database::connect();
        // Bersihkan token dummy jika ada
        $db->table('users')->where('fcm_token', 'dummy_test_fcm_token_zoel')->update(['fcm_token' => null]);
        
        $credPath = ROOTPATH . 'firebase-adminsdk.json';
        $credExists = file_exists($credPath);
        
        $tokensCount = $db->table('users')->where('fcm_token !=', null)->where('fcm_token !=', '')->countAllResults();
        $recentUsers = $db->table('users')->select('id, name, phone, fcm_token')->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();

        $fcmService = new \App\Libraries\FcmService();
        $sendResult = null;
        $error = null;

        $userWithToken = $db->table('users')->where('fcm_token !=', null)->where('fcm_token !=', '')->orderBy('id', 'DESC')->get()->getRow();
        if ($userWithToken && $credExists) {
            try {
                $sendResult = $fcmService->sendPushNotification(
                    $userWithToken->fcm_token,
                    "🔔 Tes Notifikasi Mengambang",
                    "Ini adalah tes push notification mengambang dari Maijek!",
                    ['type' => 'promo']
                );
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->respond([
            'firebase_adminsdk_exists' => $credExists,
            'cred_path' => $credPath,
            'users_with_token_count' => $tokensCount,
            'test_user_target' => $userWithToken ? ['id' => $userWithToken->id, 'name' => $userWithToken->name, 'has_token' => !empty($userWithToken->fcm_token)] : null,
            'send_result' => $sendResult,
            'error' => $error,
            'recent_users' => $recentUsers
        ]);
    }
}
