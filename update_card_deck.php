<?php
// update_card_deck.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_id = $_POST['card_id'];
    $new_deck_id = $_POST['new_deck_id'];
    $user_id = $_SESSION['user_id'];

    // Verify that the user has access to the card and the new deck
    $stmt = $pdo->prepare("
        SELECT c.id 
        FROM cards c 
        JOIN decks d ON c.deck_id = d.id 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$card_id, $user_id, $user_id]);
    $card = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$card) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    // Also verify that the new deck is in the same space and user has access
    $stmt = $pdo->prepare("
        SELECT d.id 
        FROM decks d 
        JOIN spaces s ON d.space_id = s.id 
        LEFT JOIN space_members sm ON s.id = sm.space_id 
        WHERE d.id = ? AND (s.user_id = ? OR sm.user_id = ?)
    ");
    $stmt->execute([$new_deck_id, $user_id, $user_id]);
    $deck = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deck) {
        echo json_encode(['success' => false, 'error' => 'Invalid deck']);
        exit;
    }

    // Update the card's deck
    $stmt = $pdo->prepare("UPDATE cards SET deck_id = ? WHERE id = ?");
    $stmt->execute([$new_deck_id, $card_id]);

    // Add experience for moving a card
    addExperience($user_id, 2, $pdo);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>