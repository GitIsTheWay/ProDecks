<?php
// create_deck.php - ایجاد Deck جدید در Space
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $space_id = $_POST['space_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $color = $_POST['color'] ?? '#4a5568';
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to space
    $stmt = $pdo->prepare("
        SELECT s.id 
        FROM spaces s 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE s.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$space_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0 && !empty($name)) {
        // Get max position in space
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM decks WHERE space_id = ?");
        $stmt->execute([$space_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Insert new deck
        $stmt = $pdo->prepare("
            INSERT INTO decks (space_id, name, description, color, position) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$space_id, $name, $description, $color, $position]);
        
        // Add experience
        addExperience($user_id, 5, $pdo);
        
        header("Location: space_decks.php?id=" . $space_id . "&success=deck_created");
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>