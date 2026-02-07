<?php
// cards/toggle_status.php - تغییر وضعیت کارت
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $card_id = $_POST['card_id'];
    
    // Verify card access through deck, space and project
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
        echo json_encode(['success' => false, 'error' => 'دسترسی به این کارت مجاز نیست']);
        exit;
    }
    
    try {
        // Toggle status
        $new_status = $card['status'] === 'todo' ? 'done' : 'todo';
        $stmt = $pdo->prepare("UPDATE cards SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $card_id]);
        
        // Add experience for completing task
        if ($new_status === 'done') {
            addExperience($user_id, 5, $pdo);
        }
        
        echo json_encode(['success' => true, 'new_status' => $new_status]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'خطا در تغییر وضعیت: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>