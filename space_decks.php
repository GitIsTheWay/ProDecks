<?php
// space_decks.php - سیستم Decks پیشرفته
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$space_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify user has access to space
$stmt = $pdo->prepare("
    SELECT s.* 
    FROM spaces s 
    LEFT JOIN space_members sm ON s.id = sm.space_id 
    WHERE s.id = ? AND (s.user_id = ? OR sm.user_id = ?)
");
$stmt->execute([$space_id, $user_id, $user_id]);
$space = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$space) {
    header("Location: spaces_manager.php");
    exit;
}

// Get decks with cards and subcards
$stmt = $pdo->prepare("
    SELECT d.*, 
           COUNT(DISTINCT c.id) as card_count
    FROM decks d 
    LEFT JOIN cards c ON d.id = c.deck_id 
    WHERE d.space_id = ? 
    GROUP BY d.id 
    ORDER BY d.position
");
$stmt->execute([$space_id]);
$decks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cards for each deck with subcards
foreach ($decks as &$deck) {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               u.username as assignee_name,
               u.full_name as assignee_fullname,
               COUNT(sc.id) as subcard_count
        FROM cards c 
        LEFT JOIN users u ON c.assignee_id = u.id 
        LEFT JOIN cards sc ON c.id = sc.parent_card_id
        WHERE c.deck_id = ? AND c.parent_card_id IS NULL
        GROUP BY c.id
        ORDER BY c.position
    ");
    $stmt->execute([$deck['id']]);
    $deck['cards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get subcards for each card
    foreach ($deck['cards'] as &$card) {
        $stmt = $pdo->prepare("
            SELECT sc.*, u.username as assignee_name, u.full_name as assignee_fullname
            FROM cards sc 
            LEFT JOIN users u ON sc.assignee_id = u.id 
            WHERE sc.parent_card_id = ? 
            ORDER BY sc.position
        ");
        $stmt->execute([$card['id']]);
        $card['subcards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$page_title = $space['name'] . " - Decks";
include 'includes/header.php';
?>

<div class="spaces-container">
    <div class="container-fluid">
        <!-- Space Header -->
        <div class="space-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="spaces_manager.php" class="text-light">Spaces</a></li>
                            <li class="breadcrumb-item active text-light"><?php echo htmlspecialchars($space['name']); ?></li>
                        </ol>
                    </nav>
                    <h1 class="h3 text-white mb-1"><?php echo htmlspecialchars($space['name']); ?></h1>
                    <p class="text-light opacity-75 mb-0"><?php echo htmlspecialchars($space['description']); ?></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#createDeckModal">
                        <i class="fas fa-plus me-2"></i>Deck جدید
                    </button>
                    <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#spaceSettingsModal">
                        <i class="fas fa-cog me-2"></i>تنظیمات
                    </button>
                </div>
            </div>
        </div>

        <!-- Decks Board -->
        <div class="decks-board">
            <div class="deck-grid" id="decks-container">
                <?php foreach ($decks as $deck): ?>
                    <div class="deck-card-advanced" data-deck-id="<?php echo $deck['id']; ?>">
                        <div class="deck-header-advanced">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-light"><?php echo htmlspecialchars($deck['name']); ?></h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary"><?php echo $deck['card_count']; ?></span>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-light border-0 p-0" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="openEditDeckModal(<?php echo $deck['id']; ?>)">
                                                <i class="fas fa-edit me-2"></i>ویرایش Deck
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteDeck(<?php echo $deck['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>حذف Deck
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="deck-body-advanced custom-scrollbar" data-deck-id="<?php echo $deck['id']; ?>">
                            <?php foreach ($deck['cards'] as $card): ?>
                                <div class="card-item animated-card" data-card-id="<?php echo $card['id']; ?>" draggable="true">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="text-light mb-0 flex-grow-1 me-2"><?php echo htmlspecialchars($card['title']); ?></h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-light border-0 p-0" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="openEditCardModal(<?php echo $card['id']; ?>)">
                                                    <i class="fas fa-edit me-2"></i>ویرایش
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="openAddSubcardModal(<?php echo $card['id']; ?>)">
                                                    <i class="fas fa-plus me-2"></i>افزودن Subcard
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCard(<?php echo $card['id']; ?>)">
                                                    <i class="fas fa-trash me-2"></i>حذف
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($card['description'])): ?>
                                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($card['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <!-- Card Meta -->
                                    <div class="card-meta d-flex justify-content-between align-items-center">
                                        <?php if ($card['assignee_name']): ?>
                                            <span class="badge bg-dark">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($card['assignee_fullname'] ?: $card['assignee_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($card['subcard_count'] > 0): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-layer-group me-1"></i>
                                                <?php echo $card['subcard_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Subcards -->
                                    <?php if (!empty($card['subcards'])): ?>
                                        <div class="subcards-container mt-2">
                                            <?php foreach ($card['subcards'] as $subcard): ?>
                                                <div class="subcard-item" data-subcard-id="<?php echo $subcard['id']; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-light"><?php echo htmlspecialchars($subcard['title']); ?></span>
                                                        <div class="subcard-actions">
                                                            <button class="btn btn-sm btn-outline-light border-0 p-0" onclick="editSubcard(<?php echo $subcard['id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger border-0 p-0" onclick="deleteSubcard(<?php echo $subcard['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($deck['cards'])): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">کارتی وجود ندارد</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="deck-header-advanced">
                            <button class="btn btn-outline-light w-100" onclick="openAddCardModal(<?php echo $deck['id']; ?>)">
                                <i class="fas fa-plus me-2"></i>افزودن Card
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Add New Deck Column -->
                <div class="deck-card-advanced add-deck-column">
                    <div class="deck-body-advanced text-center p-4">
                        <i class="fas fa-plus-circle fa-2x text-muted mb-3"></i>
                        <h6 class="text-muted">افزودن Deck جدید</h6>
                        <button class="btn btn-outline-light btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#createDeckModal">
                            ایجاد Deck
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'includes/space_deck_modals.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="js/space_decks.js"></script>

<?php include 'includes/footer.php'; ?>