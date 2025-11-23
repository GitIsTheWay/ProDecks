<?php
// delete_subcard.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subcard_id = $_POST['subcard_id'];
    $user_id = $_SESSION['user_id'];

    // Verify user has access to this subcard
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$subcard_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        // Delete subcard
        $stmt = $pdo->prepare("DELETE FROM cards WHERE id = ?");
        $stmt->execute([$subcard_id]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>