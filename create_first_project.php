<?php
// create_first_project.php
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dev_type = $_POST['dev_type'] ?? '';
    $project_name = trim($_POST['project_name'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (!empty($dev_type) && !empty($project_name)) {
        // Create project
        $description = "پروژه اولیه برای " . getDevTypeText($dev_type);
        
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, owner_id, dev_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$project_name, $description, $user_id, $dev_type]);
        $project_id = $pdo->lastInsertId();
        
        // Add owner as project member
        $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'owner')");
        $stmt->execute([$project_id, $user_id]);
        
        // Create default decks based on dev type
        $default_decks = getDefaultDecks($dev_type);
        $position = 1;
        
        foreach ($default_decks as $deck_name) {
            $stmt = $pdo->prepare("INSERT INTO decks (project_id, name, position) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $deck_name, $position]);
            $position++;
        }
        
        // Add experience for creating project
        addExperience($user_id, 10, $pdo);
        
        // Store dev type in session
        $_SESSION['dev_type'] = $dev_type;
        
        header("Location: project_decks.php?id=" . $project_id . "&new_user=1");
        exit;
    }
}

$dev_type = $_POST['dev_type'] ?? '';
if (empty($dev_type)) {
    header("Location: onboarding.php");
    exit;
}

$page_title = "ایجاد پروژه اول";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="card-title mb-0">پروژه اول خود را ایجاد کنید</h3>
                <p class="text-muted mt-2">نام پروژه بازی خود را وارد کنید</p>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <span class="badge bg-primary fs-6">
                        <?php echo getDevTypeText($dev_type); ?>
                    </span>
                </div>
                
                <form method="post">
                    <input type="hidden" name="dev_type" value="<?php echo htmlspecialchars($dev_type); ?>">
                    
                    <div class="mb-4">
                        <label for="project_name" class="form-label">نام پروژه</label>
                        <input type="text" class="form-control form-control-lg" id="project_name" name="project_name" 
                               placeholder="مثلا: بازی ماجراجویی من" required autofocus>
                        <div class="form-text">می‌توانید بعداً این نام را تغییر دهید</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            ایجاد پروژه و شروع کار <i class="fas fa-rocket ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
function getDevTypeText($dev_type) {
    switch ($dev_type) {
        case 'solo': return 'توسعه‌دهنده مستقل';
        case 'indie': return 'تیم مستقل';
        case 'aaa': return 'استودیوی حرفه‌ای';
        default: return 'توسعه‌دهنده';
    }
}

function getDefaultDecks($dev_type) {
    switch ($dev_type) {
        case 'solo':
            return ['ایده‌ها', 'در حال توسعه', 'تست', 'تکمیل شده'];
        case 'indie':
            return ['backlog', 'طراحی', 'توسعه', 'تست', 'آماده انتشار'];
        case 'aaa':
            return ['Concepts', 'Pre-production', 'Production', 'QA', 'Polishing', 'Gold Master'];
        default:
            return ['انجام نشده', 'در حال انجام', 'انجام شده'];
    }
}

include 'includes/footer.php';
?>