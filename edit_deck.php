<?php
// edit_deck.php
include 'includes/config.php';
include 'includes/functions.php';
include 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deck_id = $_POST['deck_id'];
    $project_id = $_POST['project_id'];
    $name = trim($_POST['name']);
    $user_id = $_SESSION['user_id'];

    // Verify user has access to this deck
    $stmt = $pdo->prepare("
        SELECT d.id 
        FROM decks d 
        JOIN project_members pm ON d.project_id = pm.project_id 
        WHERE d.id = ? AND pm.user_id = ?
    ");
    $stmt->execute([$deck_id, $user_id]);
    
    if ($stmt->rowCount() > 0 && !empty($name)) {
        // Update deck
        $stmt = $pdo->prepare("UPDATE decks SET name = ? WHERE id = ?");
        $stmt->execute([$name, $deck_id]);
        
        header("Location: project_decks.php?id=" . $project_id);
        exit;
    }
}

header("Location: dashboard.php");
exit;
?>