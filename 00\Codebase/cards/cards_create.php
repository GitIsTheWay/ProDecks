<?php
// cards/create.php - ایجاد کارت جدید
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $deck_id = $_POST['deck_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?: null;
    $assignee_id = $_POST['assignee_id'] ?: null;
    
    // Verify deck access through space and project
    $stmt = $pdo->prepare("
        SELECT d.id FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        JOIN projects p ON s.project_id = p.id 
        WHERE d.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$deck_id, $user_id]);
    $deck = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$deck) {
        $_SESSION['error'] = 'دسترسی به این Deck مجاز نیست';
        header("Location: ../decks/index.php?id=$deck_id");
        exit;
    }
    
    try {
        // Get max position
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM cards WHERE deck_id = ?");
        $stmt->execute([$deck_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Create card
        $stmt = $pdo->prepare("
            INSERT INTO cards (deck_id, title, description, priority, due_date, assignee_id, position) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$deck_id, $title, $description, $priority, $due_date, $assignee_id, $position]);
        $card_id = $pdo->lastInsertId();
        
        // Add experience
        addExperience($user_id, 2, $pdo);
        
        $_SESSION['success'] = 'کارت با موفقیت ایجاد شد';
        header("Location: ../decks/index.php?id=$deck_id");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ایجاد کارت: ' . $e->getMessage();
        header("Location: ../decks/index.php?id=$deck_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>