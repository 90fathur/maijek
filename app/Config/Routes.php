<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/privacy', 'Home::privacy');

// Menangani CORS Preflight Request (OPTIONS)
$routes->options('api/(:any)', function () {});

// Helper to restrict debug / maintenance / direct DDL migration endpoints
$isAuthorizedDebug = function () {
    if (is_cli()) {
        return true;
    }
    if (ENVIRONMENT === 'development') {
        return true;
    }
    $secret = getenv('ADMIN_DEBUG_KEY');
    if (!empty($secret)) {
        $req = service('request');
        $key = $req->getGet('debug_key') ?? $req->getHeaderLine('X-Debug-Key');
        if (!empty($key) && hash_equals($secret, (string)$key)) {
            return true;
        }
    }
    return false;
};

if ($isAuthorizedDebug()) {
    $routes->get('migrate-discounts', 'MigrateDiscounts::index');
    $routes->get('demo-duitku', 'Home::demoCheckout');
    $routes->get('check-db', 'Home::checkDb');
    $routes->get('api/testfcm', '\App\Controllers\Api\TestFcm::index');

    // ==== SCRIPT MIGRASI DATABASE ONLINE ====
    $routes->get('api/migrate-timestamps', function () {
        try {
            $db = \Config\Database::connect();
            $db->query('ALTER TABLE orders ADD COLUMN accepted_at DATETIME NULL;');
            $db->query('ALTER TABLE orders ADD COLUMN in_progress_at DATETIME NULL;');
            $db->query('ALTER TABLE orders ADD COLUMN completed_at DATETIME NULL;');
            return 'Success! Kolom waktu berhasil ditambahkan ke database.';
        } catch (\Exception $e) {
            return 'Sudah ada / Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/migrate-order-status', function () {
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'accepted', 'in_progress', 'on_trip', 'completed', 'cancelled') DEFAULT 'pending';");
            return 'Success! Kolom status pada tabel orders berhasil diupdate dengan in_progress.';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/optimize-indexes', function () {
        try {
            $db = \Config\Database::connect();
            $results = [];

            // Pastikan kolom is_read ada di order_chats jika tabelnya ada
            if ($db->tableExists('order_chats') && !$db->fieldExists('is_read', 'order_chats')) {
                $db->query("ALTER TABLE order_chats ADD COLUMN is_read TINYINT(1) DEFAULT 0;");
                $results[] = "Menambahkan kolom is_read ke tabel order_chats.";
            }

            $addIndexSafely = function ($table, $indexName, $columns, $columnsSql) use ($db, &$results) {
                if (!$db->tableExists($table)) {
                    $results[] = "Tabel $table tidak ditemukan (dilewati).";
                    return;
                }
                // Validasi semua kolom harus ada
                foreach ($columns as $col) {
                    if (!$db->fieldExists($col, $table)) {
                        $results[] = "Kolom $col tidak ada di $table, index $indexName dilewati.";
                        return;
                    }
                }
                $existing = $db->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'")->getResultArray();
                if (empty($existing)) {
                    $db->query("ALTER TABLE `$table` ADD INDEX `$indexName` ($columnsSql)");
                    $results[] = "Berhasil membuat index $indexName pada tabel $table ($columnsSql).";
                } else {
                    $results[] = "Index $indexName sudah ada pada tabel $table.";
                }
            };

            // Orders
            $addIndexSafely('orders', 'idx_orders_status_service', ['status', 'service_type'], '`status`, `service_type`');
            $addIndexSafely('orders', 'idx_orders_driver_status', ['driver_id', 'status'], '`driver_id`, `status`');
            $addIndexSafely('orders', 'idx_orders_user_status', ['user_id', 'status'], '`user_id`, `status`');
            $addIndexSafely('orders', 'idx_orders_created', ['created_at'], '`created_at`');

            // Drivers
            $addIndexSafely('drivers', 'idx_drivers_radar', ['is_online', 'status', 'verification_status', 'vehicle_type'], '`is_online`, `status`, `verification_status`, `vehicle_type`');
            $addIndexSafely('drivers', 'idx_drivers_coords', ['latitude', 'longitude'], '`latitude`, `longitude`');

            // Food Orders
            $addIndexSafely('food_orders', 'idx_food_driver_status', ['driver_id', 'status'], '`driver_id`, `status`');
            $addIndexSafely('food_orders', 'idx_food_user_status', ['user_id', 'status'], '`user_id`, `status`');
            $addIndexSafely('food_orders', 'idx_food_merchant_status', ['merchant_id', 'status'], '`merchant_id`, `status`');
            $addIndexSafely('food_orders', 'idx_food_status', ['status'], '`status`');
            $addIndexSafely('food_orders', 'idx_food_created', ['created_at'], '`created_at`');

            // Order Chats
            $addIndexSafely('order_chats', 'idx_chats_order_unread', ['order_id', 'is_read', 'sender_type'], '`order_id`, `is_read`, `sender_type`');
            $addIndexSafely('order_chats', 'idx_chats_created', ['created_at'], '`created_at`');

            return "<h1>Optimasi Index Database Selesai!</h1><ul><li>" . implode("</li><li>", $results) . "</li></ul>";
        } catch (\Throwable $e) {
            return "<h1>Gagal Mengoptimasi Index!</h1><p>Error: " . $e->getMessage() . "</p>";
        }
    });

    $routes->cli('api/migrate-coins', function () {
        try {
            $db = \Config\Database::connect();
            try {
                $db->query('ALTER TABLE users ADD COLUMN coins INT DEFAULT 0 AFTER balance;');
            } catch (\Exception $e) {
            }
            try {
                $db->query('ALTER TABLE drivers ADD COLUMN coins INT DEFAULT 0 AFTER balance;');
            } catch (\Exception $e) {
            }
            return 'Success! Kolom coins berhasil ditambahkan ke database.';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/migrate-device-id', function () {
        try {
            $db = \Config\Database::connect();
            $db->query('ALTER TABLE users ADD COLUMN device_id VARCHAR(100) NULL;');
            return 'Success! Kolom device_id berhasil ditambahkan ke tabel users.';
        } catch (\Exception $e) {
            return 'Sudah ada atau Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/clean-device-ids', function () {
        try {
            $db = \Config\Database::connect();
            // Reset device_id yang generic/fallback/terlalu pendek
            $db->query("UPDATE users SET device_id = NULL WHERE device_id IN ('unknown_device_id', 'unknown', 'unknown_ios_id', '', 'null') OR LENGTH(device_id) < 6;");
            // Reset device_id pada akun yang belum pernah terverifikasi OTP
            $db->query("UPDATE users SET device_id = NULL WHERE is_verified = 0;");
            return 'Success! Seluruh batasan perangkat generik & akun pending berhasil di-reset.';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/reset-dummy-balance', function () {
        try {
            $db = \Config\Database::connect();
            // Reset saldo penumpang #USR-0029 (Rantis) yang mendapatkan saldo dari dummy topup
            $db->table('users')->where('id', 29)->update(['balance' => 0]);
            // Hapus transaksi dummy
            try {
                $db->table('transactions')->where('user_id', 29)->where('description', 'Top Up Saldo MaiPay')->delete();
            } catch (\Exception $ex) {
            }
            return 'Success! Saldo pengguna #USR-0029 (Rantis) berhasil di-reset ke Rp 0.';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/migrate-user-status', function () {
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER is_verified;");
            return 'Success! Kolom status penumpang berhasil ditambahkan ke database online.';
        } catch (\Exception $e) {
            return 'Sudah ada / Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/migrate-otp', function () {
        try {
            $db = \Config\Database::connect();
            $db->query('ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0;');
            $db->query('ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) NULL;');
            $db->query('ALTER TABLE users ADD COLUMN otp_expired_at DATETIME NULL;');
            $db->query('UPDATE users SET is_verified = 1;');
            return 'Success! Kolom OTP berhasil ditambahkan ke tabel users.';
        } catch (\Exception $e) {
            return 'Sudah ada atau Error: ' . $e->getMessage();
        }
    });

    $routes->get('api/migrate-online', function () {
        try {
            $migrate = \Config\Services::migrations();
            $latest = $migrate->latest();

            if ($latest) {
                return "<h1>Success!</h1><p>Database migrations have been successfully executed. All tables (including 'withdrawals') are now up to date!</p>";
            } else {
                return "<h1>Nothing to do</h1><p>Your database is already up to date with the latest migrations.</p>";
            }
        } catch (\Throwable $e) {
            return "<h1>Migration Failed!</h1><p>Error: " . $e->getMessage() . "</p>";
        }
    });

    $routes->get('api/migrate-notifications', 'Api\MigrateNotifications::index');

    $routes->get('dbcheck', 'DbCheck::index');

    $routes->get('api/debug/fix-drivers', function () {
        $db = \Config\Database::connect();
        // Jadikan semua driver AKTIF, APPROVED, ONLINE, dan Punya Kordinat Tengah Kota untuk keperluan testing darurat!
        // Kordinat ini diset ke area sekitar Polman (Polewali Mandar) agar masuk radius 5KM
        $db->query("UPDATE drivers SET verification_status = 'approved', status = 'active', is_online = 1, latitude = -3.4246, longitude = 119.3414 WHERE latitude IS NULL OR latitude = 0");
        $db->query("UPDATE drivers SET verification_status = 'approved', status = 'active', is_online = 1");
        return "Berhasil memperbaiki dan mengaktifkan " . $db->affectedRows() . " driver. Silakan coba pesan sekarang!";
    });

    $routes->get('api/debug/approve-merchants', function () {
        $db = \Config\Database::connect();
        $db->query("UPDATE merchants SET verification_status = 'approved', is_open = 1 WHERE verification_status != 'approved'");
        $count = $db->affectedRows();
        return "<h1>Sukses!</h1><p>Berhasil menyetujui (approve) <strong>{$count}</strong> mitra restoran/warung. Seluruh akun toko sekarang sudah berstatus APPROVED dan dapat langsung menerima pesanan serta menambah menu!</p>";
    });

    $routes->get('api/debug/wallet', function () {
        $html = "<h1>Diagnostik Sistem Dompet & Pendapatan</h1><ul>";

        $db = \Config\Database::connect();

        // Cek Tabel
        if ($db->tableExists('withdrawals')) {
            $html .= "<li style='color:green'>✔️ Tabel withdrawals ada.</li>";
        } else {
            $html .= "<li style='color:red'>❌ Tabel withdrawals BELUM ADA!</li>";
        }

        if ($db->tableExists('drivers')) {
            $drivers = $db->table('drivers')->select('id, name, balance')->get()->getResultArray();

            $orderModel = new \App\Models\OrderModel();
            foreach ($drivers as &$drv) {
                $comp = $orderModel->where('driver_id', $drv['id'])->where('status', 'completed')->countAllResults();
                $canc = $orderModel->where('driver_id', $drv['id'])->where('status', 'cancelled')->countAllResults();
                $drv['completed'] = $comp;
                $drv['cancelled'] = $canc;
            }

            $html .= "<li><b>Data Driver:</b> <pre>" . print_r($drivers, true) . "</pre></li>";
        }

        if ($db->tableExists('orders')) {
            $orders = $db->table('orders')->select('id, driver_id, status, price, payment_method')->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();
            $html .= "<li><b>5 Order Terakhir:</b> <pre>" . print_r($orders, true) . "</pre></li>";
        }

        $html .= "</ul>";
        return $html;
    });
}

// API Routes
$routes->group('api', function ($routes) use ($isAuthorizedDebug) {

    // ======== GLOBAL PUBLIC API ========
    $routes->get('settings', '\App\Controllers\Api\Settings::index');

    $routes->get('migrate-maimart', function () {
        try {
            $db = \Config\Database::connect();
            $results = [];

            // 1. Kolom merchant_type di merchants (food, mart, all)
            if ($db->tableExists('merchants') && !$db->fieldExists('merchant_type', 'merchants')) {
                $db->query("ALTER TABLE merchants ADD COLUMN merchant_type ENUM('food', 'mart', 'all') DEFAULT 'food' AFTER address;");
                $results[] = "Kolom merchant_type berhasil ditambahkan ke tabel merchants.";
            } else {
                $results[] = "Kolom merchant_type sudah ada di tabel merchants.";
            }

            // 2. Kolom category di merchants
            if ($db->tableExists('merchants') && !$db->fieldExists('category', 'merchants')) {
                $db->query("ALTER TABLE merchants ADD COLUMN category VARCHAR(100) NULL AFTER merchant_type;");
                $results[] = "Kolom category berhasil ditambahkan ke tabel merchants.";
            } else {
                $results[] = "Kolom category sudah ada di tabel merchants.";
            }

            // 3. Kolom category di merchant_menus
            if ($db->tableExists('merchant_menus') && !$db->fieldExists('category', 'merchant_menus')) {
                $db->query("ALTER TABLE merchant_menus ADD COLUMN category VARCHAR(100) NULL AFTER description;");
                $results[] = "Kolom category berhasil ditambahkan ke tabel merchant_menus.";
            } else {
                $results[] = "Kolom category sudah ada di tabel merchant_menus.";
            }

            // 4. Kolom unit di merchant_menus
            if ($db->tableExists('merchant_menus') && !$db->fieldExists('unit', 'merchant_menus')) {
                $db->query("ALTER TABLE merchant_menus ADD COLUMN unit VARCHAR(50) DEFAULT 'pcs' AFTER category;");
                $results[] = "Kolom unit berhasil ditambahkan ke tabel merchant_menus.";
            } else {
                $results[] = "Kolom unit sudah ada di tabel merchant_menus.";
            }

            // 5. Kolom order_type di food_orders (food, mart)
            if ($db->tableExists('food_orders') && !$db->fieldExists('order_type', 'food_orders')) {
                $db->query("ALTER TABLE food_orders ADD COLUMN order_type ENUM('food', 'mart') DEFAULT 'food' AFTER status;");
                $results[] = "Kolom order_type berhasil ditambahkan ke tabel food_orders.";
            } else {
                $results[] = "Kolom order_type sudah ada di tabel food_orders.";
            }

            return "<h1>Migrasi MaiMart Berhasil!</h1><ul><li>" . implode("</li><li>", $results) . "</li></ul>";
        } catch (\Throwable $e) {
            return "<h1>Migrasi MaiMart Gagal!</h1><p>Error: " . $e->getMessage() . "</p>";
        }
    });

    if ($isAuthorizedDebug()) {
        $routes->get('migrate', 'Migrate::index');
    }

    // ======== API UNTUK PENUMPANG (USER) ========
    $routes->group('user', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        // Auth (No Filter)
        $routes->post('register', 'UserAuth::register');
        $routes->post('login', 'UserAuth::login');
        $routes->post('verify-otp', 'UserAuth::verifyOtp');
        $routes->post('resend-otp', 'UserAuth::resendOtp');

        // Public Discovery / Browsing (Guest Mode - No Filter)
        $routes->get('home/banners', 'User\Home::banners');
        $routes->get('home/promos', 'User\Home::promos');
        $routes->get('home/flash-sales', 'User\Home::flashSaleMenus');
        $routes->get('home/best-sellers', 'User\Home::bestSellerMenus');
        $routes->get('home/new-merchants', 'User\Home::newMerchants');
        $routes->get('drivers/nearby', 'User\Drivers::nearby');

        // MaiFood Browsing (No Filter)
        $routes->get('food/merchants', 'User\Food::merchants');
        $routes->get('food/search', 'User\Food::search');
        $routes->get('food/latest-menus', 'User\Food::latestMenus');
        $routes->get('food/merchant/(:num)', 'User\Food::merchantDetail/$1');

        // MaiMart Browsing (No Filter)
        $routes->get('mart/categories', 'User\Mart::categories');
        $routes->get('mart/merchants', 'User\Mart::merchants');
        $routes->get('mart/search', 'User\Mart::search');
        $routes->get('mart/merchant/(:num)', 'User\Mart::merchantDetail/$1');

        // Protected (Hanya User Terdaftar / Terautentikasi)
        $routes->group('', ['filter' => 'jwt:user'], function ($routes) {
            $routes->get('profile', 'User\Profile::index');
            $routes->post('profile/update', 'User\Profile::updateProfile');
            $routes->post('account/delete', 'User\Profile::deleteAccount');

            // Notifications
            $routes->get('notifications', 'Notification::index', ['filter' => 'jwt']);
            $routes->post('notifications/(:num)/read', 'Notification::markAsRead/$1', ['filter' => 'jwt']);

            // Inbox Chats (CS & Order Chats)
            $routes->get('inbox-chats', 'User\Inbox::chats', ['filter' => 'jwt']);

            $routes->post('fcm-token', 'User\Profile::updateFcm');
            $routes->get('daily-reward', 'User\Profile::dailyReward');

            // MaiFood Orders & User Actions
            $routes->group('food', function ($routes) {
                $routes->post('order', 'User\Food::order');
                $routes->get('active', 'User\Food::active');
                $routes->get('detail/(:num)', 'User\Food::detail/$1');
                $routes->get('history', 'User\Food::history');
                $routes->post('rate', 'User\Food::rate');
                $routes->post('cancel', 'User\Food::cancel');
                $routes->get('receipt/(:num)', 'User\Receipt::foodDownload/$1');
            });

            // MaiMart Orders & User Actions
            $routes->group('mart', function ($routes) {
                $routes->post('order', 'User\Mart::order');
            });

            $routes->group('orders', function ($routes) {
                $routes->post('create', 'User\Orders::create');
                $routes->post('cancel', 'User\Orders::cancel');
                $routes->get('active', 'User\Orders::active');
                $routes->get('history', 'User\Orders::history');
                $routes->get('track/(:num)', 'User\Orders::track/$1');
                $routes->post('rate', 'User\Orders::rate');
                $routes->get('receipt/(:num)', 'User\Receipt::download/$1');

                // Wallet
                $routes->get('wallet/history', 'User\Wallet::history');
                $routes->post('wallet/topup', 'User\Wallet::topup');

                // Promo
                $routes->get('promos', 'User\Promo::index');
                $routes->post('promos/validate', 'User\Promo::validatePromo');

                // Chat Routes
                $routes->get('chat/(:num)', 'Chat::getMessages/$1');
                $routes->post('chat', 'Chat::sendMessage');

                // Food Chat Routes
                $routes->get('food-chat/(:num)', 'FoodChat::getMessages/$1');
                $routes->post('food-chat', 'FoodChat::sendMessage');
            });

            // Wallet Routes
            $routes->group('wallet', function ($routes) {
                $routes->get('history', 'User\Wallet::history');
                $routes->post('topup', 'User\Wallet::topup');
                $routes->post('withdraw', 'User\Wallet::withdraw');
                $routes->get('withdraw-status', 'User\Wallet::withdrawStatus');
            });

            // Wallet / Topup Routes
            $routes->group('topup', function ($routes) {
                $routes->post('createInvoice', 'User\Topup::createInvoice');
            });

            // CS Chat Routes
            $routes->group('cs', function ($routes) {
                $routes->get('messages', 'User\CsChat::messages');
                $routes->post('send', 'User\CsChat::send');
            });

            // Promo Routes
            $routes->group('promos', function ($routes) {
                $routes->get('', 'User\Promo::index');
                $routes->post('validate', 'User\Promo::validatePromo');
                $routes->get('dynamic', 'User\Promo::dynamic');
            });
        });
    });

    // ======== API UNTUK PENGEMUDI (DRIVER) ========
    $routes->group('driver', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        // Auth (No Filter)
        $routes->post('register', 'DriverAuth::register');
        $routes->post('login', 'DriverAuth::login');

        // Protected (Hanya Driver)
        $routes->group('', ['filter' => 'jwt:driver'], function ($routes) {
            // Profile & Status
            $routes->get('profile', 'Driver\Profile::index');
            $routes->post('profile/photo', 'Driver\Profile::updatePhoto');
            $routes->post('profile/vehicle', 'Driver\Profile::updateVehicle');
            $routes->post('profile/qris', 'Driver\Profile::uploadQris');
            $routes->post('profile/qris/toggle', 'Driver\Profile::toggleQris');
            $routes->post('location', 'Driver\Profile::updateLocation');
            $routes->post('status', 'Driver\Profile::toggleStatus');
            $routes->post('fcm-token', 'Driver\Profile::updateFcm');
            $routes->post('kyc', 'Driver\Profile::kyc');
            $routes->get('profile/reviews', 'Driver\Profile::reviews');
            $routes->get('daily-reward', 'Driver\Profile::dailyReward');
            $routes->post('account/delete', 'Driver\Profile::deleteAccount');

            // Orders
            $routes->group('orders', function ($routes) {
                $routes->get('available', 'Driver\Orders::available');
                $routes->get('active', 'Driver\Orders::active');
                $routes->get('detail/(:num)', 'Driver\Orders::detail/$1');
                $routes->get('history', 'Driver\Orders::history');
                $routes->post('accept', 'Driver\Orders::accept');
                $routes->post('update', 'Driver\Orders::updateStatus');
                $routes->post('status', 'Driver\Orders::updateStatus');
                $routes->post('uploadProof', 'Driver\Orders::uploadProof');
                $routes->post('skip', 'Driver\Orders::skip');

                // Chat Routes
                $routes->get('chat/(:num)', 'Chat::getMessages/$1');
                $routes->post('chat', 'Chat::sendMessage');

                // Food Chat Routes
                $routes->get('food-chat/(:num)', 'FoodChat::getMessages/$1');
                $routes->post('food-chat', 'FoodChat::sendMessage');
            });

            // MaiFood & MaiMart Routes for Driver
            $routes->group('food', function ($routes) {
                $routes->get('available', 'Driver\Food::available');
                $routes->get('active', 'Driver\Food::active');
                $routes->get('detail/(:num)', 'Driver\Food::detail/$1');
                $routes->post('accept', 'Driver\Food::accept');
                $routes->post('update-status', 'Driver\Food::updateStatus');
            });

            // Wallet
            $routes->group('wallet', function ($routes) {
                $routes->get('/', 'Driver\Wallet::index');
                $routes->post('withdraw', 'Driver\Wallet::withdraw');
                $routes->get('withdraw-status', 'Driver\Wallet::withdrawStatus');
                $routes->get('analytics', 'Driver\Wallet::analytics');

                // Profile
                $routes->post('profile/update', 'Driver\Profile::updateProfile');
            });

            // Topup Routes
            $routes->group('topup', function ($routes) {
                $routes->post('createInvoice', 'Driver\Topup::createInvoice');
            });

            // CS Chat Routes
            $routes->group('cs', function ($routes) {
                $routes->get('messages', 'Driver\CsChat::messages');
                $routes->post('send', 'Driver\CsChat::send');
            });
        });
    });

    // ======== API UNTUK PENJUAL (MERCHANT - MAIFOOD) ========
    $routes->group('merchant', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->post('register', 'MerchantAuth::register');
        $routes->post('login', 'MerchantAuth::login');

        $routes->group('', ['filter' => 'jwt:merchant'], function ($routes) {
            $routes->get('profile', 'Merchant\Profile::index');
            $routes->post('profile/update', 'Merchant\Profile::updateProfile');
            $routes->post('profile/upload-photo', 'Merchant\Profile::uploadPhoto');
            $routes->post('status', 'Merchant\Profile::toggleStatus');
            $routes->post('fcm-token', 'Merchant\Profile::updateFcm');

            $routes->group('menus', function ($routes) {
                $routes->get('/', 'Merchant\Menus::index');
                $routes->post('create', 'Merchant\Menus::create');
                $routes->post('update/(:num)', 'Merchant\Menus::update/$1');
                $routes->post('delete/(:num)', 'Merchant\Menus::delete/$1');

                // Variants
                $routes->post('variant/add', 'Merchant\Menus::addVariant');
                $routes->post('variant/delete/(:num)', 'Merchant\Menus::removeVariant/$1');
                $routes->post('variant/option/add', 'Merchant\Menus::addVariantOption');
                $routes->post('variant/option/delete/(:num)', 'Merchant\Menus::removeVariantOption/$1');
            });

            $routes->group('orders', function ($routes) {
                $routes->get('/', 'Merchant\Orders::index');
                $routes->get('active', 'Merchant\Orders::active');
                $routes->get('history', 'Merchant\Orders::history');
                $routes->get('show/(:num)', 'Merchant\Orders::show/$1');
                $routes->post('update-status', 'Merchant\Orders::updateStatus');
            });

            // Chat Routes
            $routes->group('chat', function ($routes) {
                $routes->get('/', 'Merchant\Chat::index');
                $routes->post('send', 'Merchant\Chat::send');
            });

            // CS Chat Routes
            $routes->group('cs', function ($routes) {
                $routes->get('messages', 'Merchant\CsChat::messages');
                $routes->post('send', 'Merchant\CsChat::send');
            });

            // Wallet
            $routes->group('wallet', function ($routes) {
                $routes->get('/', 'Merchant\Wallet::index');
            });

            // Analytics
            $routes->get('analytics', 'Merchant\Analytics::index');

            // Food Chat
            $routes->get('chat/(:num)', 'FoodChat::getMessages/$1');
            $routes->post('chat', 'FoodChat::sendMessage');

            // CS Chat Routes
            $routes->group('cs', function ($routes) {
                $routes->get('messages', 'Merchant\CsChat::messages');
                $routes->post('send', 'Merchant\CsChat::send');
            });
        });
    });

    // ======== CALLBACK / WEBHOOK ========
    $routes->group('callback', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->post('duitku', 'Callback\Duitku::index');
        $routes->get('duitku', 'Callback\Duitku::index');
        $routes->get('duitku_return', 'Callback\Duitku::returnUrl');
    });
});

if ($isAuthorizedDebug()) {
    $routes->get('api/debug/profile', function () {
        $db = \Config\Database::connect();

        $driverModel = new \App\Models\DriverModel();
        $driver = $driverModel->find(1);

        if (!$driver) return "Driver 1 not found";

        $orderModel = new \App\Models\OrderModel();
        $completedOrders = $orderModel->where('driver_id', 1)
            ->where('status', 'completed')
            ->countAllResults();

        $ratingQuery = $db->table('orders')
            ->selectAvg('rating')
            ->where('driver_id', 1)
            ->where('status', 'completed')
            ->where('rating >', 0)
            ->get()
            ->getRow();

        $performance = 100;
        if ($ratingQuery && $ratingQuery->rating > 0) {
            $performance = round(($ratingQuery->rating / 5) * 100);
        }

        $driver['total_orders'] = $completedOrders;
        $driver['performance'] = $performance;

        header('Content-Type: application/json');
        echo json_encode(['status' => 200, 'data' => $driver]);
        exit;
    });
}

// Admin Auth Routes (No filter)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::login');
    $routes->get('logout', 'Auth::logout');
});

// Admin Protected Routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminauth'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/liveUpdates', 'Dashboard::liveUpdates');

    // Finance Report
    $routes->get('finance', 'Finance::index');
    $routes->get('finance/exportCsv', 'Finance::exportCsv');

    // Drivers
    $routes->get('drivers', 'Drivers::index');
    $routes->post('drivers/store', 'Drivers::store');
    $routes->post('drivers/changeStatus/(:num)', 'Drivers::changeStatus/$1');
    $routes->post('drivers/injectBalance/(:num)', 'Drivers::injectBalance/$1');
    $routes->post('drivers/resetPassword/(:num)', 'Drivers::resetPassword/$1');
    $routes->post('drivers/updateVehicle/(:num)', 'Drivers::updateVehicle/$1');

    // Users
    $routes->get('users', 'Users::index');
    $routes->post('users/changeStatus/(:num)', 'Users::changeStatus/$1');
    $routes->post('users/resetDevice/(:num)', 'Users::resetDeviceId/$1');
    $routes->post('users/resetBalance/(:num)', 'Users::resetBalance/$1');
    $routes->post('users/setBalance/(:num)', 'Users::setBalance/$1');
    $routes->post('users/resetPassword/(:num)', 'Users::resetPassword/$1');

    // Orders
    $routes->get('orders', 'Orders::index');
    $routes->get('orders/export', 'Orders::exportCsv');
    $routes->get('orders/exportCsv/(:segment)', 'Orders::exportCsv/$1');
    $routes->get('orders/detail/(:segment)/(:num)', 'Orders::detail/$1/$2');
    $routes->post('orders/cancel/(:segment)/(:num)', 'Orders::forceCancel/$1/$2');

    // Settings
    $routes->get('settings', 'Settings::index');
    $routes->post('settings/update', 'Settings::update');

    // Kebijakan Saldo Driver (Tarik Order & Penarikan Saldo)
    $routes->get('driver-balance', 'DriverBalance::index');
    $routes->post('driver-balance/update', 'DriverBalance::update');

    // Promos
    $routes->get('promos', 'Promos::index');
    $routes->get('promos/create', 'Promos::create');
    $routes->post('promos/store', 'Promos::store');
    $routes->post('promos/toggle/(:num)', 'Promos::toggle/$1');
    $routes->post('promos/delete/(:num)', 'Promos::delete/$1');

    // Live Map
    $routes->get('map', 'Map::index');
    $routes->get('api/drivers', 'Api::getDriversLocation');

    // Broadcast
    $routes->get('broadcast', 'Broadcast::index');
    $routes->post('broadcast/send', 'Broadcast::send');

    // Verifications (KYC)
    $routes->get('verifications', 'Verifications::index');
    $routes->post('verifications/verify/(:num)/(:segment)', 'Verifications::verify/$1/$2');

    // Merchants (UMKM)
    $routes->get('merchants', 'Merchants::index');
    $routes->post('merchants/store', 'Merchants::store');
    $routes->post('merchants/update/(:num)', 'Merchants::update/$1');
    $routes->post('merchants/delete/(:num)', 'Merchants::delete/$1');
    $routes->get('merchants/menus/(:num)', 'Merchants::menus/$1');
    $routes->post('merchants/updateMenu/(:num)', 'Merchants::updateMenu/$1');
    $routes->post('merchants/addMenu/(:num)', 'Merchants::addMenu/$1');
    $routes->post('merchants/deleteMenu/(:num)', 'Merchants::deleteMenu/$1');
    $routes->post('merchants/deleteAllMenus/(:num)', 'Merchants::deleteAllMenus/$1');
    $routes->post('merchants/updatePhoto/(:num)', 'Merchants::updatePhoto/$1');
    $routes->post('merchants/changeStatus/(:num)', 'Merchants::changeStatus/$1');
    $routes->post('merchants/resetPassword/(:num)', 'Merchants::resetPassword/$1');

    // Merchant Verifications (UMKM)
    $routes->get('merchant-verifications', 'MerchantVerifications::index');
    $routes->post('merchant-verifications/verify/(:num)/(:segment)', 'MerchantVerifications::verify/$1/$2');

    // Withdrawals
    $routes->get('withdrawals', 'Withdrawals::index');
    $routes->get('withdrawals/export', 'Withdrawals::exportCsv');
    $routes->post('withdrawals/process/(:num)/(:segment)', 'Withdrawals::process/$1/$2');

    // Reviews
    $routes->get('reviews', 'Reviews::index');

    // Admins (Roles)
    $routes->get('admins', 'Admins::index');
    $routes->post('admins/create', 'Admins::create');
    $routes->post('admins/delete/(:num)', 'Admins::delete/$1');

    // Vehicle Classes
    $routes->get('vehicleclasses', 'VehicleClasses::index');
    $routes->post('vehicleclasses/create', 'VehicleClasses::create');
    $routes->post('vehicleclasses/delete/(:num)', 'VehicleClasses::delete/$1');

    // Tickets (Helpdesk)
    $routes->get('tickets', 'Tickets::index');

    // CS Chat
    $routes->get('cs-chat', 'CsChat::index');
    $routes->get('cs-chat/(:any)', 'CsChat::detail/$1');
    $routes->post('cs-chat/send', 'CsChat::send');
    $routes->post('cs-chat/resolve', 'CsChat::resolve');
    $routes->get('tickets/show/(:num)', 'Tickets::show/$1');
    $routes->post('tickets/reply/(:num)', 'Tickets::reply/$1');

    // Banners
    $routes->get('banners', 'Banners::index');
    $routes->post('banners/create', 'Banners::create');
    $routes->post('banners/delete/(:num)', 'Banners::delete/$1');

    // Zones
    $routes->get('zones', 'Zones::index');
    $routes->post('zones/create', 'Zones::create');
    $routes->post('zones/delete/(:num)', 'Zones::delete/$1');

    // Profile
    $routes->get('profile', 'Profile::index');
    $routes->post('profile/update', 'Profile::update');
});

// ======== MAIFOOD WEB MERCHANT PANEL (DESKTOP / PC KASIR) ========
$routes->group('merchant', ['namespace' => 'App\Controllers\Merchant'], static function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attemptLogin');
    $routes->get('logout', 'Auth::logout');
});

$routes->group('merchant', ['namespace' => 'App\Controllers\Merchant', 'filter' => 'merchantwebauth'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('dashboard/toggleStatus', 'Dashboard::toggleStatus');

    // Live Orders
    $routes->get('orders/live', 'Orders::live');
    $routes->get('orders/api-live', 'Orders::apiLiveOrders');
    $routes->post('orders/update-status/(:num)', 'Orders::updateStatus/$1');
    $routes->get('orders/print/(:num)', 'Orders::printReceipt/$1');

    // Menus
    $routes->get('menus', 'Menus::index');
    $routes->post('menus/store', 'Menus::store');
    $routes->post('menus/update/(:num)', 'Menus::update/$1');
    $routes->post('menus/toggle-availability/(:num)', 'Menus::toggleAvailability/$1');
    $routes->get('menus/delete/(:num)', 'Menus::delete/$1');

    // Profile & Operating Hours
    $routes->get('profile', 'Profile::index');
    $routes->post('profile/update', 'Profile::update');
    $routes->post('profile/update-photo', 'Profile::updatePhoto');
});

if ($isAuthorizedDebug()) {
    $routes->get('api/debug/test-haversine', 'Api\TestHaversine::index');
    $routes->get('api/debug/check-distance', 'Api\Debug::checkDistance');
}
