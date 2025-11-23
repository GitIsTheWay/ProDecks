<?php
// dashboard.php - نسخه پیشرفته
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$page_title = "داشبورد";
$user_id = $_SESSION['user_id'];

// Get user's spaces
$stmt = $pdo->prepare("
    SELECT s.*, COUNT(DISTINCT d.id) as deck_count, COUNT(DISTINCT c.id) as card_count
    FROM spaces s 
    LEFT JOIN decks d ON s.id = d.space_id 
    LEFT JOIN cards c ON d.id = c.deck_id
    WHERE s.user_id = ? OR s.id IN (
        SELECT space_id FROM space_members WHERE user_id = ?
    )
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT 6
");
$stmt->execute([$user_id, $user_id]);
$spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's projects (برای سازگاری)
$stmt = $pdo->prepare("
    SELECT p.*, pm.role 
    FROM projects p 
    JOIN project_members pm ON p.id = pm.project_id 
    WHERE pm.user_id = ?
    ORDER BY p.created_at DESC
    LIMIT 3
");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user stats
$stmt = $pdo->prepare("SELECT level, experience FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_stats = $stmt->fetch(PDO::FETCH_ASSOC);

$experience_percentage = ($user_stats['experience'] % 100);
$next_level_exp = 100 - ($user_stats['experience'] % 100);

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">داشبورد</h1>
                    <p class="text-muted mb-0">خوش آمدید <?php echo $_SESSION['username']; ?>! وضعیت پروژه‌های خود را بررسی کنید</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="spaces_manager.php" class="btn btn-gradient">
                        <i class="fas fa-rocket me-2"></i>مدیریت Spaces
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title text-primary"><?php echo count($spaces); ?></h3>
                            <p class="card-text">Spaces فعال</p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-layer-group fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title text-success"><?php echo $user_stats['level']; ?></h3>
                            <p class="card-text">سطح شما</p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-trophy fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title text-warning"><?php echo $user_stats['experience']; ?></h3>
                            <p class="card-text">امتیاز تجربه</p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-star fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title text-info"><?php echo $next_level_exp; ?></h3>
                            <p class="card-text">XP تا سطح بعدی</p>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-bolt fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Spaces Section -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Spaces اخیر</h5>
                    <a href="spaces_manager.php" class="btn btn-sm btn-outline-primary">مشاهده همه</a>
                </div>
                <div class="card-body">
                    <?php if (empty($spaces)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-rocket fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">هنوز Space ای ندارید</h5>
                            <p class="text-muted">برای شروع کار، اولین Space خود را ایجاد کنید</p>
                            <a href="spaces_manager.php" class="btn btn-primary">ایجاد Space اول</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($spaces as $space): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="space-card-small">
                                        <div class="d-flex align-items-center">
                                            <div class="space-color-indicator" style="background-color: <?php echo $space['color']; ?>"></div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($space['name']); ?></h6>
                                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($space['description']); ?></p>
                                                <div class="d-flex text-muted small">
                                                    <span class="me-3"><i class="fas fa-layer-group me-1"></i> <?php echo $space['deck_count']; ?> دک</span>
                                                    <span><i class="fas fa-sticky-note me-1"></i> <?php echo $space['card_count']; ?> کارت</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="space_decks.php?id=<?php echo $space['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                                ورود به Space
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Progress Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">پیشرفت شما</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="level-badge mb-2">سطح <?php echo $user_stats['level']; ?></div>
                        <p class="text-muted small"><?php echo $user_stats['experience']; ?> XP از <?php echo ($user_stats['level'] * 100); ?> XP</p>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-gradient" style="width: <?php echo $experience_percentage; ?>%"></div>
                    </div>
                    <p class="text-center small text-muted mb-0"><?php echo $next_level_exp; ?> XP تا سطح بعدی</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">دسترسی سریع</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="spaces_manager.php" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus me-2"></i>ایجاد Space جدید
                        </a>
                        <a href="tutorial.php" class="btn btn-outline-secondary text-start">
                            <i class="fas fa-graduation-cap me-2"></i>آموزش استفاده
                        </a>
                        <a href="#" class="btn btn-outline-info text-start" data-bs-toggle="modal" data-bs-target="#achievementsModal">
                            <i class="fas fa-trophy me-2"></i>دستاوردها
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card-primary { border-right: 4px solid #667eea; }
.stats-card-success { border-right: 4px solid #10b981; }
.stats-card-warning { border-right: 4px solid #f59e0b; }
.stats-card-info { border-right: 4px solid #3b82f6; }

.stats-icon {
    opacity: 0.7;
}

.space-card-small {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.space-card-small:hover {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.space-color-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.level-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 1.1rem;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
}
</style>

<?php include 'includes/footer.php'; ?>