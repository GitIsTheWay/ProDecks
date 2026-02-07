<?php
// decks/delete.php - حذف Deck
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $deck_id = $_POST['deck_id'];
    
    // Verify deck access and ownership through project
    $stmt = $pdo->prepare("
        SELECT d.*, p.owner_id as project_owner 
        FROM decks d 
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
    
    // Only project owner can delete deck
    if ($deck['project_owner'] != $user_id) {
        $_SESSION['error'] = 'فقط مالک پروژه می‌تواند Deck را حذف کند';
        header("Location: index.php?id=$deck_id");
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete all subcards in deck (through cards)
        $stmt = $pdo->prepare("
            DELETE sc FROM subcards sc 
            JOIN cards c ON sc.card_id = c.id 
            WHERE c.deck_id = ?
        ");
        $stmt->execute([$deck_id]);
        
        // Delete all cards in deck
        $stmt = $pdo->prepare("DELETE FROM cards WHERE deck_id = ?");
        $stmt->execute([$deck_id]);
        
        // Delete deck
        $stmt = $pdo->prepare("DELETE FROM decks WHERE id = ?");
        $stmt->execute([$deck_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Deck با موفقیت حذف شد';
        header("Location: ../spaces/index.php?id=" . $deck['space_id']);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'خطا در حذف Deck: ' . $e->getMessage();
        header("Location: index.php?id=$deck_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>