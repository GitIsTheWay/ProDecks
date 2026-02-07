<?php
// subcards/create.php - ایجاد زیرکارت جدید
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $card_id = $_POST['card_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    
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
        // Get max position
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM subcards WHERE card_id = ?");
        $stmt->execute([$card_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Create subcard
        $stmt = $pdo->prepare("
            INSERT INTO subcards (card_id, title, description, position) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$card_id, $title, $description, $position]);
        $subcard_id = $pdo->lastInsertId();
        
        // Add experience
        addExperience($user_id, 1, $pdo);
        
        echo json_encode(['success' => true, 'subcard_id' => $subcard_id]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'خطا در ایجاد زیرکارت: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>