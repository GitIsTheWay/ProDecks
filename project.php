<?php
// project.php
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$project_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Check if user has access to project
$stmt = $pdo->prepare("
    SELECT p.*, pm.role 
    FROM projects p 
    JOIN project_members pm ON p.id = pm.project_id 
    WHERE p.id = ? AND pm.user_id = ?
");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header("Location: dashboard.php");
    exit;
}

// Get project decks with cards
$stmt = $pdo->prepare("
    SELECT d.*, 
           COUNT(c.id) as card_count
    FROM decks d 
    LEFT JOIN cards c ON d.id = c.deck_id 
    WHERE d.project_id = ? 
    GROUP BY d.id 
    ORDER BY d.position
");
$stmt->execute([$project_id]);
$decks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cards for each deck
foreach ($decks as &$deck) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.username as assignee_name, u.full_name as assignee_fullname
        FROM cards c 
        LEFT JOIN users u ON c.assignee_id = u.id 
        WHERE c.deck_id = ? 
        ORDER BY c.position
    ");
    $stmt->execute([$deck['id']]);
    $deck['cards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = $project['name'];
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><?php echo htmlspecialchars($project['name']); ?></h2>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($project['description']); ?></p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addDeckModal">
            <i class="fas fa-plus me-2"></i>افزودن دک
        </button>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#projectMembersModal">
            <i class="fas fa-users me-2"></i>اعضای پروژه
        </button>
        <?php if ($project['role'] == 'owner'): ?>
        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
            <i class="fas fa-user-plus me-2"></i>دعوت عضو
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Project Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?php echo count($decks); ?></h4>
                <p class="mb-0">تعداد دک‌ها</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?php echo array_sum(array_column($decks, 'card_count')); ?></h4>
                <p class="mb-0">کل کارت‌ها</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4><?php 
                    $completed_cards = 0;
                    foreach ($decks as $deck) {
                        if ($deck['name'] == 'انجام شده') {
                            $completed_cards = $deck['card_count'];
                            break;
                        }
                    }
                    echo $completed_cards;
                ?></h4>
                <p class="mb-0">کارت‌های انجام شده</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?php echo count(getProjectMembers($project_id, $pdo)); ?></h4>
                <p class="mb-0">اعضای تیم</p>
            </div>
        </div>
    </div>
</div>

<div class="board-container">
    <div class="row" id="decks-container">
        <?php foreach ($decks as $deck): ?>
            <div class="col-md-4 mb-4" data-deck-id="<?php echo $deck['id']; ?>">
                <div class="card deck-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?php echo htmlspecialchars($deck['name']); ?></h6>
                        <span class="badge bg-primary"><?php echo $deck['card_count']; ?></span>
                    </div>
                    <div class="card-body deck-body" data-deck-id="<?php echo $deck['id']; ?>">
                        <?php foreach ($deck['cards'] as $card): ?>
                            <div class="card task-card mb-2 draggable-card" 
                                 data-card-id="<?php echo $card['id']; ?>"
                                 draggable="true">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($card['title']); ?></h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="openEditCardModal(<?php echo $card['id']; ?>)">ویرایش</a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCard(<?php echo $card['id']; ?>)">حذف</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($card['description'])): ?>
                                        <p class="card-text small text-muted"><?php echo htmlspecialchars($card['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <?php if ($card['assignee_name']): ?>
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($card['assignee_fullname'] ?: $card['assignee_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($card['due_date']): ?>
                                            <span class="badge bg-<?php echo getDueDateColor($card['due_date']); ?>">
                                                <i class="fas fa-calendar me-1"></i><?php echo jdate('Y/m/d', strtotime($card['due_date'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($card['priority'] != 'medium'): ?>
                                        <div class="mt-2">
                                            <span class="badge bg-<?php echo getPriorityColor($card['priority']); ?>">
                                                <?php echo getPriorityText($card['priority']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-outline-primary w-100" 
                                onclick="openAddCardModal(<?php echo $deck['id']; ?>)">
                            <i class="fas fa-plus me-1"></i>افزودن کارت
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Deck Modal -->
<div class="modal fade" id="addDeckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن دک جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add_deck.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deck_name" class="form-label">نام دک</label>
                        <input type="text" class="form-control" id="deck_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد دک</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Card Modal -->
<div class="modal fade" id="addCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن کارت جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add_card.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <input type="hidden" id="card_deck_id" name="deck_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="card_title" class="form-label">عنوان کارت</label>
                        <input type="text" class="form-control" id="card_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="card_description" class="form-label">توضیحات</label>
                        <textarea class="form-control" id="card_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="card_assignee" class="form-label">واگذار شده به</label>
                                <select class="form-select" id="card_assignee" name="assignee_id">
                                    <option value="">انتخاب کاربر</option>
                                    <?php
                                    $members = getProjectMembers($project_id, $pdo);
                                    foreach ($members as $member): ?>
                                        <option value="<?php echo $member['id']; ?>">
                                            <?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="card_priority" class="form-label">اولویت</label>
                                <select class="form-select" id="card_priority" name="priority">
                                    <option value="low">کم</option>
                                    <option value="medium" selected>متوسط</option>
                                    <option value="high">زیاد</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="card_due_date" class="form-label">تاریخ سررسید</label>
                        <input type="date" class="form-control" id="card_due_date" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ایجاد کارت</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Card Modal -->
<div class="modal fade" id="editCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش کارت</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="edit_card.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <input type="hidden" id="edit_card_id" name="card_id">
                <div class="modal-body" id="editCardModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Project Members Modal -->
<div class="modal fade" id="projectMembersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اعضای پروژه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <?php
                    $members = getProjectMembers($project_id, $pdo);
                    foreach ($members as $member): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?></strong>
                                <small class="text-muted d-block">@<?php echo $member['username']; ?></small>
                            </div>
                            <span class="badge bg-<?php echo $member['role'] == 'owner' ? 'primary' : 'secondary'; ?>">
                                <?php echo $member['role'] == 'owner' ? 'مالک' : 'عضو'; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Invite Member Modal -->
<div class="modal fade" id="inviteMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">دعوت عضو جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="invite_member.php" method="post">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invite_email" class="form-label">ایمیل کاربر</label>
                        <input type="email" class="form-control" id="invite_email" name="email" required>
                        <div class="form-text">ایمیلی که کاربر با آن ثبت نام کرده است</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ارسال دعوت</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/project.js"></script>
<script>
function openEditCardModal(cardId) {
    fetch('get_card.php?id=' + cardId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editCardModalBody').innerHTML = html;
            document.getElementById('edit_card_id').value = cardId;
            var editModal = new bootstrap.Modal(document.getElementById('editCardModal'));
            editModal.show();
        });
}

function deleteCard(cardId) {
    if (confirm('آیا از حذف این کارت اطمینان دارید؟')) {
        fetch('delete_card.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'card_id=' + cardId + '&project_id=<?php echo $project_id; ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('خطا در حذف کارت');
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>

<?php
// Helper functions for card display
function getPriorityColor($priority) {
    switch ($priority) {
        case 'high': return 'danger';
        case 'medium': return 'warning';
        case 'low': return 'success';
        default: return 'secondary';
    }
}

function getPriorityText($priority) {
    switch ($priority) {
        case 'high': return 'فوری';
        case 'medium': return 'متوسط';
        case 'low': return 'کم';
        default: return 'نامشخص';
    }
}

function getDueDateColor($due_date) {
    $today = new DateTime();
    $due = new DateTime($due_date);
    $diff = $today->diff($due)->days;
    
    if ($due < $today) return 'danger';
    if ($diff <= 2) return 'warning';
    return 'info';
}

// Simple jdate function for Persian date
function jdate($format, $timestamp = '') {
    if ($timestamp == '') {
        $timestamp = time();
    }
    return date($format, $timestamp);
}
?>