<?php
// get_deck.php - دریافت اطلاعات Deck برای ویرایش
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$deck_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify user has access to deck
$stmt = $pdo->prepare("
    SELECT d.* 
    FROM decks d 
    JOIN spaces s ON d.space_id = s.id 
    LEFT JOIN space_members sm ON s.id = sm.space_id 
    WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
");
$stmt->execute([$deck_id, $user_id, $user_id]);
$deck = $stmt->fetch(PDO::FETCH_ASSOC);

if ($deck) {
    echo json_encode(['success' => true, 'deck' => $deck]);
} else {
    echo json_encode(['success' => false, 'error' => 'Deck not found']);
}
?>