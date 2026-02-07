<?php
// includes/auth.php - نسخه نهایی
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ../login.php');
        exit;
    }
}

function requireGuest() {
    if (isset($_SESSION['user_id'])) {
        header('Location: ../dashboard.php');
        exit;
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasProjectAccess($pdo, $project_id, $user_id = null) {
    if (!$user_id) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    if (!$user_id) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        SELECT id FROM project_members 
        WHERE project_id = ? AND user_id = ?
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->rowCount() > 0;
}

function isProjectOwner($pdo, $project_id, $user_id = null) {
    if (!$user_id) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    if (!$user_id) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        SELECT id FROM projects 
        WHERE id = ? AND owner_id = ?
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->rowCount() > 0;
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
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username);
}

function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit;
}

function checkPermission($pdo, $permission, $project_id = null) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        return false;
    }
    
    // پیاده‌سازی سیستم دسترسی پیشرفته‌تر در آینده
    switch ($permission) {
        case 'create_project':
            return true; // همه کاربران می‌توانند پروژه ایجاد کنند
            
        case 'manage_project':
            return $project_id ? isProjectOwner($pdo, $project_id) : false;
            
        case 'view_project':
            return $project_id ? hasProjectAccess($pdo, $project_id) : false;
            
        default:
            return false;
    }
}

function logout() {
    // پاک کردن تمام داده‌های نشست
    $_SESSION = array();
    
    // اگر کوکی نشست وجود دارد، آن را حذف کن
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // نابود کردن نشست
    session_destroy();
    
    // هدایت به صفحه ورود
    header('Location: login.php');
    exit;
}
?>