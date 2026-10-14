<?php
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "maijek_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$queries = [
    // 1. Update merchants table
    "ALTER TABLE merchants 
     ADD COLUMN opening_time TIME NULL AFTER is_open,
     ADD COLUMN closing_time TIME NULL AFTER opening_time,
     ADD COLUMN operating_days VARCHAR(50) NULL AFTER closing_time;",

    // 2. Update food_order_items table
    "ALTER TABLE food_order_items 
     ADD COLUMN variant_details JSON NULL AFTER price;",

    // 3. Create menu_variants table
    "CREATE TABLE IF NOT EXISTS menu_variants (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        menu_id INT(11) UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        type ENUM('single', 'multiple') DEFAULT 'single',
        is_required TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (menu_id) REFERENCES merchant_menus(id) ON DELETE CASCADE
    )",

    // 4. Create menu_variant_options table
    "CREATE TABLE IF NOT EXISTS menu_variant_options (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        variant_id INT(11) UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        extra_price DECIMAL(10,2) DEFAULT '0.00',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (variant_id) REFERENCES menu_variants(id) ON DELETE CASCADE
    )",

    // 5. Create food_chats table
    "CREATE TABLE IF NOT EXISTS food_chats (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        food_order_id INT(11) UNSIGNED NOT NULL,
        sender_type ENUM('user', 'driver', 'merchant') NOT NULL,
        sender_id INT(11) UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (food_order_id) REFERENCES food_orders(id) ON DELETE CASCADE
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
