<?php
// decks/edit.php - ویرایش Deck
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $deck_id = $_POST['deck_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#38b2ac';
    
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
        header('Location: ../dashboard.php');
        exit;
    }
    
    try {
        // Update deck
        $stmt = $pdo->prepare("UPDATE decks SET name = ?, description = ?, color = ? WHERE id = ?");
        $stmt->execute([$name, $description, $color, $deck_id]);
        
        $_SESSION['success'] = 'Deck با موفقیت ویرایش شد';
        header("Location: index.php?id=$deck_id");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ویرایش Deck: ' . $e->getMessage();
        header("Location: index.php?id=$deck_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>