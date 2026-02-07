<?php
// includes/functions.php - Advanced Codecks-like Functions

// Experience System
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
        $_SESSION['user_level'] = $new_level;
    }
    
    // Update session experience
    $_SESSION['user_experience'] = $user['experience'] + $experience;
    
    return ['new_level' => $new_level, 'new_experience' => $user['experience'] + $experience];
}

// Project Functions
function getProjectMembers($project_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.avatar, pm.role 
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
            COUNT(DISTINCT s.id) as space_count,
            COUNT(DISTINCT d.id) as deck_count,
            COUNT(DISTINCT c.id) as card_count,
            COUNT(DISTINCT CASE WHEN c.status = 'done' THEN c.id END) as completed_cards
        FROM projects p
        LEFT JOIN spaces s ON p.id = s.project_id
        LEFT JOIN decks d ON s.id = d.space_id
        LEFT JOIN cards c ON d.id = c.deck_id
        WHERE p.id = ?
    ");
    $stmt->execute([$project_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Space Functions
function getSpaceStats($space_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT d.id) as deck_count,
            COUNT(DISTINCT c.id) as card_count,
            COUNT(DISTINCT CASE WHEN c.status = 'done' THEN c.id END) as completed_cards
        FROM spaces s
        LEFT JOIN decks d ON s.id = d.space_id
        LEFT JOIN cards c ON d.id = c.deck_id
        WHERE s.id = ?
    ");
    $stmt->execute([$space_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Deck Functions
function getDeckStats($deck_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as card_count,
            COUNT(CASE WHEN status = 'done' THEN 1 END) as completed_cards,
            COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority_cards
        FROM cards 
        WHERE deck_id = ?
    ");
    $stmt->execute([$deck_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Card Priority Functions
function getPriorityColor($priority) {
    switch ($priority) {
        case 'high': return '#f56565';
        case 'medium': return '#ed8936';
        case 'low': return '#48bb78';
        default: return '#a0aec0';
    }
}

function getPriorityText($priority) {
    switch ($priority) {
        case 'high': return 'بالا';
        case 'medium': return 'متوسط';
        case 'low': return 'کم';
        default: return 'نامشخص';
    }
}

// Date Functions
function getDueDateColor($due_date) {
    if (!$due_date) return '#a0aec0';
    
    $today = new DateTime();
    $due = new DateTime($due_date);
    $diff = $today->diff($due)->days;
    
    if ($due < $today) return '#f56565'; // Overdue
    if ($diff <= 2) return '#ed8936'; // Due soon
    return '#48bb78'; // On track
}

// Access Control Functions
function hasProjectAccess($pdo, $project_id, $user_id = null) {
    if (!$user_id) $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("
        SELECT id FROM project_members 
        WHERE project_id = ? AND user_id = ?
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->rowCount() > 0;
}

function isProjectOwner($pdo, $project_id, $user_id = null) {
    if (!$user_id) $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("
        SELECT id FROM projects 
        WHERE id = ? AND owner_id = ?
    ");
    $stmt->execute([$project_id, $user_id]);
    return $stmt->rowCount() > 0;
}

// Utility Functions
function generateInviteCode() {
    return substr(str_shuffle(str_repeat('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', 8)), 0, 8);
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function showNotification($message, $type = 'info') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}
?>