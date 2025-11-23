<?php
// includes/auth.php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit;
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasProjectAccess($pdo, $project_id, $user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'];
    }
    
    $stmt = $pdo->prepare("
        SELECT pm.id 
        FROM project_members pm 
        WHERE pm.project_id = ? AND pm.user_id = ?
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->rowCount() > 0;
}

function isProjectOwner($pdo, $project_id, $user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'];
    }
    
    $stmt = $pdo->prepare("
        SELECT pm.id 
        FROM project_members pm 
        WHERE pm.project_id = ? AND pm.user_id = ? AND pm.role = 'owner'
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->rowCount() > 0;
}

function canEditProject($pdo, $project_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return isProjectOwner($pdo, $project_id) || hasProjectAccess($pdo, $project_id);
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function checkPermission($pdo, $permission, $project_id = null) {
    $user_id = $_SESSION['user_id'];
    
    switch ($permission) {
        case 'create_project':
            return isLoggedIn();
            
        case 'edit_project':
            return $project_id && canEditProject($pdo, $project_id);
            
        case 'view_project':
            return $project_id && hasProjectAccess($pdo, $project_id);
            
        case 'delete_project':
            return $project_id && isProjectOwner($pdo, $project_id);
            
        default:
            return false;
    }
}

function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>