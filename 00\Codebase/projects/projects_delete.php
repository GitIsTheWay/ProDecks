<?php
// projects/delete.php - حذف پروژه
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $project_id = $_POST['project_id'];
    
    // بررسی مالکیت پروژه
    if (!isProjectOwner($pdo, $project_id, $user_id)) {
        $_SESSION['error'] = 'فقط مالک پروژه می‌تواند آن را حذف کند';
        header("Location: index.php?id=$project_id");
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // حذف تمام زیرکارت‌ها (از طریق کارت‌ها و Decks)
        $stmt = $pdo->prepare("
            DELETE sc FROM subcards sc 
            JOIN cards c ON sc.card_id = c.id 
            JOIN decks d ON c.deck_id = d.id 
            JOIN spaces s ON d.space_id = s.id 
            WHERE s.project_id = ?
        ");
        $stmt->execute([$project_id]);
        
        // حذف تمام کارت‌ها
        $stmt = $pdo->prepare("
            DELETE c FROM cards c 
            JOIN decks d ON c.deck_id = d.id 
            JOIN spaces s ON d.space_id = s.id 
            WHERE s.project_id = ?
        ");
        $stmt->execute([$project_id]);
        
        // حذف تمام Decks
        $stmt = $pdo->prepare("
            DELETE d FROM decks d 
            JOIN spaces s ON d.space_id = s.id 
            WHERE s.project_id = ?
        ");
        $stmt->execute([$project_id]);
        
        // حذف تمام Spaces
        $stmt = $pdo->prepare("DELETE FROM spaces WHERE project_id = ?");
        $stmt->execute([$project_id]);
        
        // حذف اعضای پروژه
        $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = ?");
        $stmt->execute([$project_id]);
        
        // حذف پروژه
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'پروژه با موفقیت حذف شد';
        header('Location: ../dashboard.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'خطا در حذف پروژه: ' . $e->getMessage();
        header("Location: index.php?id=$project_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>