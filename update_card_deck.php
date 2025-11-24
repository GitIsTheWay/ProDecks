// اضافه کردن بررسی CSRF و هندلینگ خطا
session_start();
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// بررسی CSRF token
if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    $card_id = $_POST['card_id'] ?? null;
    $new_deck_id = $_POST['new_deck_id'] ?? null;
    $user_id = getCurrentUserId();

    if (!$card_id || !$new_deck_id) {
        throw new Exception('Missing required parameters');
    }

    // بررسی دسترسی کاربر به کارت و دک جدید
    $stmt = $pdo->prepare("
        SELECT c.id FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        JOIN space_members sm ON s.id = sm.space_id 
        WHERE c.id = ? AND sm.user_id = ?
    ");
    $stmt->execute([$card_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Access denied to card');
    }

    // بررسی دسترسی به دک جدید
    $stmt = $pdo->prepare("
        SELECT d.id FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        JOIN space_members sm ON s.id = sm.space_id 
        WHERE d.id = ? AND sm.user_id = ?
    ");
    $stmt->execute([$new_deck_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Access denied to target deck');
    }

    // آپدیت دک کارت
    $stmt = $pdo->prepare("UPDATE cards SET deck_id = ? WHERE id = ?");
    $stmt->execute([$new_deck_id, $card_id]);

    // اضافه کردن تجربه
    addExperience($user_id, 2, $pdo);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Drag & Drop Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطا در انتقال کارت']);
}