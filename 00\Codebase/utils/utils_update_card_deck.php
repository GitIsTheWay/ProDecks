<?php
// utils/update_card_deck.php - تغییر Deck یک کارت (برای درگ‌اند‌دراپ)
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $card_id = $_POST['card_id'] ?? null;
    $new_deck_id = $_POST['new_deck_id'] ?? null;
    
    if (!$card_id || !$new_deck_id) {
        echo json_encode(['success' => false, 'error' => 'اطلاعات ناقص']);
        exit;
    }
    
    // بررسی دسترسی کاربر به کارت (از طریق پروژه)
    $stmt = $pdo->prepare("
        SELECT c.* FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        JOIN projects p ON s.project_id = p.id 
        WHERE c.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$card_id, $user_id]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$card) {
        echo json_encode(['success' => false, 'error' => 'دسترسی به کارت مجاز نیست']);
        exit;
    }
    
    // بررسی دسترسی کاربر به Deck جدید (از طریق پروژه)
    $stmt = $pdo->prepare("
        SELECT d.* FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        JOIN projects p ON s.project_id = p.id 
        WHERE d.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$new_deck_id, $user_id]);
    $new_deck = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$new_deck) {
        echo json_encode(['success' => false, 'error' => 'دسترسی به Deck جدید مجاز نیست']);
        exit;
    }
    
    // بررسی اینکه آیا Deck جدید در همان Space است یا نه
    $stmt = $pdo->prepare("
        SELECT d.space_id FROM decks d WHERE d.id IN (?, ?)
    ");
    $stmt->execute([$card['deck_id'], $new_deck_id]);
    $spaces = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count(array_unique($spaces)) > 1) {
        echo json_encode(['success' => false, 'error' => 'نمی‌توان کارت را به Space دیگری منتقل کرد']);
        exit;
    }
    
    try {
        // دریافت موقعیت جدید در Deck مقصد
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM cards WHERE deck_id = ?");
        $stmt->execute([$new_deck_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_position = $result['max_pos'] + 1;
        
        // آپدیت Deck و موقعیت کارت
        $stmt = $pdo->prepare("UPDATE cards SET deck_id = ?, position = ? WHERE id = ?");
        $stmt->execute([$new_deck_id, $new_position, $card_id]);
        
        // اضافه کردن تجربه برای حرکت کارت
        addExperience($user_id, 2, $pdo);
        
        echo json_encode([
            'success' => true,
            'message' => 'کارت با موفقیت منتقل شد',
            'new_deck_id' => $new_deck_id,
            'new_position' => $new_position
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'خطا در انتقال کارت: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>