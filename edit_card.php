<?php
// edit_card.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_id = $_POST['card_id'];
    $space_id = $_POST['space_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assignee_id = $_POST['assignee_id'] ?: null;
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?: null;
    $time_estimate = $_POST['time_estimate'] ?: null;
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
    
    if ($stmt->rowCount() > 0 && !empty($title)) {
        // Update card
        $stmt = $pdo->prepare("
            UPDATE cards 
            SET title = ?, description = ?, assignee_id = ?, priority = ?, due_date = ?, time_estimate = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $assignee_id, $priority, $due_date, $time_estimate, $card_id]);
        
        // Add experience for editing card
        addExperience($user_id, 2, $pdo);
        
        header("Location: space_decks.php?id=" . $space_id);
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>