<?php
// spaces_manager.php - سیستم مدیریت Spaces مشابه Codecks
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

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
");
$stmt->execute([$user_id, $user_id]);
$spaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "مدیریت Spaces";
include 'includes/header.php';
?>

<div class="spaces-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-white mb-1">Spaces شما</h1>
                <p class="text-light mb-0">مدیریت کامل پروژه‌ها و تیم‌ها</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createSpaceModal">
                    <i class="fas fa-plus me-2"></i>Space جدید
                </button>
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#joinSpaceModal">
                    <i class="fas fa-sign-in-alt me-2"></i>پیوستن به Space
                </button>
            </div>
        </div>

        <!-- Spaces Grid -->
        <div class="row">
            <?php if (empty($spaces)): ?>
                <div class="col-12">
                    <div class="space-card text-center py-5">
                        <i class="fas fa-rocket fa-3x text-muted mb-3"></i>
                        <h4 class="text-light">هنوز Space ای ندارید</h4>
                        <p class="text-muted">برای شروع کار، اولین Space خود را ایجاد کنید</p>
                        <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createSpaceModal">
                            ایجاد Space اول
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($spaces as $space): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="space-card">
                            <div class="space-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($space['name']); ?></h5>
                                        <p class="mb-0 text-light opacity-75"><?php echo htmlspecialchars($space['description']); ?></p>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="space_decks.php?id=<?php echo $space['id']; ?>">
                                                <i class="fas fa-folder-open me-2"></i>مشاهده Decks
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="openEditSpaceModal(<?php echo $space['id']; ?>)">
                                                <i class="fas fa-edit me-2"></i>ویرایش Space
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteSpace(<?php echo $space['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>حذف Space
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="space-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-light">
                                            <h6 class="mb-0"><?php echo $space['deck_count']; ?></h6>
                                            <small class="text-muted">Decks</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-light">
                                            <h6 class="mb-0"><?php echo $space['card_count']; ?></h6>
                                            <small class="text-muted">Cards</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-light">
                                            <h6 class="mb-0"><?php echo getSpaceMembersCount($space['id'], $pdo); ?></h6>
                                            <small class="text-muted">اعضا</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="space_decks.php?id=<?php echo $space['id']; ?>" class="btn btn-outline-light w-100">
                                        <i class="fas fa-arrow-left me-2"></i>ورود به Space
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'includes/space_modals.php'; ?>

<script src="js/spaces_manager.js"></script>

<?php
function getSpaceMembersCount($space_id, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM space_members WHERE space_id = ?");
    $stmt->execute([$space_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] + 1; // +1 for owner
}

include 'includes/footer.php';
?>