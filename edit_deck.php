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
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#4a5568';
    $user_id = getCurrentUserId();

    if (!$deck_id || empty($name)) {
        throw new Exception('پارامترهای ضروری ارسال نشده است');
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

    // آپدیت دک
    $stmt = $pdo->prepare("UPDATE decks SET name = ?, description = ?, color = ? WHERE id = ?");
    $stmt->execute([$name, $description, $color, $deck_id]);

    // گرفتن اطلاعات آپدیت شده
    $stmt = $pdo->prepare("SELECT * FROM decks WHERE id = ?");
    $stmt->execute([$deck_id]);
    $updated_deck = $stmt->fetch(PDO::FETCH_ASSOC);

    // اضافه کردن تجربه
    addExperience($user_id, 3, $pdo);

    echo json_encode([
        'success' => true,
        'deck' => $updated_deck,
        'message' => 'Deck با موفقیت ویرایش شد'
    ]);

} catch (Exception $e) {
    error_log("Edit Deck Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>