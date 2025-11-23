<?php
// delete_deck.php - نسخه اصلاح شده برای Spaces
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deck_id = $_POST['deck_id'];
    $user_id = $_SESSION['user_id'];

    // Verify user has access to this deck
    $stmt = $pdo->prepare("
        SELECT d.id 
        FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$deck_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        try {
            $pdo->beginTransaction();
            
            // Delete all subcards first
            $stmt = $pdo->prepare("
                DELETE c FROM cards c 
                WHERE c.parent_card_id IN (SELECT id FROM cards WHERE deck_id = ?)
            ");
            $stmt->execute([$deck_id]);
            
            // Delete all cards in the deck
            $stmt = $pdo->prepare("DELETE FROM cards WHERE deck_id = ?");
            $stmt->execute([$deck_id]);
            
            // Then delete the deck
            $stmt = $pdo->prepare("DELETE FROM decks WHERE id = ?");
            $stmt->execute([$deck_id]);
            
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