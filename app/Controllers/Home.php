<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $merchantModel = new \App\Models\MerchantModel();
        $settingModel = new \App\Models\SettingModel();
        $driverModel = new \App\Models\DriverModel();
        $bannerModel = new \App\Models\BannerModel();

        // Ambil daftar mitra merchant resmi (approved)
        $merchants = $merchantModel->where('verification_status', 'approved')
            ->orderBy('is_open', 'DESC')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Ambil banner promo aktif jika ada
        $banners = $bannerModel->where('status', 'active')->findAll();

        // Ambil pengaturan tarif dan kontak
        $settingsRaw = $settingModel->findAll();
        $settings = [];
        foreach ($settingsRaw as $s) {
            $settings[$s['setting_key']] = $s['setting_value'];
        }

        $totalDrivers = $driverModel->where('verification_status', 'approved')->countAllResults();
        if ($totalDrivers < 30) {
            $totalDrivers = 38;
        }

        $data = [
            'title' => 'Maijek | Ojek Online, Mobil & Pesan Makanan Polewali Mandar',
            'merchants' => $merchants,
            'banners' => $banners,
            'settings' => $settings,
            'total_drivers' => $totalDrivers,
            'total_merchants' => count($merchants),
        ];

        return view('maijek_landing', $data);
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function demoCheckout()
    {
        $merchantCode = 'DS33894';
        $merchantKey = '03b769f62b88c9b2cca09ba7ceae6c0d';
        $apiUrl = 'https://api-sandbox.duitku.com/api/merchant/createinvoice';

        $merchantOrderId = 'DEMO-' . time();
        $amount = 10500;
        $timestamp = round(microtime(true) * 1000);
        $signature = hash('md5', $merchantCode . $merchantOrderId . $amount . $merchantKey);

        $params = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => 'Tes Layanan Mai-Car (Sandbox)',
            'email' => 'tester@duitku.com',
            'phoneNumber' => '085117228559',
            'customerVaName' => 'Penguji Duitku',
            'callbackUrl' => base_url('api/callback/duitku'),
            'returnUrl' => base_url(), // Return to landing page
            'signature' => $signature,
            'expiryPeriod' => 60
        ];

        $signatureHeader = hash('sha256', $merchantCode . $timestamp . $merchantKey);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
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

        $result = json_decode($response);

        if (isset($result->paymentUrl)) {
            return redirect()->to($result->paymentUrl);
        } else {
            return "Gagal menghubungkan ke Sandbox Duitku. Error: " . ($result->statusMessage ?? 'Unknown Error');
        }
    }
    public function checkDb()
    {
        $db = \Config\Database::connect();

        echo "<h1>DB Debug Info</h1>";
        echo "Current Server Time (PHP): " . date('Y-m-d H:i:s') . "<br>";

        $dbTime = $db->query("SELECT NOW() as db_time")->getRow()->db_time;
        echo "Current DB Time (MySQL): " . $dbTime . "<br><br>";

        echo "<h2>Merchants Status (Live Server)</h2>";
        $merchants = $db->table('merchants')->get()->getResultArray();
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nama</th><th>Phone</th><th>is_open</th><th>Verification</th><th>Hours</th><th>Days</th><th>Last Active</th></tr>";
        foreach ($merchants as $m) {
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td><b>" . htmlspecialchars($m['name']) . "</b></td>";
            echo "<td>{$m['phone']}</td>";
            echo "<td style='background:" . ($m['is_open'] == 1 ? '#dcfce7' : '#fee2e2') . "'><b>" . ($m['is_open'] == 1 ? 'BUKA (1)' : 'TUTUP (0)') . "</b></td>";
            echo "<td>" . ($m['verification_status'] ?? '-') . "</td>";
            echo "<td>" . ($m['opening_time'] ?? '-') . " s/d " . ($m['closing_time'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($m['operating_days'] ?? '-') . "</td>";
            echo "<td>" . ($m['last_active_at'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";

        echo "<h2>Last 10 Completed Mai-Ride Orders</h2>";
        $orders = $db->table('orders')->where('status', 'completed')->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();

        if (empty($orders)) {
            echo "<p>No completed orders found in 'orders' table.</p>";
        } else {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Driver ID</th><th>Status</th><th>Price</th><th>Created At</th><th>Updated At</th><th>Completed At</th></tr>";
            foreach ($orders as $o) {
                echo "<tr>";
                echo "<td>{$o['id']}</td>";
                echo "<td>{$o['driver_id']}</td>";
                echo "<td>{$o['status']}</td>";
                echo "<td>{$o['price']}</td>";
                echo "<td>{$o['created_at']}</td>";
                echo "<td>{$o['updated_at']}</td>";
                echo "<td>{$o['completed_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        echo "<h2>Last 10 Completed Mai-Food Orders</h2>";
        $foods = $db->table('food_orders')->where('status', 'completed')->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();

        if (empty($foods)) {
            echo "<p>No completed orders found in 'food_orders' table.</p>";
        } else {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Driver ID</th><th>Status</th><th>Delivery Fee</th><th>Created At</th><th>Updated At</th></tr>";
            foreach ($foods as $o) {
                echo "<tr>";
                echo "<td>{$o['id']}</td>";
                echo "<td>{$o['driver_id']}</td>";
                echo "<td>{$o['status']}</td>";
                echo "<td>{$o['delivery_fee']}</td>";
                echo "<td>{$o['created_at']}</td>";
                echo "<td>{$o['updated_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}
