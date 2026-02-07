<?php
// cards/delete.php - حذف کارت
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $card_id = $_POST['card_id'];
    
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
        $pdo->beginTransaction();
        
        // Delete all subcards
        $stmt = $pdo->prepare("DELETE FROM subcards WHERE card_id = ?");
        $stmt->execute([$card_id]);
        
        // Delete card
        $stmt = $pdo->prepare("DELETE FROM cards WHERE id = ?");
        $stmt->execute([$card_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'کارت با موفقیت حذف شد';
        header("Location: ../decks/index.php?id=" . $card['deck_id']);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'خطا در حذف کارت: ' . $e->getMessage();
        header("Location: ../decks/index.php?id=" . $card['deck_id']);
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>