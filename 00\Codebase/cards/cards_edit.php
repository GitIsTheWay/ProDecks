<?php
// cards/edit.php - ویرایش کارت
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $card_id = $_POST['card_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?: null;
    $assignee_id = $_POST['assignee_id'] ?: null;
    
    // Verify card access through deck, space and project
    $stmt = $pdo->prepare("
        SELECT c.* FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        JOIN projects p ON s.project_id = p.id 
        WHERE c.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$card_id, $user_id]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$card) {
        $_SESSION['error'] = 'دسترسی به این کارت مجاز نیست';
        header('Location: ../dashboard.php');
        exit;
    }
    
    try {
        // Update card
        $stmt = $pdo->prepare("
            UPDATE cards 
            SET title = ?, description = ?, priority = ?, due_date = ?, assignee_id = ? 
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $priority, $due_date, $assignee_id, $card_id]);
        
        $_SESSION['success'] = 'کارت با موفقیت ویرایش شد';
        header("Location: ../decks/index.php?id=" . $card['deck_id']);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ویرایش کارت: ' . $e->getMessage();
        header("Location: ../decks/index.php?id=" . $card['deck_id']);
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>