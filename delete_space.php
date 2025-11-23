<?php
// delete_space.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $space_id = $_POST['space_id'];
    $user_id = $_SESSION['user_id'];

    // Verify user is space owner
    $stmt = $pdo->prepare("SELECT id FROM spaces WHERE id = ? AND user_id = ?");
    $stmt->execute([$space_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        try {
            $pdo->beginTransaction();
            
            // Delete all cards in space (including subcards)
            $stmt = $pdo->prepare("
                DELETE c FROM cards c 
                JOIN decks d ON c.deck_id = d.id 
                WHERE d.space_id = ?
            ");
            $stmt->execute([$space_id]);
            
            // Delete all decks in space
            $stmt = $pdo->prepare("DELETE FROM decks WHERE space_id = ?");
            $stmt->execute([$space_id]);
            
            // Delete space members
            $stmt = $pdo->prepare("DELETE FROM space_members WHERE space_id = ?");
            $stmt->execute([$space_id]);
            
            // Delete space
            $stmt = $pdo->prepare("DELETE FROM spaces WHERE id = ?");
            $stmt->execute([$space_id]);
            
            $pdo->commit();
            
            $_SESSION['success_message'] = 'Space با موفقیت حذف شد';
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'شما مالک این Space نیستید']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>