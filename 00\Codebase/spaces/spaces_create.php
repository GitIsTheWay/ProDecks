<?php
// spaces/create.php - ایجاد Space جدید
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $project_id = $_POST['project_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $color = $_POST['color'] ?? '#4a5568';
    
    // Verify project access
    if (!hasProjectAccess($pdo, $project_id, $user_id)) {
        $_SESSION['error'] = 'دسترسی به این پروژه مجاز نیست';
        header("Location: ../projects/index.php?id=$project_id");
        exit;
    }
    
    try {
        // Get max position
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(position), 0) as max_pos FROM spaces WHERE project_id = ?");
        $stmt->execute([$project_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $result['max_pos'] + 1;
        
        // Create space
        $stmt = $pdo->prepare("INSERT INTO spaces (project_id, name, description, color, position) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$project_id, $name, $description, $color, $position]);
        $space_id = $pdo->lastInsertId();
        
        // Add experience
        addExperience($user_id, 5, $pdo);
        
        $_SESSION['success'] = 'Space با موفقیت ایجاد شد';
        header("Location: index.php?id=$space_id");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'خطا در ایجاد Space: ' . $e->getMessage();
        header("Location: ../projects/index.php?id=$project_id");
        exit;
    }
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>