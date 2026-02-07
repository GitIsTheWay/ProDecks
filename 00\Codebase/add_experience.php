<?php
// add_experience.php - سیستم تجربه کاربری سازگار با معماری جدید
require_once 'includes/config.php';
require_once 'includes/auth.php';

// فقط کاربران لاگین شده می‌توانند تجربه اضافه کنند
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'لطفاً ابتدا وارد شوید']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // دریافت داده‌ها از JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $points = isset($input['points']) ? intval($input['points']) : 0;
    $activity = isset($input['activity']) ? trim($input['activity']) : 'general';
    
    // اعتبارسنجی
    if ($points <= 0 || $points > 100) {
        echo json_encode(['success' => false, 'error' => 'مقدار امتیاز نامعتبر است']);
        exit;
    }
    
    try {
        // اضافه کردن تجربه
        $result = addExperience($user_id, $points, $pdo);
        
        // ثبت فعالیت (اختیاری)
        $stmt = $pdo->prepare("INSERT INTO user_activities (user_id, activity_type, points_earned) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $activity, $points]);
        
        // پاسخ موفق
        echo json_encode([
            'success' => true,
            'points_added' => $points,
            'new_level' => $result['new_level'],
            'new_experience' => $result['new_experience'],
            'level_up' => $result['new_level'] > $_SESSION['user_level']
        ]);
        
    } catch (Exception $e) {
        error_log("Add Experience Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'خطا در افزودن تجربه']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر']);
}
?>