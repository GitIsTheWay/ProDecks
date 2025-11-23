<?php
// add_subcard.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $parent_card_id = $_POST['parent_card_id'];
    $space_id = $_POST['space_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assignee_id = $_POST['assignee_id'] ?: null;
    $status = $_POST['status'] ?? 'todo';
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to parent card
    $stmt = $pdo->prepare("
        SELECT c.deck_id 
        FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$parent_card_id, $user_id, $user_id]);
    
    if ($stmt->rowCount() > 0 && !empty($title)) {
        $deck_id = $stmt->fetch(PDO::FETCH_ASSOC)['deck_id'];
        
        // Get max position for subcards
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM cards WHERE parent_card_id = ?");
        $stmt->execute([$parent_card_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Insert subcard
        $stmt = $pdo->prepare("
            INSERT INTO cards (deck_id, parent_card_id, title, description, assignee_id, position, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$deck_id, $parent_card_id, $title, $description, $assignee_id, $position, $status]);
        
        // Add experience
        addExperience($user_id, 2, $pdo);
        
        header("Location: space_decks.php?id=" . $space_id);
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>