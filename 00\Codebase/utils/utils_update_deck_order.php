<?php
// utils/update_deck_order.php - بروزرسانی ترتیب Deck‌ها در Space
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['deck_order']) || !is_array($input['deck_order'])) {
        echo json_encode(['success' => false, 'error' => 'داده‌های نامعتبر']);
        exit;
    }
    
    $deck_order = $input['deck_order'];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($deck_order as $item) {
            $deck_id = $item['deck_id'] ?? null;
            $position = $item['position'] ?? null;
            $space_id = $item['space_id'] ?? null;
            
            if (!$deck_id || !$position || !$space_id) {
                continue;
            }
            
            // بررسی دسترسی کاربر به Deck (از طریق پروژه)
            $stmt = $pdo->prepare("
                SELECT d.* FROM decks d 
                JOIN spaces s ON d.space_id = s.id 
                JOIN projects p ON s.project_id = p.id 
                WHERE d.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
            ");
            $stmt->execute([$deck_id, $user_id]);
            $deck = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$deck) {
                throw new Exception('دسترسی به Deck مجاز نیست');
            }
            
            // بررسی اینکه Deck در همان Space است
            if ($deck['space_id'] != $space_id) {
                throw new Exception('Deck متعلق به این Space نیست');
            }
            
            // آپدیت موقعیت Deck
            $stmt = $pdo->prepare("UPDATE decks SET position = ? WHERE id = ?");
            $stmt->execute([$position, $deck_id]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'ترتیب Deck‌ها با موفقیت به‌روزرسانی شد'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'خطا در به‌روزرسانی ترتیب: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>