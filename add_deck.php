<?php
// add_deck.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = $_POST['project_id'];
    $name = trim($_POST['name']);
    $user_id = $_SESSION['user_id'];
    
    // Verify user has access to project
    $stmt = $pdo->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    
    if ($stmt->rowCount() > 0 && !empty($name)) {
        // Get max position
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM decks WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Insert new deck
        $stmt = $pdo->prepare("INSERT INTO decks (project_id, name, position) VALUES (?, ?, ?)");
        $stmt->execute([$project_id, $name, $position]);
        
        // Add experience
        addExperience($user_id, 5, $pdo);
    }
}

header("Location: project.php?id=" . $project_id);
exit;
?>