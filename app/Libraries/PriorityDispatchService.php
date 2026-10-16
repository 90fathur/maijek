<?php

namespace App\Libraries;

use Config\Database;
use App\Libraries\FcmService;

/**
 * PriorityDispatchService
 * 
 * Mengelola sistem pengiriman pesanan ke Driver Prioritas terlebih dahulu:
 * 1. Pesanan baru diprioritaskan ke Driver Prioritas (is_priority = 1) terdekat.
 * 2. Menunggu respon selama 7-10 detik (dapat diatur di Pengaturan Admin).
 * 3. Jika driver prioritas tidak merespon / melewati pesanan, sistem otomatis
 *    menyiarkan (fallback broadcast) ke seluruh driver terdekat lainnya.
 * 4. Murni berjalan di server-side backend tanpa perlu merubah APK / rilis ulang aplikasi.
 */
class PriorityDispatchService
{
    protected $db;
    protected $fcmService;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->fcmService = new FcmService();
    }

    /**
     * Cek apakah sistem Driver Prioritas sedang aktif
     */
    public function isPriorityEnabled(): bool
    {
        try {
            $row = $this->db->table('settings')->where('setting_key', 'priority_dispatch_enabled')->get()->getRow();
            if ($row) {
                return (string)$row->setting_value === '1';
            }
        } catch (\Throwable $e) {}
        return true; // Default aktif
    }

    /**
     * Ambil durasi waktu tunggu respon driver prioritas dalam detik (default 10 detik)
     */
    public function getPriorityTimeout(): int
    {
        try {
            $row = $this->db->table('settings')->where('setting_key', 'priority_dispatch_timeout')->get()->getRow();
            if ($row && is_numeric($row->setting_value)) {
                $val = (int)$row->setting_value;
                if ($val >= 3 && $val <= 60) {
                    return $val;
                }
            }
        } catch (\Throwable $e) {}
        return 10; // Default 10 detik
    }

    /**
     * Cari Driver Terdekat Sesuai Radius dan Koordinat
     */
    public function getNearbyDrivers(float $lat, float $lng, float $radius, bool $isCar = false, int $limit = 25): array
    {
        $builder = $this->db->table('drivers');
        $builder->select('drivers.*, (
            6371 * acos(
                LEAST(1.0, cos(radians(' . $this->db->escape($lat) . ')) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(' . $this->db->escape($lng) . ')) + 
                sin(radians(' . $this->db->escape($lat) . ')) * 
                sin(radians(latitude)))
            )
        ) AS distance');

        $builder->where('is_online', 1);
        $builder->where('status', 'active');
        $builder->where('verification_status', 'approved');
        $builder->where('fcm_token !=', null);
        $builder->where('fcm_token !=', '');

        // Filter Heartbeat agar tidak kirim ke driver offline/zombie (toleransi 90 menit untuk driver mangkal)
        try {
            if ($this->db->fieldExists('last_heartbeat_at', 'drivers')) {
                $builder->where('last_heartbeat_at >=', date('Y-m-d H:i:s', strtotime('-90 minutes')));
            }
        } catch (\Throwable $e) {}

        if ($isCar) {
            $builder->whereIn('vehicle_type', ['mobil', 'Mobil', 'MOBIL', 'car', 'Car', 'car_premium']);
        } else {
            $builder->whereIn('vehicle_type', ['motor', 'Motor', 'MOTOR']);
        }

        $builder->having('distance <=', $radius);
        $builder->orderBy('distance', 'ASC');
        $builder->limit($limit);

        return $builder->get()->getResult();
    }

    /**
     * Dispatch pesanan Ride / Send / Titip:
     * Mengirim notifikasi ke Driver Prioritas dulu jika ada, atau langsung ke semua jika tidak ada.
     */
    public function dispatchRideOrder(int $orderId, array $orderData, float $pickupLat, float $pickupLng, string $serviceType): array
    {
        $settingModel = new \App\Models\SettingModel();
        $rawService = strtolower($serviceType ?? 'motor');

        if (strpos($rawService, 'titip') !== false) {
            $radius = (float)($settingModel->where('setting_key', 'search_radius_titip')->first()['setting_value'] ?? 5.0);
        } else if (strpos($rawService, 'send') !== false) {
            $radius = (float)($settingModel->where('setting_key', 'search_radius_send')->first()['setting_value'] ?? 5.0);
        } else {
            $radius = (float)($settingModel->where('setting_key', 'search_radius_ride')->first()['setting_value'] ?? 5.0);
        }

        $isCar = in_array(strtolower($serviceType), ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        $nearbyDrivers = $this->getNearbyDrivers($pickupLat, $pickupLng, $radius, $isCar);

        if (empty($nearbyDrivers)) {
            log_message('error', "PriorityDispatch: Tidak ada driver terdekat ditemukan untuk Order #{$orderId}");
            return ['status' => 'no_drivers', 'count' => 0];
        }

        $isPriorityEnabled = $this->isPriorityEnabled();
        $priorityDrivers = [];
        $regularDrivers = [];

        foreach ($nearbyDrivers as $driver) {
            if (!empty($driver->is_priority) && (int)$driver->is_priority === 1) {
                $priorityDrivers[] = $driver;
            } else {
                $regularDrivers[] = $driver;
            }
        }

        // Pastikan kolom database tersedia
        $this->ensurePriorityColumns();

        if ($isPriorityEnabled && !empty($priorityDrivers)) {
            // KIRIM HANYA KE DRIVER PRIORITAS
            $targetDrivers = $priorityDrivers;
            $this->db->table('orders')->where('id', $orderId)->update([
                'priority_dispatched_at' => date('Y-m-d H:i:s'),
                'fallback_dispatched_at' => null,
            ]);

            $this->sendRidePush($targetDrivers, $orderId, true);
            log_message('info', "PriorityDispatch: Order #{$orderId} dikirim ke " . count($priorityDrivers) . " Driver Prioritas ⭐");

            return [
                'status'         => 'dispatched_priority',
                'priority_count' => count($priorityDrivers),
                'regular_count'  => count($regularDrivers),
                'timeout'        => $this->getPriorityTimeout()
            ];
        } else {
            // KIRIM KE SEMUA DRIVER TERDEKAT
            $this->db->table('orders')->where('id', $orderId)->update([
                'priority_dispatched_at' => null,
                'fallback_dispatched_at' => date('Y-m-d H:i:s'),
            ]);

            $this->sendRidePush($nearbyDrivers, $orderId, false);
            log_message('info', "PriorityDispatch: Order #{$orderId} langsung disebarkan ke " . count($nearbyDrivers) . " driver (tanpa prioritas)");

            return [
                'status' => 'dispatched_all',
                'count'  => count($nearbyDrivers)
            ];
        }
    }

    /**
     * Cek apakah order masih menunggu driver prioritas dan apakah timeout sudah habis.
     * Jika timeout sudah habis, sebar otomatis ke seluruh driver lain!
     */
    public function checkAndTriggerRideFallback(int $orderId): bool
    {
        $order = $this->db->table('orders')->where('id', $orderId)->get()->getRow();
        if (!$order) return false;

        // Hanya proses jika order masih pending (belum diambil siapa pun)
        if ($order->status !== 'pending' || !empty($order->driver_id)) {
            return false;
        }

        // Cek apakah order dalam mode prioritas dan belum pernah disebarkan fallback-nya
        if (empty($order->priority_dispatched_at) || !empty($order->fallback_dispatched_at)) {
            return false;
        }

        $elapsedSeconds = time() - strtotime($order->priority_dispatched_at);
        $timeout = $this->getPriorityTimeout();

        if ($elapsedSeconds < $timeout) {
            // Masih dalam jendela prioritas, jangan sebar dulu
            return false;
        }

        // Timeout tercapai! Tandai fallback_dispatched_at sekarang agar tidak double-broadcast
        $this->db->table('orders')->where('id', $orderId)->update([
            'fallback_dispatched_at' => date('Y-m-d H:i:s')
        ]);

        // Broadcast ke semua driver terdekat
        $pickupLat = (float)($order->pickup_lat ?? 0);
        $pickupLng = (float)($order->pickup_lng ?? 0);
        $serviceType = $order->service_type ?? 'motor';

        $settingModel = new \App\Models\SettingModel();
        $rawService = strtolower($serviceType);

        if (strpos($rawService, 'titip') !== false) {
            $radius = (float)($settingModel->where('setting_key', 'search_radius_titip')->first()['setting_value'] ?? 5.0);
        } else if (strpos($rawService, 'send') !== false) {
            $radius = (float)($settingModel->where('setting_key', 'search_radius_send')->first()['setting_value'] ?? 5.0);
        } else {
            $radius = (float)($settingModel->where('setting_key', 'search_radius_ride')->first()['setting_value'] ?? 5.0);
        }

        $isCar = in_array(strtolower($serviceType), ['mobil', 'car', 'car_premium', 'mai_send_mobil', 'mai_titip_mobil']);
        $nearbyDrivers = $this->getNearbyDrivers($pickupLat, $pickupLng, $radius, $isCar);

        if (!empty($nearbyDrivers)) {
            $this->sendRidePush($nearbyDrivers, $orderId, false);
            log_message('info', "PriorityDispatch: Fallback broadcast untuk Order #{$orderId} ke " . count($nearbyDrivers) . " driver setelah {$elapsedSeconds}s");
        }

        return true;
    }

    /**
     * Dispatch pesanan Food / Mart:
     * Mengirim notifikasi ke Driver Prioritas dulu jika ada, atau ke semua.
     */
    public function dispatchFoodOrder(int $foodOrderId, array $order, object $merchant, string $status, bool $isMart): array
    {
        if (empty($merchant->latitude) || empty($merchant->longitude)) {
            return ['status' => 'no_merchant_coord'];
        }

        $pickupLat = (float)$merchant->latitude;
        $pickupLng = (float)$merchant->longitude;

        $radiusKey = $isMart ? 'search_radius_mart' : 'search_radius_food';
        $radiusRow = $this->db->table('settings')->where('setting_key', $radiusKey)->get()->getRow();
        $radius = $radiusRow ? (float)$radiusRow->setting_value : 15.0;

        $targetVehicle = strtolower($order['vehicle_type'] ?? 'motor');
        $isCar = in_array($targetVehicle, ['mobil', 'car']);

        $nearbyDrivers = $this->getNearbyDrivers($pickupLat, $pickupLng, $radius, $isCar);
        if (empty($nearbyDrivers)) {
            return ['status' => 'no_drivers', 'count' => 0];
        }

        $isPriorityEnabled = $this->isPriorityEnabled();
        $priorityDrivers = [];
        $regularDrivers = [];

        foreach ($nearbyDrivers as $driver) {
            if (!empty($driver->is_priority) && (int)$driver->is_priority === 1) {
                $priorityDrivers[] = $driver;
            } else {
                $regularDrivers[] = $driver;
            }
        }

        $this->ensurePriorityColumns();

        if ($isPriorityEnabled && !empty($priorityDrivers)) {
            $this->db->table('food_orders')->where('id', $foodOrderId)->update([
                'priority_dispatched_at' => date('Y-m-d H:i:s'),
                'fallback_dispatched_at' => null,
            ]);

            $this->sendFoodPush($priorityDrivers, $foodOrderId, $merchant->name, $targetVehicle, $status, $isMart, true);
            log_message('info', "PriorityDispatch: Food Order #{$foodOrderId} dikirim ke " . count($priorityDrivers) . " Driver Prioritas ⭐");

            return [
                'status'         => 'dispatched_priority',
                'priority_count' => count($priorityDrivers),
                'regular_count'  => count($regularDrivers),
                'timeout'        => $this->getPriorityTimeout()
            ];
        } else {
            $this->db->table('food_orders')->where('id', $foodOrderId)->update([
                'priority_dispatched_at' => null,
                'fallback_dispatched_at' => date('Y-m-d H:i:s'),
            ]);

            $this->sendFoodPush($nearbyDrivers, $foodOrderId, $merchant->name, $targetVehicle, $status, $isMart, false);
            log_message('info', "PriorityDispatch: Food Order #{$foodOrderId} langsung disebarkan ke " . count($nearbyDrivers) . " driver");

            return [
                'status' => 'dispatched_all',
                'count'  => count($nearbyDrivers)
            ];
        }
    }

    /**
     * Cek dan trigger fallback untuk pesanan Food / Mart
     */
    public function checkAndTriggerFoodFallback(int $foodOrderId): bool
    {
        $order = $this->db->table('food_orders')->where('id', $foodOrderId)->get()->getRow();
        if (!$order) return false;

        if (!empty($order->driver_id) || !in_array($order->status, ['processing', 'accepted_by_merchant', 'ready', 'pending'])) {
            return false;
        }

        if (empty($order->priority_dispatched_at) || !empty($order->fallback_dispatched_at)) {
            return false;
        }

        $elapsedSeconds = time() - strtotime($order->priority_dispatched_at);
        $timeout = $this->getPriorityTimeout();

        if ($elapsedSeconds < $timeout) {
            return false;
        }

        // Tandai fallback sudah disebarkan
        $this->db->table('food_orders')->where('id', $foodOrderId)->update([
            'fallback_dispatched_at' => date('Y-m-d H:i:s')
        ]);

        $merchant = $this->db->table('merchants')->where('id', $order->merchant_id)->get()->getRow();
        if ($merchant && !empty($merchant->latitude) && !empty($merchant->longitude)) {
            $isMart = (isset($order->order_type) && $order->order_type === 'mart');
            $radiusKey = $isMart ? 'search_radius_mart' : 'search_radius_food';
            $radiusRow = $this->db->table('settings')->where('setting_key', $radiusKey)->get()->getRow();
            $radius = $radiusRow ? (float)$radiusRow->setting_value : 15.0;

            $targetVehicle = strtolower($order->vehicle_type ?? 'motor');
            $isCar = in_array($targetVehicle, ['mobil', 'car']);

            $nearbyDrivers = $this->getNearbyDrivers((float)$merchant->latitude, (float)$merchant->longitude, $radius, $isCar);
            if (!empty($nearbyDrivers)) {
                $this->sendFoodPush($nearbyDrivers, $foodOrderId, $merchant->name, $targetVehicle, $order->status, $isMart, false);
                log_message('info', "PriorityDispatch: Food fallback broadcast untuk Order #{$foodOrderId} ke " . count($nearbyDrivers) . " driver setelah {$elapsedSeconds}s");
            }
        }

        return true;
    }

    /**
     * Memeriksa apakah suatu order boleh dilihat oleh driver saat driver membuka /driver/orders/available
     */
    public function isOrderVisibleToDriver(array $order, array $driver): bool
    {
        // Jika driver adalah driver prioritas, selalu boleh lihat
        if (!empty($driver['is_priority']) && (int)$driver['is_priority'] === 1) {
            return true;
        }

        // Jika order belum pernah masuk fase prioritas, boleh lihat
        if (empty($order['priority_dispatched_at'])) {
            return true;
        }

        // Jika sudah di-fallback, boleh lihat
        if (!empty($order['fallback_dispatched_at'])) {
            return true;
        }

        // Jika masih dalam jendela prioritas, driver non-prioritas belum boleh melihatnya
        $elapsed = time() - strtotime($order['priority_dispatched_at']);
        $timeout = $this->getPriorityTimeout();

        if ($elapsed < $timeout) {
            return false;
        }

        // Timeout sudah habis! Jalankan fallback broadcast di background dan izinkan supir ini melihatnya
        $this->checkAndTriggerRideFallback((int)$order['id']);
        return true;
    }

    /**
     * Memeriksa apakah food order boleh dilihat driver di /driver/food/available
     */
    public function isFoodOrderVisibleToDriver(array $order, array $driver): bool
    {
        if (!empty($driver['is_priority']) && (int)$driver['is_priority'] === 1) {
            return true;
        }

        if (empty($order['priority_dispatched_at'])) {
            return true;
        }

        if (!empty($order['fallback_dispatched_at'])) {
            return true;
        }

        $elapsed = time() - strtotime($order['priority_dispatched_at']);
        $timeout = $this->getPriorityTimeout();

        if ($elapsed < $timeout) {
            return false;
        }

        $this->checkAndTriggerFoodFallback((int)$order['id']);
        return true;
    }

    /**
     * Kirim FCM Ride ke kumpulan driver
     */
    private function sendRidePush(array $drivers, int $orderId, bool $isPriority): void
    {
        try {
            $title = $isPriority ? "Order Baru Masuk! ⭐ (Prioritas)" : "Order Baru Masuk!";
            foreach ($drivers as $driver) {
                if (!empty($driver->fcm_token)) {
                    $distKm = isset($driver->distance) ? round($driver->distance, 1) : null;
                    $body = $distKm !== null
                        ? "Ada penumpang mencari supir di sekitar Anda ({$distKm} KM)."
                        : "Ada pesanan baru di sekitar lokasi Anda.";

                    if ($isPriority) {
                        $body .= " Segera ambil kesempatan prioritas ini!";
                    }

                    $this->fcmService->sendPushNotification(
                        $driver->fcm_token,
                        $title,
                        $body,
                        ['type' => 'new_order', 'order_id' => $orderId, 'is_priority' => $isPriority ? '1' : '0']
                    );
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'sendRidePush error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim FCM Food / Mart ke kumpulan driver
     */
    private function sendFoodPush(array $drivers, int $orderId, string $merchantName, string $targetVehicle, string $status, bool $isMart, bool $isPriority): void
    {
        try {
            $vehicleLabel = in_array($targetVehicle, ['mobil', 'car']) ? 'Kurir Mobil 🚗' : 'Kurir Motor 🛵';
            $prefix = $isPriority ? "⭐ [Prioritas] " : "";
            $title = $prefix . ($isMart ? 'Order MaiMart (' : 'Order MaiFood (') . $vehicleLabel . ')!';

            foreach ($drivers as $driver) {
                $fcmToken = is_object($driver) ? ($driver->fcm_token ?? '') : ($driver['fcm_token'] ?? '');
                if (!empty($fcmToken)) {
                    $dist = is_object($driver) ? ($driver->distance ?? null) : ($driver['distance'] ?? null);
                    $distText = ($dist !== null) ? round($dist, 1) . ' KM' : '';

                    $driverBody = ($status == 'processing' || $status == 'accepted_by_merchant')
                        ? ($isMart
                            ? "Toko {$merchantName} " . ($distText ? "({$distText}) " : "") . "sedang menyiapkan belanjaan. Ambil sekarang!"
                            : "Resto {$merchantName} " . ($distText ? "({$distText}) " : "") . "sedang menyiapkan pesanan. Ambil sekarang!")
                        : ($isMart
                            ? "Ada pesanan belanja siap diantar dari toko {$merchantName}."
                            : "Ada pesanan makanan siap diantar dari restoran {$merchantName}.");

                    if ($isPriority) {
                        $driverBody .= " (Penawaran Prioritas)";
                    }

                    $this->fcmService->sendPushNotification(
                        $fcmToken,
                        $title,
                        $driverBody,
                        [
                            'type'         => $isMart ? 'new_mart_order' : 'new_food_order',
                            'order_type'   => $isMart ? 'mart' : 'food',
                            'order_id'     => (string)$orderId,
                            'vehicle_type' => $targetVehicle,
                            'is_priority'  => $isPriority ? '1' : '0'
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'sendFoodPush error: ' . $e->getMessage());
        }
    }

    /**
     * Memastikan kolom database ada (Auto-healing)
     */
    public function ensurePriorityColumns(): void
    {
        try {
            if (!$this->db->fieldExists('is_priority', 'drivers')) {
                $this->db->query("ALTER TABLE drivers ADD COLUMN is_priority TINYINT(1) NOT NULL DEFAULT 0 AFTER status;");
            }
            if (!$this->db->fieldExists('priority_dispatched_at', 'orders')) {
                $this->db->query("ALTER TABLE orders ADD COLUMN priority_dispatched_at DATETIME NULL AFTER status;");
            }
            if (!$this->db->fieldExists('fallback_dispatched_at', 'orders')) {
                $this->db->query("ALTER TABLE orders ADD COLUMN fallback_dispatched_at DATETIME NULL AFTER priority_dispatched_at;");
            }
            if (!$this->db->fieldExists('priority_dispatched_at', 'food_orders')) {
                $this->db->query("ALTER TABLE food_orders ADD COLUMN priority_dispatched_at DATETIME NULL AFTER status;");
            }
            if (!$this->db->fieldExists('fallback_dispatched_at', 'food_orders')) {
                $this->db->query("ALTER TABLE food_orders ADD COLUMN fallback_dispatched_at DATETIME NULL AFTER priority_dispatched_at;");
            }
        } catch (\Throwable $e) {}
    }
}
