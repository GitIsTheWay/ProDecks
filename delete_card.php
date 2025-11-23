<?php
// delete_card.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_id = $_POST['card_id'];
    $user_id = $_SESSION['user_id'];

    // Verify user has access to this card
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$card_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        try {
            $pdo->beginTransaction();
            
            // First delete all subcards
            $stmt = $pdo->prepare("DELETE FROM cards WHERE parent_card_id = ?");
            $stmt->execute([$card_id]);
            
            // Then delete the main card
            $stmt = $pdo->prepare("DELETE FROM cards WHERE id = ?");
            $stmt->execute([$card_id]);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>