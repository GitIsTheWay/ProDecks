<?php
// spaces/edit.php - ویرایش Space
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $space_id = $_POST['space_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#4a5568';
    
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
        header('Location: ../dashboard.php');
        exit;
    }
    
    try {
        // Update space
        $stmt = $pdo->prepare("UPDATE spaces SET name = ?, description = ?, color = ? WHERE id = ?");
        $stmt->execute([$name, $description, $color, $space_id]);
        
        $_SESSION['success'] = 'Space با موفقیت ویرایش شد';
        header("Location: index.php?id=$space_id");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ویرایش Space: ' . $e->getMessage();
        header("Location: index.php?id=$space_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>