<?php
// utils/get_space.php - دریافت اطلاعات Space (سازگار با سیستم جدید)
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID Space مشخص نشده است']);
    exit;
}

$space_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// بررسی دسترسی کاربر به Space (از طریق پروژه)
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        p.name as project_name,
        p.color as project_color,
        COUNT(DISTINCT d.id) as deck_count,
        COUNT(DISTINCT c.id) as card_count,
        COUNT(DISTINCT CASE WHEN c.status = 'done' THEN c.id END) as completed_cards
    FROM spaces s
    JOIN projects p ON s.project_id = p.id
    LEFT JOIN decks d ON s.id = d.space_id
    LEFT JOIN cards c ON d.id = c.deck_id
    WHERE s.id = ? 
    AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    GROUP BY s.id
");

$stmt->execute([$space_id, $user_id]);
$space = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$space) {
    echo json_encode(['success' => false, 'error' => 'Space یافت نشد یا دسترسی ندارید']);
    exit;
}

echo json_encode([
    'success' => true,
    'space' => $space
]);
?>