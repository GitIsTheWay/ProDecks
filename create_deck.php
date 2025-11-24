// اضافه کردن پاسخ JSON برای آپدیت real-time
session_start();
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $space_id = $_POST['space_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#4a5568';
    $user_id = getCurrentUserId();

    if (!$space_id || empty($name)) {
        throw new Exception('Missing required parameters');
    }

    // بررسی دسترسی کاربر به space
    $stmt = $pdo->prepare("
        SELECT s.id FROM spaces s 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE s.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$space_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Access denied');
    }

    // گرفتن موقعیت ماکسیمم
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM decks WHERE space_id = ?");
    $stmt->execute([$space_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $position = $result['max_pos'] + 1;

    // ایجاد دک جدید
    $stmt = $pdo->prepare("INSERT INTO decks (space_id, name, description, color, position) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$space_id, $name, $description, $color, $position]);
    $deck_id = $pdo->lastInsertId();

    // اضافه کردن تجربه
    addExperience($user_id, 5, $pdo);

    // بازگرداندن اطلاعات دک جدید
    $stmt = $pdo->prepare("SELECT * FROM decks WHERE id = ?");
    $stmt->execute([$deck_id]);
    $new_deck = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'deck' => $new_deck,
        'message' => 'Deck با موفقیت ایجاد شد'
    ]);

} catch (Exception $e) {
    error_log("Create Deck Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'خطا در ایجاد Deck']);
}