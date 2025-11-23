<?php
// get_card.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    exit;
}

$card_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify user has access to this card
$stmt = $pdo->prepare("
    SELECT c.*, d.space_id 
    FROM cards c 
    JOIN decks d ON c.deck_id = d.id 
    JOIN spaces s ON d.space_id = s.id 
    LEFT JOIN space_members sm ON s.id = sm.space_id 
    WHERE c.id = ? AND (s.user_id = ? OR sm.user_id = ?)
");
$stmt->execute([$card_id, $user_id, $user_id]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);

if ($card) {
    $space_id = $card['space_id'];
    $members = getSpaceMembers($space_id, $pdo);
    ?>
    <div class="mb-3">
        <label for="edit_card_title" class="form-label">عنوان کارت</label>
        <input type="text" class="form-control" id="edit_card_title" name="title" value="<?php echo htmlspecialchars($card['title']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="edit_card_description" class="form-label">توضیحات</label>
        <textarea class="form-control" id="edit_card_description" name="description" rows="3"><?php echo htmlspecialchars($card['description']); ?></textarea>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="edit_card_assignee" class="form-label">واگذار شده به</label>
                <select class="form-select" id="edit_card_assignee" name="assignee_id">
                    <option value="">انتخاب کاربر</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['id']; ?>" <?php echo $card['assignee_id'] == $member['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="edit_card_priority" class="form-label">اولویت</label>
                <select class="form-select" id="edit_card_priority" name="priority">
                    <option value="low" <?php echo $card['priority'] == 'low' ? 'selected' : ''; ?>>کم</option>
                    <option value="medium" <?php echo $card['priority'] == 'medium' ? 'selected' : ''; ?>>متوسط</option>
                    <option value="high" <?php echo $card['priority'] == 'high' ? 'selected' : ''; ?>>زیاد</option>
                    <option value="critical" <?php echo $card['priority'] == 'critical' ? 'selected' : ''; ?>>بحرانی</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="edit_card_due_date" class="form-label">تاریخ سررسید</label>
                <input type="date" class="form-control" id="edit_card_due_date" name="due_date" value="<?php echo $card['due_date']; ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="edit_card_time_estimate" class="form-label">تخمین زمان (ساعت)</label>
                <input type="number" class="form-control" id="edit_card_time_estimate" name="time_estimate" value="<?php echo $card['time_estimate']; ?>" step="0.5" min="0">
            </div>
        </div>
    </div>
    <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
    <?php
}
?>