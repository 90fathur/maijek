<?php

namespace App\Controllers\Api\Callback;

use CodeIgniter\RESTful\ResourceController;

class Duitku extends ResourceController
{
    protected $format = 'json';

    // Kredensial Duitku (Diambil dari .env secara aman dengan fallback)
    private function getMerchantCode()
    {
        return getenv('DUITKU_MERCHANT_CODE') ?: 'D22885';
    }

    private function getMerchantKey()
    {
        return getenv('DUITKU_API_KEY') ?: 'e6d639fa7d0ba6309b953aef61650d20';
    }

    // Fungsi ini dipanggil secara rahasia oleh Duitku Server (Sistem Robot)
    public function index()
    {
        $db = \Config\Database::connect();

        $merchantOrderId = trim((string)$this->request->getPost('merchantOrderId'));
        $amount = trim((string)$this->request->getPost('amount'));
        $merchantCode = trim((string)$this->request->getPost('merchantCode'));
        $signature = trim((string)$this->request->getPost('signature'));
        $reference = trim((string)$this->request->getPost('reference'));
        $resultCode = trim((string)$this->request->getPost('resultCode'));

        $configuredMerchantKey = $this->getMerchantKey();
        $configuredMerchantCode = $this->getMerchantCode();

        if (!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
            // Validasi bahwa merchant code cocok dengan konfigurasi kita
            if ($merchantCode !== $configuredMerchantCode) {
                log_message('error', 'Duitku Callback: Merchant Code mismatch.');
                return $this->response->setJSON(['status' => 'Unauthorized Merchant'])->setStatusCode(403);
            }

            $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $configuredMerchantKey);

            // Perbandingan waktu konstan (Anti-Timing Attack)
            if (hash_equals($calcSignature, $signature)) {
                $db->transStart();

                // Kunci baris topup dengan FOR UPDATE untuk mencegah race condition / multi callback
                $topup = $db->query("SELECT * FROM topups WHERE merchant_order_id = ? FOR UPDATE", [$merchantOrderId])->getRow();

                if ($topup && $topup->status === 'pending') {
                    if ($resultCode === '00') {
                        // Pembayaran SUKSES
                        // 1. Update status topup
                        $db->table('topups')->where('id', $topup->id)->update([
                            'status' => 'success',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        // 2. Tambah saldo ke dompet (Mai-Pay) secara atomik
                        if ($topup->user_type == 'driver') {
                            $db->table('drivers')->where('id', $topup->user_id)->set('balance', 'balance + ' . (float)$topup->amount, false)->update();
                            $user = $db->table('drivers')->where('id', $topup->user_id)->get()->getRow();
                        } elseif ($topup->user_type == 'merchant') {
                            $db->table('merchants')->where('id', $topup->user_id)->set('balance', 'balance + ' . (float)$topup->amount, false)->update();
                            $user = $db->table('merchants')->where('id', $topup->user_id)->get()->getRow();
                        } else {
                            $db->table('users')->where('id', $topup->user_id)->set('balance', 'balance + ' . (float)$topup->amount, false)->update();
                            $user = $db->table('users')->where('id', $topup->user_id)->get()->getRow();
                        }

                        $db->transComplete();

                        // 3. Kirim Notifikasi FCM (Opsional, agar HP pengguna berbunyi "Ting!")
                        if ($user && !empty($user->fcm_token)) {
                            try {
                                $fcmService = new \App\Libraries\FcmService();
                                $fcmService->sendPushNotification(
                                    $user->fcm_token,
                                    "Top-Up Berhasil! 💰",
                                    "Saldo Mai-Pay Anda bertambah Rp " . number_format($topup->amount, 0, ',', '.'),
                                    ['type' => 'topup_success']
                                );
                            } catch (\Throwable $e) {
                                log_message('error', 'FCM Topup error: ' . $e->getMessage());
                            }
                        }
                    } else {
                        // Pembayaran GAGAL atau KEDALUWARSA
                        $db->table('topups')->where('id', $topup->id)->update([
                            'status' => 'failed',
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        $db->transComplete();
                    }

                    return $this->response->setJSON(['status' => 'Success'])->setStatusCode(200);
                } else if ($topup && $topup->status === 'success') {
                    // Sudah diproses sebelumnya (Idempoten)
                    $db->transComplete();
                    return $this->response->setJSON(['status' => 'Already Processed'])->setStatusCode(200);
                }

                $db->transComplete();
            } else {
                log_message('error', 'Bad Signature in Duitku Callback');
                return $this->response->setJSON(['status' => 'Bad Signature'])->setStatusCode(400);
            }
        }

        return $this->response->setJSON(['status' => 'Bad Request'])->setStatusCode(400);
    }

    public function returnUrl()
    {
        // Tampilkan halaman sukses sederhana agar user bisa menutup browser
        $html = "
        <html>
        <head>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Pembayaran Selesai</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; padding: 50px 20px; background-color: #f8f9fa; }
                .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 400px; margin: 0 auto; }
                h2 { color: #28a745; margin-bottom: 10px; }
                p { color: #6c757d; font-size: 16px; margin-bottom: 25px; }
                .btn { display: inline-block; padding: 12px 25px; background-color: #004d40; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='card'>
                <h2>🎉 Pembayaran Selesai!</h2>
                <p>Transaksi Anda sedang diproses. Silakan kembali ke aplikasi Maijek untuk mengecek saldo Anda.</p>
                <!-- Tombol ini bisa dicoba diklik atau user langsung tutup webview/browser -->
                <a href='#' class='btn' onclick='window.close();'>Tutup Halaman Ini</a>
            </div>
        </body>
        </html>
        ";
        return $this->response->setBody($html)->setContentType('text/html');
    }
}
