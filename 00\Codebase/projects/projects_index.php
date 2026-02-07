<?php
// projects/index.php - مدیریت پروژه
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$project_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify project access
if (!hasProjectAccess($pdo, $project_id, $user_id)) {
    $_SESSION['error'] = 'دسترسی به این پروژه مجاز نیست';
    header('Location: ../dashboard.php');
    exit;
}

// Get project details
$stmt = $pdo->prepare("
    SELECT p.*, u.username as owner_name 
    FROM projects p 
    LEFT JOIN users u ON p.owner_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

// Get project spaces with stats
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        COUNT(DISTINCT d.id) as deck_count,
        COUNT(DISTINCT c.id) as card_count
    FROM spaces s
    LEFT JOIN decks d ON s.id = d.space_id
    LEFT JOIN cards c ON d.id = c.deck_id
    WHERE s.project_id = ?
    GROUP BY s.id
    ORDER BY s.position ASC
");
$stmt->execute([$project_id]);
$spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get project members
$members = getProjectMembers($project_id, $pdo);
$project_stats = getProjectStats($project_id, $pdo);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['name']); ?> - ProDecks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body class="project-page">
    <?php include '../includes/header.php'; ?>

    <!-- Project Header -->
    <div class="project-header" style="background: linear-gradient(135deg, <?php echo $project['color']; ?> 0%, #2d3748 100%);">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php" class="text-white-50">داشبورد</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($project['name']); ?></li>
                        </ol>
                    </nav>
                    <h1 class="project-title"><?php echo htmlspecialchars($project['name']); ?></h1>
                    <p class="project-description text-white-50"><?php echo htmlspecialchars($project['description'] ?? ''); ?></p>
                </div>
                <div class="col-md-4 text-start">
                    <div class="project-actions">
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#createSpaceModal">
                            <i class="fas fa-plus me-1"></i>Space جدید
                        </button>
                        <?php if (isProjectOwner($pdo, $project_id)): ?>
                            <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#projectSettingsModal">
                                <i class="fas fa-cog"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <!-- Project Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $project['color']; ?>">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $project_stats['space_count']; ?></h3>
                        <span>Space</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $project['color']; ?>">
                        <i class="fas fa-columns"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $project_stats['deck_count']; ?></h3>
                        <span>Deck</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $project['color']; ?>">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $project_stats['card_count']; ?></h3>
                        <span>Card</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: <?php echo $project['color']; ?>">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $project_stats['completed_cards']; ?></h3>
                        <span>انجام شده</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spaces Section -->
        <div class="row">
            <div class="col-12">
                <h3 class="section-title mb-4">Spaces</h3>
                
                <?php if (empty($spaces)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open fa-4x mb-3"></i>
                        <h4>هنوز Space ای وجود ندارد</h4>
                        <p class="text-muted">اولین Space خود را ایجاد کنید</p>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createSpaceModal">
                            ساخت Space جدید
                        </button>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($spaces as $space): ?>
                            <div class="col-xl-4 col-lg-6 mb-4">
                                <div class="space-card" onclick="window.location.href='../spaces/index.php?id=<?php echo $space['id']; ?>'">
                                    <div class="space-header" style="border-color: <?php echo $space['color']; ?>">
                                        <div class="space-color" style="background-color: <?php echo $space['color']; ?>"></div>
                                        <h5 class="space-title"><?php echo htmlspecialchars($space['name']); ?></h5>
                                        <div class="space-actions">
                                            <button class="btn btn-sm btn-outline-secondary" onclick="event.stopPropagation(); editSpace(<?php echo $space['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="space-body">
                                        <p class="space-description"><?php echo htmlspecialchars($space['description'] ?? 'بدون توضیحات'); ?></p>
                                        <div class="space-stats">
                                            <span class="stat">
                                                <i class="fas fa-columns"></i>
                                                <?php echo $space['deck_count']; ?> Deck
                                            </span>
                                            <span class="stat">
                                                <i class="fas fa-tasks"></i>
                                                <?php echo $space['card_count']; ?> Card
                                            </span>
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

    <!-- Create Space Modal -->
    <div class="modal fade" id="createSpaceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ایجاد Space جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../spaces/create.php" method="POST">
                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="spaceName" class="form-label">نام Space</label>
                            <input type="text" class="form-control" id="spaceName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="spaceDescription" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="spaceDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="spaceColor" class="form-label">رنگ Space</label>
                            <input type="color" class="form-control-color" id="spaceColor" name="color" value="#4a5568">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ایجاد Space</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editSpace(spaceId) {
        // Implementation for editing space
        console.log('Edit space:', spaceId);
    }
    </script>
</body>
</html>