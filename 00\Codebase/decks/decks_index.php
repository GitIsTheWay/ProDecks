<?php
// decks/index.php - مدیریت Deck
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$deck_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify deck access through space and project
$stmt = $pdo->prepare("
    SELECT d.*, s.name as space_name, s.color as space_color, p.name as project_name, p.id as project_id
    FROM decks d 
    JOIN spaces s ON d.space_id = s.id 
    JOIN projects p ON s.project_id = p.id 
    WHERE d.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
");
$stmt->execute([$deck_id, $user_id]);
$deck = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deck) {
    $_SESSION['error'] = 'دسترسی به این Deck مجاز نیست';
    header('Location: ../dashboard.php');
    exit;
}

// Get deck cards with priorities and subcards count
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        u.username as assignee_name,
        COUNT(sc.id) as subcard_count,
        COUNT(CASE WHEN sc.status = 'done' THEN sc.id END) as completed_subcards
    FROM cards c
    LEFT JOIN users u ON c.assignee_id = u.id
    LEFT JOIN subcards sc ON c.id = sc.card_id
    WHERE c.deck_id = ?
    GROUP BY c.id
    ORDER BY 
        CASE c.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END,
        c.position ASC
");
$stmt->execute([$deck_id]);
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$deck_stats = getDeckStats($deck_id, $pdo);

// Group cards by priority for better organization
$priority_cards = [
    'high' => [],
    'medium' => [],
    'low' => []
];

foreach ($cards as $card) {
    $priority_cards[$card['priority']][] = $card;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($deck['name']); ?> - ProDecks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body class="deck-page">
    <?php include '../includes/header.php'; ?>

    <!-- Deck Header -->
    <div class="deck-header" style="background: linear-gradient(135deg, <?php echo $deck['color']; ?> 0%, <?php echo $deck['space_color']; ?> 100%);">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-white-50">داشبورد</a></li>
                            <li class="breadcrumb-item"><a href="../projects/index.php?id=<?php echo $deck['project_id']; ?>" class="text-white-50"><?php echo htmlspecialchars($deck['project_name']); ?></a></li>
                            <li class="breadcrumb-item"><a href="../spaces/index.php?id=<?php echo $deck['space_id']; ?>" class="text-white-50"><?php echo htmlspecialchars($deck['space_name']); ?></a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($deck['name']); ?></li>
                        </ol>
                    </nav>
                    <h1 class="deck-title"><?php echo htmlspecialchars($deck['name']); ?></h1>
                    <p class="deck-description text-white-50"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></p>
                </div>
                <div class="col-md-4 text-start">
                    <div class="deck-actions">
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#createCardModal">
                            <i class="fas fa-plus me-1"></i>کارت جدید
                        </button>
                        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#editDeckModal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Deck Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $deck['color']; ?>">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $deck_stats['card_count']; ?></h3>
                        <span>کل کارت‌ها</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $deck['color']; ?>">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $deck_stats['completed_cards']; ?></h3>
                        <span>انجام شده</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $deck['color']; ?>">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $deck_stats['high_priority_cards']; ?></h3>
                        <span>اولویت بالا</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $deck['color']; ?>">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $deck_stats['card_count'] > 0 ? round(($deck_stats['completed_cards'] / $deck_stats['card_count']) * 100) : 0; ?>%</h3>
                        <span>پیشرفت</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Section by Priority -->
        <div class="row">
            <!-- High Priority Cards -->
            <div class="col-xl-4 mb-4">
                <div class="priority-section priority-high">
                    <div class="priority-header">
                        <h5>
                            <i class="fas fa-exclamation-circle me-2"></i>
                            اولویت بالا
                            <span class="badge bg-danger ms-2"><?php echo count($priority_cards['high']); ?></span>
                        </h5>
                    </div>
                    <div class="cards-container" data-priority="high">
                        <?php foreach ($priority_cards['high'] as $card): ?>
                            <div class="card-item priority-high" onclick="openCardModal(<?php echo $card['id']; ?>)">
                                <div class="card-header">
                                    <h6 class="card-title"><?php echo htmlspecialchars($card['title']); ?></h6>
                                    <div class="card-actions">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation(); toggleCardStatus(<?php echo $card['id']; ?>)">
                                            <i class="fas fa-<?php echo $card['status'] === 'done' ? 'check-square' : 'square'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if ($card['description']): ?>
                                        <p class="card-description"><?php echo htmlspecialchars($card['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="card-meta">
                                        <?php if ($card['assignee_name']): ?>
                                            <span class="assignee">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($card['assignee_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($card['subcard_count'] > 0): ?>
                                            <span class="subcards-count">
                                                <i class="fas fa-list-ul"></i>
                                                <?php echo $card['subcard_count']; ?> زیرکارت
                                                <?php if ($card['completed_subcards'] > 0): ?>
                                                    <span class="completed">(<?php echo $card['completed_subcards']; ?> انجام شده)</span>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($card['due_date']): ?>
                                        <div class="due-date <?php echo getDueDateColor($card['due_date']) === '#f56565' ? 'overdue' : ''; ?>">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo jdate('Y/m/d', strtotime($card['due_date'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($priority_cards['high'])): ?>
                            <div class="empty-cards">
                                <i class="fas fa-inbox"></i>
                                <p>هیچ کارتی با اولویت بالا وجود ندارد</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Medium Priority Cards -->
            <div class="col-xl-4 mb-4">
                <div class="priority-section priority-medium">
                    <div class="priority-header">
                        <h5>
                            <i class="fas fa-clock me-2"></i>
                            اولویت متوسط
                            <span class="badge bg-warning ms-2"><?php echo count($priority_cards['medium']); ?></span>
                        </h5>
                    </div>
                    <div class="cards-container" data-priority="medium">
                        <?php foreach ($priority_cards['medium'] as $card): ?>
                            <div class="card-item priority-medium" onclick="openCardModal(<?php echo $card['id']; ?>)">
                                <div class="card-header">
                                    <h6 class="card-title"><?php echo htmlspecialchars($card['title']); ?></h6>
                                    <div class="card-actions">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation(); toggleCardStatus(<?php echo $card['id']; ?>)">
                                            <i class="fas fa-<?php echo $card['status'] === 'done' ? 'check-square' : 'square'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if ($card['description']): ?>
                                        <p class="card-description"><?php echo htmlspecialchars($card['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="card-meta">
                                        <?php if ($card['assignee_name']): ?>
                                            <span class="assignee">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($card['assignee_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($card['subcard_count'] > 0): ?>
                                            <span class="subcards-count">
                                                <i class="fas fa-list-ul"></i>
                                                <?php echo $card['subcard_count']; ?> زیرکارت
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($priority_cards['medium'])): ?>
                            <div class="empty-cards">
                                <i class="fas fa-inbox"></i>
                                <p>هیچ کارتی با اولویت متوسط وجود ندارد</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Low Priority Cards -->
            <div class="col-xl-4 mb-4">
                <div class="priority-section priority-low">
                    <div class="priority-header">
                        <h5>
                            <i class="fas fa-arrow-down me-2"></i>
                            اولویت کم
                            <span class="badge bg-success ms-2"><?php echo count($priority_cards['low']); ?></span>
                        </h5>
                    </div>
                    <div class="cards-container" data-priority="low">
                        <?php foreach ($priority_cards['low'] as $card): ?>
                            <div class="card-item priority-low" onclick="openCardModal(<?php echo $card['id']; ?>)">
                                <div class="card-header">
                                    <h6 class="card-title"><?php echo htmlspecialchars($card['title']); ?></h6>
                                    <div class="card-actions">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation(); toggleCardStatus(<?php echo $card['id']; ?>)">
                                            <i class="fas fa-<?php echo $card['status'] === 'done' ? 'check-square' : 'square'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if ($card['description']): ?>
                                        <p class="card-description"><?php echo htmlspecialchars($card['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="card-meta">
                                        <?php if ($card['assignee_name']): ?>
                                            <span class="assignee">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($card['assignee_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($card['subcard_count'] > 0): ?>
                                            <span class="subcards-count">
                                                <i class="fas fa-list-ul"></i>
                                                <?php echo $card['subcard_count']; ?> زیرکارت
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($priority_cards['low'])): ?>
                            <div class="empty-cards">
                                <i class="fas fa-inbox"></i>
                                <p>هیچ کارتی با اولویت کم وجود ندارد</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Card Modal -->
    <div class="modal fade" id="createCardModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ایجاد کارت جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../cards/create.php" method="POST">
                    <input type="hidden" name="deck_id" value="<?php echo $deck_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cardTitle" class="form-label">عنوان کارت</label>
                            <input type="text" class="form-control" id="cardTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="cardDescription" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="cardDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cardPriority" class="form-label">اولویت</label>
                                    <select class="form-select" id="cardPriority" name="priority">
                                        <option value="low">کم</option>
                                        <option value="medium" selected>متوسط</option>
                                        <option value="high">بالا</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cardDueDate" class="form-label">تاریخ سررسید</label>
                                    <input type="date" class="form-control" id="cardDueDate" name="due_date">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cardAssignee" class="form-label">واگذار شده به</label>
                            <select class="form-select" id="cardAssignee" name="assignee_id">
                                <option value="">انتخاب کاربر</option>
                                <?php
                                $members = getProjectMembers($deck['project_id'], $pdo);
                                foreach ($members as $member): ?>
                                    <option value="<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

    <!-- Edit Deck Modal -->
    <div class="modal fade" id="editDeckModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش Deck</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit.php" method="POST">
                    <input type="hidden" name="deck_id" value="<?php echo $deck_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editDeckName" class="form-label">نام Deck</label>
                            <input type="text" class="form-control" id="editDeckName" name="name" value="<?php echo htmlspecialchars($deck['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDeckDescription" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="editDeckDescription" name="description" rows="3"><?php echo htmlspecialchars($deck['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editDeckColor" class="form-label">رنگ Deck</label>
                            <input type="color" class="form-control-color" id="editDeckColor" name="color" value="<?php echo $deck['color']; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openCardModal(cardId) {
        // Implementation for opening card modal
        console.log('Open card modal:', cardId);
    }

    function toggleCardStatus(cardId) {
        fetch('../cards/toggle_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'card_id=' + cardId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('خطا در تغییر وضعیت کارت');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در ارتباط با سرور');
        });
    }
    </script>
</body>
</html>