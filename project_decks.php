<?php
// project_decks.php
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

// Get project decks with cards and spaces
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

$page_title = $project['name'] . " - Decks";
include 'includes/header.php';
?>

<div class="decks-container">
    <!-- Header -->
    <div class="decks-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><?php echo htmlspecialchars($project['name']); ?></h1>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($project['description']); ?></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addDeckModal">
                    <i class="fas fa-plus me-2"></i>دک جدید
                </button>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#projectSettingsModal">
                    <i class="fas fa-cog me-2"></i>تنظیمات
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body text-center py-2">
                    <h6 class="mb-0"><?php echo count($decks); ?></h6>
                    <small class="text-muted">دک</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body text-center py-2">
                    <h6 class="mb-0"><?php echo array_sum(array_column($decks, 'card_count')); ?></h6>
                    <small class="text-muted">کارت</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body text-center py-2">
                    <h6 class="mb-0"><?php echo getCompletedCardsCount($decks); ?></h6>
                    <small class="text-muted">تکمیل شده</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body text-center py-2">
                    <h6 class="mb-0"><?php echo getUrgentCardsCount($decks); ?></h6>
                    <small class="text-muted">فوری</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body text-center py-2">
                    <h6 class="mb-0"><?php echo getProgressPercentage($decks); ?>%</h6>
                    <small class="text-muted">پیشرفت</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stat-card">
                <div class="card-body text-center py-2">
                    <h6 class="mb-0"><?php echo $_SESSION['level']; ?></h6>
                    <small class="text-muted">سطح</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Decks Board -->
    <div class="decks-board">
        <div class="row" id="decks-container">
            <?php foreach ($decks as $deck): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4" data-deck-id="<?php echo $deck['id']; ?>">
                    <div class="card deck-card">
                        <div class="card-header deck-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="deck-title mb-0"><?php echo htmlspecialchars($deck['name']); ?></h6>
                                <div class="deck-actions">
                                    <span class="badge bg-light text-dark deck-count"><?php echo $deck['card_count']; ?></span>
                                    <div class="dropdown deck-dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0 border-0" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" 
                                                   onclick="openEditDeckModal(<?php echo $deck['id']; ?>, '<?php echo htmlspecialchars($deck['name']); ?>')">
                                                <i class="fas fa-edit me-2"></i>ویرایش
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" 
                                                   onclick="deleteDeck(<?php echo $deck['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>حذف
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body deck-body" data-deck-id="<?php echo $deck['id']; ?>">
                            <?php foreach ($deck['cards'] as $card): ?>
                                <div class="card task-card mb-3 draggable-card animated-card" 
                                     data-card-id="<?php echo $card['id']; ?>"
                                     draggable="true">
                                    <div class="card-body p-3">
                                        <!-- Card Header -->
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0 flex-grow-1 me-2">
                                                <?php echo htmlspecialchars($card['title']); ?>
                                            </h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0 card-menu-btn" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" 
                                                           onclick="openEditCardModal(<?php echo $card['id']; ?>)">
                                                        <i class="fas fa-edit me-2"></i>ویرایش
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" href="#" 
                                                           onclick="deleteCard(<?php echo $card['id']; ?>)">
                                                        <i class="fas fa-trash me-2"></i>حذف
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <!-- Card Description -->
                                        <?php if (!empty($card['description'])): ?>
                                            <p class="card-text small text-muted card-description">
                                                <?php echo htmlspecialchars($card['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <!-- Card Meta -->
                                        <div class="card-meta mt-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <!-- Assignee -->
                                                <?php if ($card['assignee_name']): ?>
                                                    <div class="assignee-info">
                                                        <span class="badge bg-light text-dark">
                                                            <i class="fas fa-user me-1"></i>
                                                            <?php echo htmlspecialchars($card['assignee_fullname'] ?: $card['assignee_name']); ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <div></div>
                                                <?php endif; ?>
                                                
                                                <!-- Due Date -->
                                                <?php if ($card['due_date']): ?>
                                                    <div class="due-date">
                                                        <span class="badge bg-<?php echo getDueDateColor($card['due_date']); ?>">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?php echo jdate('Y/m/d', strtotime($card['due_date'])); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Priority -->
                                            <?php if ($card['priority'] != 'medium'): ?>
                                                <div class="priority mt-2">
                                                    <span class="badge bg-<?php echo getPriorityColor($card['priority']); ?> priority-badge">
                                                        <i class="fas fa-flag me-1"></i>
                                                        <?php echo getPriorityText($card['priority']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Empty State -->
                            <?php if (empty($deck['cards'])): ?>
                                <div class="empty-deck text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted small mb-0">هیچ کارتی وجود ندارد</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Add Card Button -->
                        <div class="card-footer deck-footer">
                            <button class="btn btn-sm btn-outline-primary w-100 add-card-btn" 
                                    onclick="openAddCardModal(<?php echo $deck['id']; ?>)">
                                <i class="fas fa-plus me-1"></i>افزودن کارت
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Add New Deck Column -->
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card add-deck-column">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-plus-circle fa-2x text-muted mb-3"></i>
                        <h6 class="text-muted">افزودن دک جدید</h6>
                        <button class="btn btn-outline-primary btn-sm mt-2" 
                                data-bs-toggle="modal" data-bs-target="#addDeckModal">
                            ایجاد دک
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'includes/deck_modals.php'; ?>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="js/project_decks.js"></script>

<style>
.decks-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 20px 0;
}

.deck-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    background: white;
}

.deck-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.deck-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 12px 12px 0 0 !important;
    border: none;
    padding: 15px 20px;
}

.deck-title {
    font-weight: 600;
    font-size: 0.95rem;
}

.deck-body {
    min-height: 400px;
    max-height: 70vh;
    overflow-y: auto;
    padding: 15px;
    background: #f8fafc;
    transition: all 0.3s ease;
}

.deck-body.drag-over {
    background: #e2e8f0;
    border: 2px dashed #667eea;
}

.task-card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border-right: 4px solid #667eea;
    cursor: move;
}

.task-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateX(-2px);
}

.task-card.dragging {
    opacity: 0.6;
    transform: rotate(5deg);
}

.animated-card {
    animation: slideInUp 0.3s ease;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.add-deck-column {
    border: 2px dashed #cbd5e0;
    background: #f7fafc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-deck-column:hover {
    border-color: #667eea;
    background: #edf2f7;
}

.card-menu-btn {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover .card-menu-btn {
    opacity: 1;
}

.empty-deck {
    opacity: 0.6;
}

.priority-badge {
    font-size: 0.75rem;
}

/* Custom Scrollbar */
.deck-body::-webkit-scrollbar {
    width: 6px;
}

.deck-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.deck-body::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.deck-body::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>

<?php
// Helper functions
function getCompletedCardsCount($decks) {
    $count = 0;
    foreach ($decks as $deck) {
        if (strpos($deck['name'], 'تکمیل') !== false || 
            strpos($deck['name'], 'انجام شده') !== false ||
            strpos($deck['name'], 'Done') !== false) {
            $count += $deck['card_count'];
        }
    }
    return $count;
}

function getUrgentCardsCount($decks) {
    // This would need to query cards with high priority
    // For now, return a placeholder
    return 0;
}

function getProgressPercentage($decks) {
    $total_cards = array_sum(array_column($decks, 'card_count'));
    $completed_cards = getCompletedCardsCount($decks);
    
    if ($total_cards == 0) return 0;
    return round(($completed_cards / $total_cards) * 100);
}

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

function jdate($format, $timestamp = '') {
    if ($timestamp == '') {
        $timestamp = time();
    }
    return date($format, $timestamp);
}

include 'includes/footer.php';
?>