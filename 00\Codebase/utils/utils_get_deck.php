<?php
// utils/get_deck.php - دریافت اطلاعات Deck (سازگار با سیستم جدید)
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID Deck مشخص نشده است']);
    exit;
}

$deck_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// بررسی دسترسی کاربر به Deck (از طریق پروژه)
$stmt = $pdo->prepare("
    SELECT 
        d.*,
        s.name as space_name,
        s.color as space_color,
        p.name as project_name,
        p.color as project_color
    FROM decks d
    JOIN spaces s ON d.space_id = s.id
    JOIN projects p ON s.project_id = p.id
    WHERE d.id = ? 
    AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
");

$stmt->execute([$deck_id, $user_id]);
$deck = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deck) {
    echo json_encode(['success' => false, 'error' => 'Deck یافت نشد یا دسترسی ندارید']);
    exit;
}

// دریافت آمار Deck
$deck_stats = getDeckStats($deck_id, $pdo);

echo json_encode([
    'success' => true,
    'deck' => $deck,
    'stats' => $deck_stats
]);
?>