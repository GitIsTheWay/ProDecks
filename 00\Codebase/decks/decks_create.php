<?php
// decks/create.php - ایجاد Deck جدید
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $space_id = $_POST['space_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#38b2ac';
    
    // Verify space access through project
    $stmt = $pdo->prepare("
        SELECT s.id FROM spaces s 
        JOIN projects p ON s.project_id = p.id 
        WHERE s.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$space_id, $user_id]);
    $space = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$space) {
        $_SESSION['error'] = 'دسترسی به این Space مجاز نیست';
        header("Location: ../spaces/index.php?id=$space_id");
        exit;
    }
    
    try {
        // Get max position
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM decks WHERE space_id = ?");
        $stmt->execute([$space_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Create deck
        $stmt = $pdo->prepare("INSERT INTO decks (space_id, name, description, color, position) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$space_id, $name, $description, $color, $position]);
        $deck_id = $pdo->lastInsertId();
        
        // Add experience
        addExperience($user_id, 3, $pdo);
        
        $_SESSION['success'] = 'Deck با موفقیت ایجاد شد';
        header("Location: index.php?id=$deck_id");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ایجاد Deck: ' . $e->getMessage();
        header("Location: ../spaces/index.php?id=$space_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>