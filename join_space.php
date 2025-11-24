<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $invite_code = $_POST['invite_code'] ?? null;
    $user_id = getCurrentUserId();

    if (!$invite_code) {
        throw new Exception('کد دعوت ارسال نشده است');
    }

    // پیدا کردن space با کد دعوت
    $stmt = $pdo->prepare("SELECT * FROM spaces WHERE invite_code = ?");
    $stmt->execute([$invite_code]);
    $space = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$space) {
        throw new Exception('کد دعوت معتبر نیست');
    }

    // بررسی اینکه کاربر قبلاً عضو هست یا نه
    $stmt = $pdo->prepare("SELECT id FROM space_members WHERE space_id = ? AND user_id = ?");
    $stmt->execute([$space['id'], $user_id]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('شما قبلاً به این Space پیوسته‌اید');
    }

    // اضافه کردن کاربر به space
    $stmt = $pdo->prepare("INSERT INTO space_members (space_id, user_id, role) VALUES (?, ?, 'member')");
    $stmt->execute([$space['id'], $user_id]);

    // اضافه کردن تجربه
    addExperience($user_id, 5, $pdo);

    echo json_encode([
        'success' => true,
        'message' => 'با موفقیت به Space پیوستید',
        'space_id' => $space['id']
    ]);

} catch (Exception $e) {
    error_log("Join Space Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>