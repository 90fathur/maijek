<?php

if (!function_exists('get_otp_key')) {
    function get_otp_key($key)
    {
        try {
            $settingModel = new \App\Models\SettingModel();
            $dbVal = $settingModel->where('setting_key', strtolower($key))->first();
            if ($dbVal && !empty($dbVal['setting_value'])) {
                return $dbVal['setting_value'];
            }
        } catch (\Exception $e) {
        }

        return env(strtoupper($key));
    }
}

if (!function_exists('format_phone_international')) {
    function format_phone_international($phone)
    {
        $clean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            return '62' . $clean;
        } elseif (str_starts_with($clean, '62')) {
            return $clean;
        }
        return $clean;
    }
}

if (!function_exists('send_cloudchat_otp')) {
    function send_cloudchat_otp($phone, $otp)
    {
        $apiKey = get_otp_key('cloudchat_api_key');
        if (empty($apiKey)) {
            log_message('error', 'Cloudchat API Key not set');
            return false;
        }

        // Format phone number to start with 62 instead of 0, 8, or +62
        $phone = format_phone_international($phone);

        $url = 'https://app.cloudchat.id/api/public/v1/messages';
        $message = "*$otp* adalah kode verifikasi OTP Anda. Demi keamanan, jangan berikan kode ini kepada siapapun.";

        $data = [
            'channel' => 'whatsapp',
            'type'    => 'text',
            'to'      => $phone,
            'content' => [
                'text' => $message,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($httpCode == 200 || $httpCode == 201) {
            $responseData = json_decode($response, true);
            if (isset($responseData['success']) && $responseData['success'] === false) {
                log_message('error', 'Cloudchat API Soft Error: ' . $response);
                return false;
            }
            return true;
        } else {
            log_message('error', 'Cloudchat API Error HTTP ' . $httpCode . ' (' . $curlErr . '): ' . $response);
            return false;
        }
    }
}

if (!function_exists('send_bablast_otp')) {
    function send_bablast_otp($phone, $otp)
    {
        $apiKey = get_otp_key('bablast_api_key');
        if (empty($apiKey)) {
            log_message('error', 'Bablast API Key not set');
            return false;
        }

        // Format phone number to start with 62 instead of 0, 8, or +62
        $phone = format_phone_international($phone);

        $url = 'https://api.bablast.id/waba/send-template';
        $data = [
            'phone' => $phone,
            'template_name' => 'verifikasi_otp',
            'language' => 'id',
            'parameters' => [(int)$otp]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 || $httpCode == 201) {
            $responseData = json_decode($response, true);
            if (isset($responseData['success']) && $responseData['success'] === false) {
                log_message('error', 'Bablast API Soft Error: ' . $response);
                return false;
            }
            return true;
        } else {
            log_message('error', 'Bablast API Error HTTP ' . $httpCode . ': ' . $response);
            return false;
        }
    }
}

if (!function_exists('send_whatsapp_otp')) {
    function send_whatsapp_otp($phone, $otp)
    {
        // 1. Coba Cloudchat jika API Key terisi
        $cloudchatKey = get_otp_key('cloudchat_api_key');
        if (!empty($cloudchatKey)) {
            if (send_cloudchat_otp($phone, $otp)) {
                return true;
            }
            log_message('info', 'Cloudchat gagal/error -> Fallback ke gateway cadangan');
        }

        // 2. Coba Bablast WABA jika API Key terisi
        $bablastKey = get_otp_key('bablast_api_key');
        if (!empty($bablastKey)) {
            if (send_bablast_otp($phone, $otp)) {
                return true;
            }
            log_message('info', 'Bablast gagal/error -> Fallback ke Whapify');
        }

        // 3. Coba Whapify jika API Secret & Account ID terisi
        $whapifySecret = get_otp_key('whapify_api_secret');
        if (!empty($whapifySecret)) {
            if (send_whapify_otp($phone, $otp)) {
                return true;
            }
            log_message('info', 'Whapify gagal/error -> Fallback ke Fonnte');
        }

        // 4. Coba Fonnte sebagai fallback terakhir
        return send_fonnte_otp($phone, $otp);
    }
}

if (!function_exists('send_whapify_otp')) {
    function send_whapify_otp($phone, $otp)
    {
        $secret = get_otp_key('whapify_api_secret');
        $account = get_otp_key('whapify_account_id');

        if (empty($secret) || empty($account)) {
            log_message('error', 'Whapify credentials not set');
            return false;
        }

        // Format phone number to start with 62 instead of 0, 8, or +62
        $phone = format_phone_international($phone);

        $url = 'https://whapify.id/api/send/whatsapp';
        $message = "*$otp* adalah kode verifikasi Anda. Demi keamanan, jangan bagikan kode ini.";

        $postData = [
            'secret' => $secret,
            'account' => $account,
            'recipient' => $phone,
            'type' => 'text',
            'message' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        // Passing array directly forces multipart/form-data in PHP cURL
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 || $httpCode == 201) {
            $responseData = json_decode($response, true);
            if (isset($responseData['status']) && $responseData['status'] === false) {
                log_message('error', 'Whapify API Soft Error: ' . $response . ' -> Fallback to Fonnte');
                return send_fonnte_otp($phone, $otp);
            }
            return true;
        } else {
            log_message('error', 'Whapify API Error HTTP ' . $httpCode . ': ' . $response . ' -> Fallback to Fonnte');
            return send_fonnte_otp($phone, $otp);
        }
    }
}

if (!function_exists('send_fonnte_otp')) {
    function send_fonnte_otp($phone, $otp)
    {
        $token = get_otp_key('fonnte_api_key');

        if (empty($token)) {
            log_message('error', 'Fonnte token not set');
            return false;
        }

        // Format phone number to start with 62
        $phone = format_phone_international($phone);

        $url = 'https://api.fonnte.com/send';
        $message = "*$otp* adalah kode verifikasi Anda. Demi keamanan, jangan bagikan kode ini.";

        $postData = [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62',
            'typing' => false,
            'delay' => '2'
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token
            ),
            CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['status']) && $responseData['status'] === false) {
                log_message('error', 'Fonnte API Soft Error: ' . $response);
                return false;
            }
            return true;
        } else {
            log_message('error', 'Fonnte API Error HTTP ' . $httpCode . ': ' . $response);
            return false;
        }
    }
}
