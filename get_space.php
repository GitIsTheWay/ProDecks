<?php
// get_space.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$space_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify user has access to space
$stmt = $pdo->prepare("
    SELECT s.* 
    FROM spaces s 
    LEFT JOIN space_members sm ON s.id = sm.space_id 
    WHERE s.id = ? AND (s.user_id = ? OR sm.user_id = ?)
");
$stmt->execute([$space_id, $user_id, $user_id]);
$space = $stmt->fetch(PDO::FETCH_ASSOC);

if ($space) {
    echo json_encode(['success' => true, 'space' => $space]);
} else {
    echo json_encode(['success' => false, 'error' => 'Space not found']);
}
?>