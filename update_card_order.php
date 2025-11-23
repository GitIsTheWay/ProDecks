<?php
// update_card_order.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $card_order = $input['card_order'] ?? [];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        foreach ($card_order as $card) {
            // Verify user has access to this card
            $stmt = $pdo->prepare("
                SELECT c.id 
                FROM cards c 
                JOIN decks d ON c.deck_id = d.id 
                JOIN spaces s ON d.space_id = s.id 
                LEFT JOIN space_members sm ON s.id = sm.space_id 
                WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
            ");
            $stmt->execute([$card['card_id'], $user_id, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE cards SET position = ? WHERE id = ?");
                $stmt->execute([$card['position'], $card['card_id']]);
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