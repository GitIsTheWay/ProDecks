<?php
// includes/config.php - نسخه نهایی
session_start();

// تنظیمات سایت
define('SITE_NAME', 'ProDecks');
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define('BASE_PATH', dirname(dirname(__FILE__)));
define('DEBUG_MODE', true);

// تنظیمات دیتابیس
$db_config = [
    'host' => 'localhost',
    'name' => 'prodecks',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['name']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // تنظیم کدگذاری UTF-8
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        die("Connection failed: " . $e->getMessage());
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("خطا در اتصال به پایگاه داده");
    }
}

// بارگذاری توابع
require_once 'functions.php';

// تنظیم CSRF Token اگر وجود ندارد
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// تنظیم منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// شروع نشست
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>