<?php
// invite_member.php
include 'includes/config.php';
include 'includes/functions.php';
include 'includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_id = $_POST['project_id'];
    $email = trim($_POST['email']);
    $user_id = $_SESSION['user_id'];

    // Verify user is project owner
    $stmt = $pdo->prepare("
        SELECT pm.role 
        FROM project_members pm 
        WHERE pm.project_id = ? AND pm.user_id = ? AND pm.role = 'owner'
    ");
    $stmt->execute([$project_id, $user_id]);
    
    if ($stmt->rowCount() > 0 && !empty($email)) {
        // Find user by email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($target_user) {
            // Check if user is already a member
            $stmt = $pdo->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
            $stmt->execute([$project_id, $target_user['id']]);
            
            if ($stmt->rowCount() == 0) {
                // Add user to project
                $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'member')");
                $stmt->execute([$project_id, $target_user['id']]);
                
                $_SESSION['success_message'] = "کاربر با موفقیت به پروژه اضافه شد";
            } else {
                $_SESSION['error_message'] = "این کاربر قبلاً عضو پروژه است";
            }
        } else {
            $_SESSION['error_message'] = "کاربری با این ایمیل یافت نشد";
        }
    } else {
        $_SESSION['error_message'] = "شما دسترسی لازم برای دعوت عضو را ندارید";
    }
    
    header("Location: project.php?id=" . $project_id);
    exit;
}

header("Location: dashboard.php");
exit;
?>