<?php
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

header('Content-Type: application/json');

$deck_id = $_GET['id'] ?? null;
$user_id = getCurrentUserId();

if (!$deck_id) {
    echo json_encode(['success' => false, 'error' => 'شناسه Deck ارسال نشده است']);
    exit;
}

try {
    // بررسی دسترسی کاربر به دک
    $stmt = $pdo->prepare("
        SELECT d.*, s.name as space_name 
        FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$deck_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('دسترسی به این Deck امکان‌پذیر نیست');
    }

    $deck = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'deck' => $deck
    ]);

} catch (Exception $e) {
    error_log("Get Deck Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>