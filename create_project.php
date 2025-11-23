<?php
// create_project.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];
    
    if (!empty($name)) {
        // Create project
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, owner_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $user_id]);
        $project_id = $pdo->lastInsertId();
        
        // Add owner as project member
        $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'owner')");
        $stmt->execute([$project_id, $user_id]);
        
        // Create default decks
        $default_decks = ['انجام نشده', 'در حال انجام', 'انجام شده'];
        $position = 1;
        
        foreach ($default_decks as $deck_name) {
            $stmt = $pdo->prepare("INSERT INTO decks (project_id, name, position) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $deck_name, $position]);
            $position++;
        }
        
        // Add experience for creating project
        addExperience($user_id, 10, $pdo);
        
        header("Location: project.php?id=" . $project_id);
        exit;
    }
}

header("Location: dashboard.php");
exit;
?>