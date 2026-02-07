<?php
// spaces/delete.php - حذف Space
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $space_id = $_POST['space_id'];
    
    // Verify space access and ownership through project
    $stmt = $pdo->prepare("
        SELECT s.*, p.owner_id as project_owner 
        FROM spaces s 
        JOIN projects p ON s.project_id = p.id 
        WHERE s.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    ");
    $stmt->execute([$space_id, $user_id]);
    $space = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$space) {
        $_SESSION['error'] = 'دسترسی به این Space مجاز نیست';
        header('Location: ../dashboard.php');
        exit;
    }
    
    // Only project owner can delete space
    if ($space['project_owner'] != $user_id) {
        $_SESSION['error'] = 'فقط مالک پروژه می‌تواند Space را حذف کند';
        header("Location: index.php?id=$space_id");
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Delete all cards in space (through decks)
        $stmt = $pdo->prepare("
            DELETE c FROM cards c 
            JOIN decks d ON c.deck_id = d.id 
            WHERE d.space_id = ?
        ");
        $stmt->execute([$space_id]);
        
        // Delete all decks in space
        $stmt = $pdo->prepare("DELETE FROM decks WHERE space_id = ?");
        $stmt->execute([$space_id]);
        
        // Delete space
        $stmt = $pdo->prepare("DELETE FROM spaces WHERE id = ?");
        $stmt->execute([$space_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Space با موفقیت حذف شد';
        header("Location: ../projects/index.php?id=" . $space['project_id']);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'خطا در حذف Space: ' . $e->getMessage();
        header("Location: index.php?id=$space_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>