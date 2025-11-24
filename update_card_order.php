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
    $card_order = $input['card_order'] ?? [];
    $user_id = getCurrentUserId();

    if (empty($card_order)) {
        throw new Exception('داده‌ای برای به‌روزرسانی دریافت نشد');
    }

    $pdo->beginTransaction();

    foreach ($card_order as $card) {
        $card_id = $card['card_id'] ?? null;
        $position = $card['position'] ?? 0;

        if (!$card_id) continue;

        // بررسی دسترسی کاربر به کارت
        $stmt = $pdo->prepare("
            SELECT c.id FROM cards c 
            JOIN decks d ON c.deck_id = d.id 
            JOIN spaces s ON d.space_id = s.id 
            LEFT JOIN space_members sm ON s.id = sm.space_id 
            WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
        ");
        $stmt->execute([$card_id, $user_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE cards SET position = ? WHERE id = ?");
            $stmt->execute([$position, $card_id]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'ترتیب کارت‌ها با موفقیت به‌روزرسانی شد']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Update Card Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطا در به‌روزرسانی ترتیب کارت‌ها']);
}
?>