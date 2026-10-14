<?php
$servername = "localhost";
$username = "root"; // Sesuaikan jika Anda jalankan di server
$password = "";
$dbname = "maijek_db"; // Sesuaikan dengan nama DB Anda

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$queries = [
    // 1. Table merchants
    "CREATE TABLE IF NOT EXISTS merchants (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        address TEXT NULL,
        latitude DOUBLE NULL,
        longitude DOUBLE NULL,
        photo VARCHAR(255) NULL,
        is_open TINYINT(1) DEFAULT 0,
        balance DECIMAL(15,2) DEFAULT '0.00',
        fcm_token VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    // 2. Table merchant_menus
    "CREATE TABLE IF NOT EXISTS merchant_menus (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        merchant_id INT(11) UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT NULL,
        price DECIMAL(10,2) DEFAULT '0.00',
        photo VARCHAR(255) NULL,
        is_available TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
    )",

    // 3. Table food_orders
    "CREATE TABLE IF NOT EXISTS food_orders (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) UNSIGNED NOT NULL,
        driver_id INT(11) UNSIGNED NULL,
        merchant_id INT(11) UNSIGNED NOT NULL,
        delivery_address TEXT NULL,
        delivery_lat DOUBLE NULL,
        delivery_lng DOUBLE NULL,
        distance DOUBLE NULL,
        food_total DECIMAL(15,2) DEFAULT '0.00',
        delivery_fee DECIMAL(10,2) DEFAULT '0.00',
        total_price DECIMAL(15,2) DEFAULT '0.00',
        payment_method VARCHAR(50) DEFAULT 'cash',
        status ENUM('pending', 'accepted_by_merchant', 'driver_assigned', 'driver_at_merchant', 'delivering', 'completed', 'cancelled') DEFAULT 'pending',
        cancel_reason TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
        FOREIGN KEY (merchant_id) REFERENCES merchants(id) ON DELETE CASCADE
    )",

    // 4. Table food_order_items
    "CREATE TABLE IF NOT EXISTS food_order_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        food_order_id INT(11) UNSIGNED NOT NULL,
        menu_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) DEFAULT 1,
        price DECIMAL(10,2) DEFAULT '0.00',
        notes TEXT NULL,
        FOREIGN KEY (food_order_id) REFERENCES food_orders(id) ON DELETE CASCADE,
        FOREIGN KEY (menu_id) REFERENCES merchant_menus(id) ON DELETE CASCADE
    )"
];

foreach ($queries as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Successfully executed query.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
$conn->close();
