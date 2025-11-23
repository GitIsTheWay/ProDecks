<?php
// update_deck_order.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $deck_order = $input['deck_order'] ?? [];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        foreach ($deck_order as $deck) {
            // Verify user has access to this deck
            $stmt = $pdo->prepare("
                SELECT d.id 
                FROM decks d 
                JOIN spaces s ON d.space_id = s.id 
                LEFT JOIN space_members sm ON s.id = sm.space_id 
                WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
            ");
            $stmt->execute([$deck['deck_id'], $user_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE decks SET position = ? WHERE id = ?");
                $stmt->execute([$deck['position'], $deck['deck_id']]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>