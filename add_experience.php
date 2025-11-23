<?php
// add_experience.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $points = $input['points'] ?? 0;
    $user_id = $_SESSION['user_id'];

    $new_level = addExperience($user_id, $points, $pdo);
    
    echo json_encode([
        'success' => true,
        'level_up' => $new_level !== false,
        'new_level' => $new_level ?: $_SESSION['level'],
        'new_experience' => $_SESSION['experience']
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>