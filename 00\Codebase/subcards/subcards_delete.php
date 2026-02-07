<?php
// subcards/delete.php - حذف زیرکارت
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $subcard_id = $_POST['subcard_id'];
    
    // Verify subcard access through card, deck, space and project
    $stmt = $pdo->prepare("
        SELECT sc.* FROM subcards sc 
        JOIN cards c ON sc.card_id = c.id 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        JOIN projects p ON s.project_id = p.id 
        WHERE sc.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$subcard_id, $user_id]);
    $subcard = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subcard) {
        echo json_encode(['success' => false, 'error' => 'دسترسی به این زیرکارت مجاز نیست']);
        exit;
    }
    
    try {
        // Delete subcard
        $stmt = $pdo->prepare("DELETE FROM subcards WHERE id = ?");
        $stmt->execute([$subcard_id]);
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'خطا در حذف زیرکارت: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>