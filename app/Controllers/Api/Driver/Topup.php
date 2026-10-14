<?php

namespace App\Controllers\Api\Driver;

use CodeIgniter\RESTful\ResourceController;

class Topup extends ResourceController
{
    protected $format = 'json';

    private function getMerchantCode()
    {
        return getenv('DUITKU_MERCHANT_CODE') ?: 'D22885';
    }

    private function getMerchantKey()
    {
        return getenv('DUITKU_API_KEY') ?: 'e6d639fa7d0ba6309b953aef61650d20';
    }

    private $apiUrl = 'https://api-prod.duitku.com/api/merchant/createinvoice';

    public function createInvoice()
    {
        $payload = $_SERVER['jwt_payload'];
        $amount = (int) $this->request->getVar('amount');

        // Rate limit invoice creation (Anti-Flooding): Maksimal 5 per 10 menit
        $throttler = \Config\Services::throttler();
        if ($throttler->check('topup_drv_inv_' . $payload['uid'], 5, 600) === false) {
            return $this->respond(['status' => 429, 'message' => 'Terlalu banyak permintaan tagihan. Silakan tunggu beberapa menit.'], 429);
        }

        $minAmount = 10000;
        if (!$amount || $amount < $minAmount) {
            return $this->respond(['status' => 400, 'message' => 'Minimal top-up Rp ' . number_format($minAmount, 0, ',', '.')], 400);
        }

        // Biaya Layanan Payment Gateway (0.9% untuk menutup MDR QRIS Duitku 0.7%)
        $adminFee = (int) ceil($amount * 0.009);
        $totalPayment = $amount + $adminFee;

        $merchantCode = $this->getMerchantCode();
        $merchantKey = $this->getMerchantKey();

        $db = \Config\Database::connect();
        $driver = $db->table('drivers')->where('id', $payload['uid'])->get()->getRow();

        if (!$driver) {
            return $this->respond(['status' => 404, 'message' => 'Pengemudi tidak ditemukan'], 404);
        }

        $merchantOrderId = 'MAIPAY-DRV-' . time() . '-' . $payload['uid'];

        // Buat Signature MD5 (Duitku format: merchantCode + merchantOrderId + paymentAmount + merchantKey)
        $signature = md5($merchantCode . $merchantOrderId . $totalPayment . $merchantKey);

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $totalPayment,
            'paymentMethod' => 'SP', // Langsung QRIS (ShopeePay QRIS / Standar QRIS Nasional)
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => 'Top-Up Saldo Dompet Driver (Rp ' . number_format($amount, 0, ',', '.') . ' + Fee Rp ' . number_format($adminFee, 0, ',', '.') . ')',
            'email' => $driver->email ?? 'driver@maijek.com',
            'phoneNumber' => $driver->phone ?? '',
            'customerVaName' => $driver->name ?? 'Maijek Driver',
            'callbackUrl' => base_url('api/callback/duitku'),
            'returnUrl' => base_url('api/callback/duitku_return'),
            'signature' => $signature,
            'expiryPeriod' => 1440
        ];

        $timestamp = round(microtime(true) * 1000);
        $signatureHeader = hash('sha256', $merchantCode . $timestamp . $merchantKey);

        // Call Duitku API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($params)),
            'x-duitku-signature: ' . $signatureHeader,
            'x-duitku-timestamp: ' . $timestamp,
            'x-duitku-merchantcode: ' . $merchantCode
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['statusCode']) && $result['statusCode'] == '00') {
            $db->table('topups')->insert([
                'user_id' => $payload['uid'],
                'user_type' => 'driver',
                'amount' => $amount,
                'merchant_order_id' => $merchantOrderId,
                'payment_url' => $result['paymentUrl'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status' => 200,
                'message' => 'Invoice berhasil dibuat',
                'data' => [
                    'payment_url' => $result['paymentUrl'],
                    'merchant_order_id' => $merchantOrderId,
                    'amount' => $amount,
                    'admin_fee' => $adminFee,
                    'total_payment' => $totalPayment
                ]
            ]);
        } else {
            return $this->respond([
                'status' => 500,
                'message' => 'Gagal membuat tagihan Duitku',
                'error_detail' => $result
            ], 500);
        }
    }
}
