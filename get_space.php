<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

header('Content-Type: application/json');

$space_id = $_GET['id'] ?? null;
$user_id = getCurrentUserId();

if (!$space_id) {
    echo json_encode(['success' => false, 'error' => 'شناسه Space ارسال نشده است']);
    exit;
}

try {
    // بررسی دسترسی کاربر به space
    $stmt = $pdo->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM decks WHERE space_id = s.id) as decks_count,
               (SELECT COUNT(*) FROM space_members WHERE space_id = s.id) + 1 as members_count,
               (s.user_id = ?) as is_owner
        FROM spaces s 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE s.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$space_id, $user_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('دسترسی به این Space امکان‌پذیر نیست');
    }

    $space = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'space' => $space
    ]);

} catch (Exception $e) {
    error_log("Get Space Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>