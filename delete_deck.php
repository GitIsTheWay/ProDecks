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
    $deck_id = $_POST['deck_id'] ?? null;
    $user_id = getCurrentUserId();

    if (!$deck_id) {
        throw new Exception('شناسه Deck ارسال نشده است');
    }

    // بررسی دسترسی کاربر به دک
    $stmt = $pdo->prepare("
        SELECT d.id FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$deck_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('دسترسی به این Deck امکان‌پذیر نیست');
    }

    $pdo->beginTransaction();

    // حذف تمام ساب‌کارت‌ها
    $stmt = $pdo->prepare("
        DELETE c FROM cards c 
        WHERE c.parent_card_id IN (SELECT id FROM cards WHERE deck_id = ?)
    ");
    $stmt->execute([$deck_id]);

    // حذف تمام کارت‌های دک
    $stmt = $pdo->prepare("DELETE FROM cards WHERE deck_id = ?");
    $stmt->execute([$deck_id]);

    // حذف خود دک
    $stmt = $pdo->prepare("DELETE FROM decks WHERE id = ?");
    $stmt->execute([$deck_id]);

    $pdo->commit();

    // اضافه کردن تجربه
    addExperience($user_id, 3, $pdo);

    echo json_encode([
        'success' => true,
        'message' => 'Deck با موفقیت حذف شد'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Delete Deck Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>