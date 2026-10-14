<?php

namespace App\Libraries;

use Google\Client as GoogleClient;

class FcmService
{
    private $projectId;
    private $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = ROOTPATH . 'firebase-adminsdk.json';
        if (file_exists($this->credentialsPath)) {
            $json = json_decode(file_get_contents($this->credentialsPath), true);
            $this->projectId = $json['project_id'] ?? null;
        }
    }

    private static $cachedAccessToken = null;
    private static $tokenExpiresAt = 0;

    private function getAccessToken()
    {
        if (self::$cachedAccessToken && time() < self::$tokenExpiresAt) {
            return self::$cachedAccessToken;
        }

        if (!file_exists($this->credentialsPath)) {
            log_message('error', 'FCM Error: firebase-adminsdk.json not found in ' . $this->credentialsPath);
            return null;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();
            $token = $client->fetchAccessTokenWithAssertion();
            if (!empty($token['access_token'])) {
                self::$cachedAccessToken = $token['access_token'];
                self::$tokenExpiresAt = time() + 3000; // Cache 50 menit
                return self::$cachedAccessToken;
            }
            return null;
        } catch (\Exception $e) {
            log_message('error', 'FCM Token Error: ' . $e->getMessage());
            return null;
        }
    }

    public function sendPushNotification($fcmToken, $title, $body, $data = [])
    {
        if (empty($fcmToken)) return false;

        $accessToken = $this->getAccessToken();
        if (!$accessToken || !$this->projectId) return false;

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $stringData = [];
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                $stringData[(string)$k] = (string)$v;
            }
        }

        $isBroadcast = isset($data['type']) && in_array($data['type'], ['system', 'promo', 'info']);
        $target = $data['target'] ?? '';
        $type = $data['type'] ?? '';
        $isNewOrderAlert = in_array($type, ['new_order', 'new_food_order', 'driver_order_request', 'merchant_new_order']);

        $channelId = $data['channel_id'] ?? null;
        $sound = $data['sound'] ?? null;

        if (!$channelId) {
            // Gunakan nada dering pesanan masuk HANYA untuk notifikasi pesanan baru
            if ($isNewOrderAlert) {
                $channelId = 'maifood_order_channel_v2';
                $sound = 'order_ringtone';
            } else {
                // Untuk selain pesanan baru (broadcast, promo, pengumuman, chat) selalu gunakan nada dering standar
                $channelId = 'high_importance_channel';
                $sound = 'default';
            }
        }

        $androidNotification = [
            'default_vibrate_timings' => true,
            'channel_id' => $channelId,
            'notification_priority' => $isNewOrderAlert ? 'PRIORITY_MAX' : 'PRIORITY_HIGH',
            'visibility' => 'PUBLIC',
        ];

        $apnsAps = [
            'content-available' => 1
        ];

        if ($sound === 'order_ringtone') {
            $androidNotification['sound'] = 'order_ringtone';
            $androidNotification['default_sound'] = false;
            $apnsAps['sound'] = 'order_ringtone.wav';
        } else {
            $androidNotification['sound'] = 'default';
            $androidNotification['default_sound'] = true;
            $apnsAps['sound'] = 'default';
        }

        // Add title and body into data just in case
        $stringData['title'] = (string) $title;
        $stringData['body'] = (string) $body;

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => (string) $title,
                    'body' => (string) $body
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => $androidNotification
                ],
                'apns' => [
                    'payload' => [
                        'aps' => $apnsAps
                    ]
                ]
            ]
        ];

        file_put_contents(ROOTPATH . 'fcm_payload_dump.txt', json_encode($payload, JSON_PRETTY_PRINT));

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            log_message('error', 'FCM cURL Error: ' . $err);
            return false;
        }

        $resDecoded = json_decode($result, true);
        if ($httpCode != 200 || isset($resDecoded['error'])) {
            $errorMsg = json_encode($resDecoded['error'] ?? $result);
            log_message('error', 'FCM API Error: ' . $errorMsg);
            return false;
        }

        return true;
    }

    public function sendPushNotificationBatch(array $tokensWithIds, $title, $body, $data = [])
    {
        if (empty($tokensWithIds)) return ['success' => 0, 'invalid_ids' => []];

        $accessToken = $this->getAccessToken();
        if (!$accessToken || !$this->projectId) return ['success' => 0, 'invalid_ids' => []];

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $stringData = [];
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                $stringData[(string)$k] = (string)$v;
            }
        }
        $stringData['title'] = (string) $title;
        $stringData['body'] = (string) $body;

        $target = $data['target'] ?? '';
        $type = $data['type'] ?? '';
        $isNewOrderAlert = in_array($type, ['new_order', 'new_food_order', 'driver_order_request', 'merchant_new_order']);
        $channelId = $data['channel_id'] ?? ($isNewOrderAlert ? 'maifood_order_channel_v2' : 'high_importance_channel');
        $sound = ($channelId === 'maifood_order_channel_v2') ? 'order_ringtone' : 'default';

        $androidNotification = [
            'default_vibrate_timings' => true,
            'channel_id' => $channelId,
            'sound' => $sound,
            'default_sound' => ($sound === 'default'),
            'notification_priority' => $isNewOrderAlert ? 'PRIORITY_MAX' : 'PRIORITY_HIGH',
            'visibility' => 'PUBLIC',
        ];

        $batches = array_chunk($tokensWithIds, 30, true);
        $totalSuccess = 0;
        $invalidIds = [];

        foreach ($batches as $batch) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($batch as $id => $token) {
                if (empty($token)) continue;

                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => (string) $title,
                            'body' => (string) $body
                        ],
                        'data' => $stringData,
                        'android' => [
                            'priority' => 'HIGH',
                            'notification' => $androidNotification
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'content-available' => 1
                                ]
                            ]
                        ]
                    ]
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                curl_multi_add_handle($mh, $ch);
                $handles[(int)$ch] = ['ch' => $ch, 'id' => $id, 'token' => $token];
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh, 0.05);
            } while ($running > 0);

            foreach ($handles as $item) {
                $ch = $item['ch'];
                $response = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode == 200) {
                    $totalSuccess++;
                } else {
                    $resDecoded = json_decode($response, true);
                    $errMsg = json_encode($resDecoded['error'] ?? $response);
                    if (
                        strpos($errMsg, 'SenderId mismatch') !== false ||
                        strpos($errMsg, 'NotRegistered') !== false ||
                        strpos($errMsg, 'UNREGISTERED') !== false ||
                        strpos($errMsg, 'INVALID_ARGUMENT') !== false
                    ) {
                        $invalidIds[] = $item['id'];
                    }
                }

                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }

            curl_multi_close($mh);
        }

        return [
            'success' => $totalSuccess,
            'invalid_ids' => $invalidIds
        ];
    }

    public function sendToTopic($topic, $title, $body, $data = [])
    {
        if (empty($topic)) return false;

        $accessToken = $this->getAccessToken();
        if (!$accessToken || !$this->projectId) return false;

        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';

        $stringData = [];
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                $stringData[(string)$k] = (string)$v;
            }
        }
        $stringData['title'] = (string) $title;
        $stringData['body'] = (string) $body;

        $target = $data['target'] ?? '';
        $type = $data['type'] ?? '';
        $isNewOrderAlert = in_array($type, ['new_order', 'new_food_order', 'driver_order_request', 'merchant_new_order']);
        $channelId = $data['channel_id'] ?? ($isNewOrderAlert ? 'maifood_order_channel_v2' : 'high_importance_channel');
        $sound = ($channelId === 'maifood_order_channel_v2') ? 'order_ringtone' : 'default';

        $payload = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => (string) $title,
                    'body' => (string) $body
                ],
                'data' => $stringData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => $sound,
                        'channel_id' => $channelId,
                        'default_vibrate_timings' => true,
                        'default_sound' => ($sound === 'default'),
                        'notification_priority' => $isNewOrderAlert ? 'PRIORITY_MAX' : 'PRIORITY_HIGH',
                        'visibility' => 'PUBLIC',
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'content-available' => 1
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', 'FCM Topic Error: ' . $err);
            return false;
        }

        $resDecoded = json_decode($result, true);
        if (isset($resDecoded['error'])) {
            log_message('error', 'FCM Topic API Error: ' . json_encode($resDecoded['error']));
            return false;
        }

        return true;
    }

    /**
     * Kirim Push Notification ke Penumpang sekaligus simpan otomatis ke Kotak Masuk (Inbox)
     */
    public function sendToUserWithInbox($userId, $title, $body, $data = [])
    {
        $db = \Config\Database::connect();

        // 1. Simpan ke Kotak Masuk (Database notifications)
        try {
            $db->table('notifications')->insert([
                'user_id' => (int)$userId,
                'title' => (string)$title,
                'message' => (string)$body,
                'target' => 'users',
                'type' => $data['type'] ?? 'transaction',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Failed inserting user notification: ' . $e->getMessage());
        }

        // 2. Kirim Push Notification FCM ke HP Penumpang
        $user = $db->table('users')->select('fcm_token')->where('id', (int)$userId)->get()->getRow();
        if ($user && !empty($user->fcm_token)) {
            $data['target'] = 'user';
            return $this->sendPushNotification($user->fcm_token, $title, $body, $data);
        }
        return false;
    }
}
