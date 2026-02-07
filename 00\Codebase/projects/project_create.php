<?php
// projects/create.php - ایجاد پروژه جدید
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#667eea';
    
    try {
        $pdo->beginTransaction();
        
        // Create project
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, color, owner_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $color, $user_id]);
        $project_id = $pdo->lastInsertId();
        
        // Add creator as project member with owner role
        $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'owner')");
        $stmt->execute([$project_id, $user_id]);
        
        // Add experience
        addExperience($user_id, 10, $pdo);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'پروژه با موفقیت ایجاد شد';
        header("Location: index.php?id=$project_id");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'خطا در ایجاد پروژه: ' . $e->getMessage();
        header('Location: ../dashboard.php');
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>