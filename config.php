<?php
// includes/config.php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'prodecks_db');
define('DB_USER', 'username');
define('DB_PASS', 'password');

// تنظیمات سایت
define('SITE_NAME', 'ProDecks');
define('SITE_URL', 'http://yourdomain.com');

// اتصال به دیتابیس
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>