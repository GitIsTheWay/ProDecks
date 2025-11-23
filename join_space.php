<?php
// join_space.php
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invite_code = trim($_POST['invite_code']);
    $user_id = $_SESSION['user_id'];

    // Find space by invite code
    $stmt = $pdo->prepare("SELECT * FROM spaces WHERE invite_code = ?");
    $stmt->execute([$invite_code]);
    $space = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($space) {
        // Check if user is already a member
        $stmt = $pdo->prepare("SELECT id FROM space_members WHERE space_id = ? AND user_id = ?");
        $stmt->execute([$space['id'], $user_id]);
        
        if ($stmt->rowCount() == 0) {
            // Add user to space as member
            $stmt = $pdo->prepare("INSERT INTO space_members (space_id, user_id, role) VALUES (?, ?, 'member')");
            $stmt->execute([$space['id'], $user_id]);
            
            $_SESSION['success_message'] = 'با موفقیت به Space پیوستید';
            header("Location: space_decks.php?id=" . $space['id']);
        } else {
            $_SESSION['error_message'] = 'شما قبلاً عضو این Space هستید';
            header("Location: spaces_manager.php");
        }
        exit;
    } else {
        $_SESSION['error_message'] = 'کد دعوت نامعتبر است';
        header("Location: spaces_manager.php");
        exit;
    }
}

header("Location: spaces_manager.php");
exit;
?>