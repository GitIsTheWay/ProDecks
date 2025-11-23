<?php
// edit_deck.php - ویرایش Deck
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deck_id = $_POST['deck_id'];
    $space_id = $_POST['space_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $color = $_POST['color'] ?? '#4a5568';
    $user_id = $_SESSION['user_id'];

    // Verify user has access to deck
    $stmt = $pdo->prepare("
        SELECT d.id 
        FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$deck_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0 && !empty($name)) {
        // Update deck
        $stmt = $pdo->prepare("
            UPDATE decks 
            SET name = ?, description = ?, color = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $color, $deck_id]);
        
        // Add experience
        addExperience($user_id, 2, $pdo);
        
        header("Location: space_decks.php?id=" . $space_id . "&success=deck_updated");
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>