<?php
// utils/update_card_order.php - بروزرسانی ترتیب کارت‌ها در Deck
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['card_order']) || !is_array($input['card_order'])) {
        echo json_encode(['success' => false, 'error' => 'داده‌های نامعتبر']);
        exit;
    }
    
    $card_order = $input['card_order'];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($card_order as $item) {
            $card_id = $item['card_id'] ?? null;
            $position = $item['position'] ?? null;
            $deck_id = $item['deck_id'] ?? null;
            
            if (!$card_id || !$position || !$deck_id) {
                continue;
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
                throw new Exception('دسترسی به کارت مجاز نیست');
            }
            
            // بررسی اینکه کارت در همان Deck است
            if ($card['deck_id'] != $deck_id) {
                throw new Exception('کارت متعلق به این Deck نیست');
            }
            
            // آپدیت موقعیت کارت
            $stmt = $pdo->prepare("UPDATE cards SET position = ? WHERE id = ?");
            $stmt->execute([$position, $card_id]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'ترتیب کارت‌ها با موفقیت به‌روزرسانی شد'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'خطا در به‌روزرسانی ترتیب: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>