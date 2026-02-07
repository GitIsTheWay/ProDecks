<?php
// subcards/toggle_status.php - تغییر وضعیت زیرکارت
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
        // Toggle status
        $new_status = $subcard['status'] === 'todo' ? 'done' : 'todo';
        $stmt = $pdo->prepare("UPDATE subcards SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $subcard_id]);
        
        // Add experience for completing subcard
        if ($new_status === 'done') {
            addExperience($user_id, 2, $pdo);
        }
        
        echo json_encode(['success' => true, 'new_status' => $new_status]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'خطا در تغییر وضعیت: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>