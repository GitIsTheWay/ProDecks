<?php
// utils/get_card.php - دریافت اطلاعات کارت (سازگار با سیستم جدید)
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID کارت مشخص نشده است']);
    exit;
}

$card_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// بررسی دسترسی کاربر به کارت (از طریق پروژه)
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        d.name as deck_name,
        d.color as deck_color,
        s.name as space_name,
        s.color as space_color,
        p.name as project_name,
        p.color as project_color,
        u.username as assignee_name,
        u.full_name as assignee_full_name,
        u.avatar as assignee_avatar,
        COUNT(sc.id) as subcard_count,
        COUNT(CASE WHEN sc.status = 'done' THEN sc.id END) as completed_subcards
    FROM cards c
    JOIN decks d ON c.deck_id = d.id
    JOIN spaces s ON d.space_id = s.id
    JOIN projects p ON s.project_id = p.id
    LEFT JOIN users u ON c.assignee_id = u.id
    LEFT JOIN subcards sc ON c.id = sc.card_id
    WHERE c.id = ? 
    AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    GROUP BY c.id
");

$stmt->execute([$card_id, $user_id]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$card) {
    echo json_encode(['success' => false, 'error' => 'کارت یافت نشد یا دسترسی ندارید']);
    exit;
}

// دریافت زیرکارت‌ها
$stmt = $pdo->prepare("
    SELECT * FROM subcards 
    WHERE card_id = ? 
    ORDER BY position ASC
");
$stmt->execute([$card_id]);
$subcards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// فرمت‌دهی تاریخ‌ها
if ($card['due_date']) {
    $card['due_date_formatted'] = jdate('Y/m/d', strtotime($card['due_date']));
    $card['due_date_color'] = getDueDateColor($card['due_date']);
}

$card['priority_text'] = getPriorityText($card['priority']);
$card['priority_color'] = getPriorityColor($card['priority']);
$card['subcards'] = $subcards;

echo json_encode([
    'success' => true,
    'card' => $card
]);
?>