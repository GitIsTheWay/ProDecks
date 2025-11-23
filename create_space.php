<?php
// create_space.php
session_start();
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $color = $_POST['color'] ?? '#667eea';
    $access = $_POST['access'] ?? 'private';
    $user_id = $_SESSION['user_id'];
    
    if (!empty($name)) {
        // Generate unique invite code - استفاده از تابع از functions.php
        $invite_code = generateInviteCode();
        
        // Create space
        $stmt = $pdo->prepare("INSERT INTO spaces (name, description, color, access_type, invite_code, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $color, $access, $invite_code, $user_id]);
        $space_id = $pdo->lastInsertId();
        
        // Add default decks
        $default_decks = ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'];
        $position = 1;
        
        foreach ($default_decks as $deck_name) {
            $stmt = $pdo->prepare("INSERT INTO decks (space_id, name, position, color) VALUES (?, ?, ?, ?)");
            $stmt->execute([$space_id, $deck_name, $position, '#4a5568']);
            $position++;
        }
        
        // Add experience
        addExperience($user_id, 15, $pdo);
        
        header("Location: space_decks.php?id=" . $space_id);
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>