<?php
// add_card.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deck_id = $_POST['deck_id'];
    $space_id = $_POST['space_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assignee_id = $_POST['assignee_id'] ?: null;
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?: null;
    $time_estimate = $_POST['time_estimate'] ?: null;
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
    
    if ($stmt->rowCount() > 0 && !empty($title)) {
        // Get max position in deck
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM cards WHERE deck_id = ? AND parent_card_id IS NULL");
        $stmt->execute([$deck_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Insert new card
        $stmt = $pdo->prepare("
            INSERT INTO cards (deck_id, title, description, assignee_id, position, priority, due_date, time_estimate) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$deck_id, $title, $description, $assignee_id, $position, $priority, $due_date, $time_estimate]);
        
        // Add experience
        addExperience($user_id, 3, $pdo);
        
        header("Location: space_decks.php?id=" . $space_id);
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>