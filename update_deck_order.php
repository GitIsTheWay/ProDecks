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
    $input = json_decode(file_get_contents('php://input'), true);
    $deck_order = $input['deck_order'] ?? [];
    $user_id = getCurrentUserId();

    if (empty($deck_order)) {
        throw new Exception('داده‌ای برای به‌روزرسانی دریافت نشد');
    }

    $pdo->beginTransaction();

    foreach ($deck_order as $deck) {
        $deck_id = $deck['deck_id'] ?? null;
        $position = $deck['position'] ?? 0;

        if (!$deck_id) continue;

        // بررسی دسترسی کاربر به دک
        $stmt = $pdo->prepare("
            SELECT d.id FROM decks d 
            JOIN spaces s ON d.space_id = s.id 
            LEFT JOIN space_members sm ON s.id = sm.space_id 
            WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
        ");
        $stmt->execute([$deck_id, $user_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE decks SET position = ? WHERE id = ?");
            $stmt->execute([$position, $deck_id]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'ترتیب دک‌ها با موفقیت به‌روزرسانی شد']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Update Deck Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطا در به‌روزرسانی ترتیب دک‌ها']);
}
?>