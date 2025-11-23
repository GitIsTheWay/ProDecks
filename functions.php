<?php
// includes/functions.php - نسخه پیشرفته

function addExperience($user_id, $experience, $pdo) {
    $stmt = $pdo->prepare("UPDATE users SET experience = experience + ? WHERE id = ?");
    $stmt->execute([$experience, $user_id]);
    
    // Check for level up
    $stmt = $pdo->prepare("SELECT experience, level FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $new_level = floor($user['experience'] / 100) + 1;
    if ($new_level > $user['level']) {
        $stmt = $pdo->prepare("UPDATE users SET level = ? WHERE id = ?");
        $stmt->execute([$new_level, $user_id]);
        
        // Update session
        $_SESSION['level'] = $new_level;
        $_SESSION['experience'] = $user['experience'] + $experience;
        
        return $new_level;
    }
    
    // Update session experience
    $_SESSION['experience'] = $user['experience'] + $experience;
    return false;
}

function addAchievement($user_id, $achievement_type, $pdo) {
    $stmt = $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_type) VALUES (?, ?)");
    return $stmt->execute([$user_id, $achievement_type]);
}

// توابع جدید برای سیستم Spaces
function getSpaceMembers($space_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, sm.role 
        FROM space_members sm 
        JOIN users u ON sm.user_id = u.id 
        WHERE sm.space_id = ?
        UNION
        SELECT u.id, u.username, u.full_name, 'owner' as role
        FROM spaces s
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ");
    $stmt->execute([$space_id, $space_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSpaceStats($space_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT d.id) as deck_count,
            COUNT(DISTINCT c.id) as card_count,
            COUNT(DISTINCT CASE WHEN c.parent_card_id IS NULL THEN c.id END) as main_card_count,
            COUNT(DISTINCT CASE WHEN c.parent_card_id IS NOT NULL THEN c.id END) as subcard_count,
            COUNT(DISTINCT CASE WHEN c.status = 'done' THEN c.id END) as completed_cards
        FROM decks d 
        LEFT JOIN cards c ON d.id = c.deck_id 
        WHERE d.space_id = ?
    ");
    $stmt->execute([$space_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProjectMembers($project_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, pm.role 
        FROM project_members pm 
        JOIN users u ON pm.user_id = u.id 
        WHERE pm.project_id = ?
    ");
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProjectStats($project_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT d.id) as deck_count,
            COUNT(c.id) as card_count,
            COUNT(CASE WHEN c.deck_id = (SELECT id FROM decks WHERE project_id = ? AND name = 'انجام شده') THEN 1 END) as completed_cards
        FROM decks d 
        LEFT JOIN cards c ON d.id = c.deck_id 
        WHERE d.project_id = ?
    ");
    $stmt->execute([$project_id, $project_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ✅ تابع generateInviteCode فقط در اینجا نگه داشته شده
function generateInviteCode() {
    return strtoupper(substr(md5(uniqid()), 0, 8));
}

// توابع نمایش برای کارت‌ها
function getPriorityColor($priority) {
    switch ($priority) {
        case 'critical': return 'danger';
        case 'high': return 'warning';
        case 'medium': return 'info';
        case 'low': return 'success';
        default: return 'secondary';
    }
}

function getPriorityText($priority) {
    switch ($priority) {
        case 'critical': return 'بحرانی';
        case 'high': return 'زیاد';
        case 'medium': return 'متوسط';
        case 'low': return 'کم';
        default: return 'نامشخص';
    }
}

function getDueDateColor($due_date) {
    if (!$due_date) return 'secondary';
    
    $today = new DateTime();
    $due = new DateTime($due_date);
    $diff = $today->diff($due)->days;
    
    if ($due < $today) return 'danger';
    if ($diff <= 2) return 'warning';
    return 'info';
}

// تابع ساده برای تاریخ شمسی
function jdate($format, $timestamp = '') {
    if ($timestamp == '') {
        $timestamp = time();
    }
    return date($format, $timestamp);
}
?>