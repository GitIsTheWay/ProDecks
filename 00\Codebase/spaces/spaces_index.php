<?php
// spaces/index.php - مدیریت Space
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$space_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify space access through project
$stmt = $pdo->prepare("
    SELECT s.*, p.name as project_name, p.color as project_color 
    FROM spaces s 
    JOIN projects p ON s.project_id = p.id 
    WHERE s.id = ? AND p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
");
$stmt->execute([$space_id, $user_id]);
$space = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$space) {
    $_SESSION['error'] = 'دسترسی به این Space مجاز نیست';
    header('Location: ../dashboard.php');
    exit;
}

// Get space decks with stats
$stmt = $pdo->prepare("
    SELECT 
        d.*,
        COUNT(DISTINCT c.id) as card_count,
        COUNT(DISTINCT CASE WHEN c.status = 'done' THEN c.id END) as completed_cards,
        COUNT(DISTINCT CASE WHEN c.priority = 'high' THEN c.id END) as high_priority_cards
    FROM decks d
    LEFT JOIN cards c ON d.id = c.deck_id
    WHERE d.space_id = ?
    GROUP BY d.id
    ORDER BY d.position ASC
");
$stmt->execute([$space_id]);
$decks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$space_stats = getSpaceStats($space_id, $pdo);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($space['name']); ?> - ProDecks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body class="space-page">
    <?php include '../includes/header.php'; ?>

    <!-- Space Header -->
    <div class="space-header" style="background: linear-gradient(135deg, <?php echo $space['color']; ?> 0%, <?php echo $space['project_color']; ?> 100%);">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-white-50">داشبورد</a></li>
                            <li class="breadcrumb-item"><a href="../projects/index.php?id=<?php echo $space['project_id']; ?>" class="text-white-50"><?php echo htmlspecialchars($space['project_name']); ?></a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($space['name']); ?></li>
                        </ol>
                    </nav>
                    <h1 class="space-title"><?php echo htmlspecialchars($space['name']); ?></h1>
                    <p class="space-description text-white-50"><?php echo htmlspecialchars($space['description'] ?? ''); ?></p>
                </div>
                <div class="col-md-4 text-start">
                    <div class="space-actions">
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#createDeckModal">
                            <i class="fas fa-plus me-1"></i>Deck جدید
                        </button>
                        <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#editSpaceModal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Space Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $space['color']; ?>">
                        <i class="fas fa-columns"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $space_stats['deck_count']; ?></h3>
                        <span>Deck</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $space['color']; ?>">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $space_stats['card_count']; ?></h3>
                        <span>Card</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $space['color']; ?>">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $space_stats['completed_cards']; ?></h3>
                        <span>انجام شده</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $space['color']; ?>">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $space_stats['card_count'] > 0 ? round(($space_stats['completed_cards'] / $space_stats['card_count']) * 100) : 0; ?>%</h3>
                        <span>پیشرفت</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decks Section -->
        <div class="row">
            <div class="col-12">
                <h3 class="section-title mb-4">Decks</h3>
                
                <?php if (empty($decks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-columns fa-4x mb-3"></i>
                        <h4>هنوز Deck ای وجود ندارد</h4>
                        <p class="text-muted">اولین Deck خود را ایجاد کنید</p>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createDeckModal">
                            ساخت Deck جدید
                        </button>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($decks as $deck): ?>
                            <div class="col-xl-4 col-lg-6 mb-4">
                                <div class="deck-card" onclick="window.location.href='../decks/index.php?id=<?php echo $deck['id']; ?>'">
                                    <div class="deck-header" style="border-color: <?php echo $deck['color']; ?>">
                                        <div class="deck-color" style="background-color: <?php echo $deck['color']; ?>"></div>
                                        <h5 class="deck-title"><?php echo htmlspecialchars($deck['name']); ?></h5>
                                        <div class="deck-actions">
                                            <button class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation(); editDeck(<?php echo $deck['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="deck-body">
                                        <p class="deck-description"><?php echo htmlspecialchars($deck['description'] ?? 'بدون توضیحات'); ?></p>
                                        <div class="deck-stats">
                                            <span class="stat">
                                                <i class="fas fa-tasks"></i>
                                                <?php echo $deck['card_count']; ?> کارت
                                            </span>
                                            <?php if ($deck['high_priority_cards'] > 0): ?>
                                                <span class="stat priority-high">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                    <?php echo $deck['high_priority_cards']; ?> مهم
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="progress mt-2">
                                            <div class="progress-bar" style="width: <?php echo $deck['card_count'] > 0 ? round(($deck['completed_cards'] / $deck['card_count']) * 100) : 0; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Deck Modal -->
    <div class="modal fade" id="createDeckModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ایجاد Deck جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../decks/create.php" method="POST">
                    <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="deckName" class="form-label">نام Deck</label>
                            <input type="text" class="form-control" id="deckName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="deckDescription" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="deckDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="deckColor" class="form-label">رنگ Deck</label>
                            <input type="color" class="form-control-color" id="deckColor" name="color" value="#38b2ac">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ایجاد Deck</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Space Modal -->
    <div class="modal fade" id="editSpaceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش Space</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit.php" method="POST">
                    <input type="hidden" name="space_id" value="<?php echo $space_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editSpaceName" class="form-label">نام Space</label>
                            <input type="text" class="form-control" id="editSpaceName" name="name" value="<?php echo htmlspecialchars($space['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSpaceDescription" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="editSpaceDescription" name="description" rows="3"><?php echo htmlspecialchars($space['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editSpaceColor" class="form-label">رنگ Space</label>
                            <input type="color" class="form-control-color" id="editSpaceColor" name="color" value="<?php echo $space['color']; ?>">
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
    function editDeck(deckId) {
        // Implementation for editing deck
        console.log('Edit deck:', deckId);
    }
    </script>
</body>
</html>