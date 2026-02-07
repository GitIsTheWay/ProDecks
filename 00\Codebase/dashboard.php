<?php
// dashboard.php - Dashboard پیشرفته Codecks-like
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's projects with stats
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        COUNT(DISTINCT s.id) as space_count,
        COUNT(DISTINCT d.id) as deck_count,
        COUNT(DISTINCT c.id) as card_count,
        u.username as owner_name
    FROM projects p
    LEFT JOIN spaces s ON p.id = s.project_id
    LEFT JOIN decks d ON s.id = d.space_id
    LEFT JOIN cards c ON d.id = c.deck_id
    LEFT JOIN users u ON p.owner_id = u.id
    WHERE p.id IN (SELECT project_id FROM project_members WHERE user_id = ?)
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user stats
$stmt = $pdo->prepare("SELECT level, experience FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate experience percentage
$exp_percentage = ($user_stats['experience'] % 100);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد - ProDecks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid py-4">
        <!-- User Stats Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="user-stats-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">خوش آمدید, <?php echo $_SESSION['user_name']; ?>!</h2>
                            <p class="text-muted mb-0">مدیریت پروژه‌های خود را آغاز کنید</p>
                        </div>
                        <div class="level-section">
                            <div class="level-badge">
                                <i class="fas fa-trophy"></i>
                                سطح <?php echo $user_stats['level']; ?>
                            </div>
                            <div class="experience-bar mt-2">
                                <div class="experience-fill" style="width: <?php echo $exp_percentage; ?>%"></div>
                                <div class="experience-text"><?php echo $user_stats['experience']; ?> XP</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="section-title">پروژه‌های شما</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                        <i class="fas fa-plus me-2"></i>پروژه جدید
                    </button>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="row">
            <?php if (empty($projects)): ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-folder-open fa-4x mb-3"></i>
                        <h4>هنوز پروژه‌ای ندارید</h4>
                        <p class="text-muted">اولین پروژه خود را ایجاد کنید و کارتان را شروع کنید</p>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                            ساخت پروژه جدید
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                        <div class="project-card" onclick="window.location.href='projects/index.php?id=<?php echo $project['id']; ?>'">
                            <div class="project-header" style="background-color: <?php echo $project['color']; ?>">
                                <div class="project-actions">
                                    <button class="btn btn-sm btn-light" onclick="event.stopPropagation(); editProject(<?php echo $project['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="project-body">
                                <h5 class="project-title"><?php echo htmlspecialchars($project['name']); ?></h5>
                                <p class="project-description"><?php echo htmlspecialchars($project['description'] ?? 'بدون توضیحات'); ?></p>
                                
                                <div class="project-stats">
                                    <div class="stat">
                                        <i class="fas fa-layer-group"></i>
                                        <span><?php echo $project['space_count']; ?> Space</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-columns"></i>
                                        <span><?php echo $project['deck_count']; ?> Deck</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-tasks"></i>
                                        <span><?php echo $project['card_count']; ?> Card</span>
                                    </div>
                                </div>
                                
                                <div class="project-footer">
                                    <small class="text-muted">
                                        ایجاد شده توسط <?php echo htmlspecialchars($project['owner_name']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div class="modal fade" id="createProjectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ایجاد پروژه جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="projects/create.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="projectName" class="form-label">نام پروژه</label>
                            <input type="text" class="form-control" id="projectName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectDescription" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="projectDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="projectColor" class="form-label">رنگ پروژه</label>
                            <input type="color" class="form-control-color" id="projectColor" name="color" value="#667eea">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ایجاد پروژه</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editProject(projectId) {
        // Implementation for editing project
        console.log('Edit project:', projectId);
    }
    </script>
</body>
</html>