<?php
// projects/edit.php - ویرایش پروژه
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $project_id = $_POST['project_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#667eea';
    
    // بررسی مالکیت پروژه
    if (!isProjectOwner($pdo, $project_id, $user_id)) {
        $_SESSION['error'] = 'فقط مالک پروژه می‌تواند آن را ویرایش کند';
        header("Location: index.php?id=$project_id");
        exit;
    }
    
    try {
        // به‌روزرسانی پروژه
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET name = ?, description = ?, color = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $color, $project_id]);
        
        $_SESSION['success'] = 'پروژه با موفقیت ویرایش شد';
        header("Location: index.php?id=$project_id");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ویرایش پروژه: ' . $e->getMessage();
        header("Location: index.php?id=$project_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>