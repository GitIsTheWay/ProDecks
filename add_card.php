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
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    $time_estimate = $_POST['time_estimate'] ?? null;
    $user_id = getCurrentUserId();

    if (!$deck_id || empty($title)) {
        throw new Exception('عنوان کارت ضروری است');
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

    // گرفتن موقعیت ماکسیمم
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM cards WHERE deck_id = ? AND parent_card_id IS NULL");
    $stmt->execute([$deck_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $position = $result['max_pos'] + 1;

    // ایجاد کارت جدید
    $stmt = $pdo->prepare("
        INSERT INTO cards (deck_id, title, description, assignee_id, position, priority, due_date, time_estimate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $deck_id, 
        $title, 
        $description, 
        $user_id, // به عنوان assignee پیش‌فرض
        $position, 
        $priority, 
        $due_date, 
        $time_estimate
    ]);
    
    $card_id = $pdo->lastInsertId();

    // گرفتن اطلاعات کارت ایجاد شده
    $stmt = $pdo->prepare("SELECT * FROM cards WHERE id = ?");
    $stmt->execute([$card_id]);
    $new_card = $stmt->fetch(PDO::FETCH_ASSOC);

    // اضافه کردن تجربه
    addExperience($user_id, 2, $pdo);

    echo json_encode([
        'success' => true,
        'card' => $new_card,
        'message' => 'کارت با موفقیت ایجاد شد'
    ]);

} catch (Exception $e) {
    error_log("Add Card Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>