<?php
// project_settings.php - تنظیمات پروژه (سیستم قدیمی)
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$project_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Verify user is project owner
$stmt = $pdo->prepare("
    SELECT p.*, pm.role 
    FROM projects p 
    JOIN project_members pm ON p.id = pm.project_id 
    WHERE p.id = ? AND pm.user_id = ? AND pm.role = 'owner'
");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header("Location: dashboard.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_project'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $dev_type = $_POST['dev_type'];
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE projects SET name = ?, description = ?, dev_type = ? WHERE id = ?");
            $stmt->execute([$name, $description, $dev_type, $project_id]);
            
            $_SESSION['success_message'] = "تنظیمات پروژه با موفقیت به‌روزرسانی شد";
            header("Location: project_settings.php?id=" . $project_id);
            exit;
        }
    } elseif (isset($_POST['delete_project'])) {
        // Verify once more that user is owner
        $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND owner_id = ?");
        $stmt->execute([$project_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            try {
                $pdo->beginTransaction();
                
                // Delete all cards in project
                $stmt = $pdo->prepare("
                    DELETE c FROM cards c 
                    JOIN decks d ON c.deck_id = d.id 
                    WHERE d.project_id = ?
                ");
                $stmt->execute([$project_id]);
                
                // Delete all decks in project
                $stmt = $pdo->prepare("DELETE FROM decks WHERE project_id = ?");
                $stmt->execute([$project_id]);
                
                // Delete project members
                $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = ?");
                $stmt->execute([$project_id]);
                
                // Delete project
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$project_id]);
                
                $pdo->commit();
                
                $_SESSION['success_message'] = "پروژه با موفقیت حذف شد";
                header("Location: dashboard.php");
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error_message'] = "خطا در حذف پروژه: " . $e->getMessage();
            }
        }
    }
}

$page_title = "تنظیمات پروژه - " . $project['name'];
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">داشبورد</a></li>
                            <li class="breadcrumb-item"><a href="project.php?id=<?php echo $project_id; ?>"><?php echo htmlspecialchars($project['name']); ?></a></li>
                            <li class="breadcrumb-item active">تنظیمات</li>
                        </ol>
                    </nav>
                    <h1>تنظیمات پروژه</h1>
                    <p class="text-muted">مدیریت تنظیمات پروژه "<?php echo htmlspecialchars($project['name']); ?>"</p>
                </div>
                <a href="project.php?id=<?php echo $project_id; ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-2"></i>بازگشت به پروژه
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- General Settings -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">تنظیمات عمومی</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">نام پروژه</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($project['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">توضیحات پروژه</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($project['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="dev_type" class="form-label">نوع توسعه</label>
                                    <select class="form-select" id="dev_type" name="dev_type">
                                        <option value="solo" <?php echo $project['dev_type'] == 'solo' ? 'selected' : ''; ?>>توسعه‌دهنده مستقل</option>
                                        <option value="indie" <?php echo $project['dev_type'] == 'indie' ? 'selected' : ''; ?>>تیم مستقل</option>
                                        <option value="aaa" <?php echo $project['dev_type'] == 'aaa' ? 'selected' : ''; ?>>استودیوی حرفه‌ای</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_project" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>ذخیره تغییرات
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card mt-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0">منطقه خطر</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">هشدار!</h6>
                                <p class="mb-2">این عملیات غیرقابل بازگشت است. با حذف پروژه، تمام اطلاعات مربوطه شامل دک‌ها، کارت‌ها و تاریخچه فعالیت‌ها نیز حذف خواهند شد.</p>
                            </div>
                            <form method="post" onsubmit="return confirm('آیا از حذف این پروژه اطمینان دارید؟ این عمل غیرقابل بازگشت است.')">
                                <div class="mb-3">
                                    <label for="confirm_delete" class="form-label">
                                        برای تأیید، عبارت "حذف پروژه" را در کادر زیر تایپ کنید:
                                    </label>
                                    <input type="text" class="form-control" id="confirm_delete" 
                                           placeholder='حذف پروژه' required>
                                </div>
                                <button type="submit" name="delete_project" class="btn btn-danger" 
                                        id="deleteButton" disabled>
                                    <i class="fas fa-trash me-2"></i>حذف دائمی پروژه
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">دسترسی سریع</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="project_members.php?id=<?php echo $project_id; ?>" class="btn btn-outline-primary text-start">
                                    <i class="fas fa-users me-2"></i>مدیریت اعضا
                                </a>
                                <a href="project.php?id=<?php echo $project_id; ?>" class="btn btn-outline-secondary text-start">
                                    <i class="fas fa-tachometer-alt me-2"></i>مشاهده پروژه
                                </a>
                                <a href="dashboard.php" class="btn btn-outline-info text-start">
                                    <i class="fas fa-home me-2"></i>بازگشت به داشبورد
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Project Statistics -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">آمار پروژه</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $stats = getProjectStats($project_id, $pdo);
                            ?>
                            <div class="text-center">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-primary mb-0"><?php echo $stats['deck_count']; ?></h4>
                                            <small class="text-muted">دک</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-success mb-0"><?php echo $stats['card_count']; ?></h4>
                                            <small class="text-muted">کارت</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="progress" style="height: 8px;">
                                        <?php
                                        $completion_rate = $stats['card_count'] > 0 ? ($stats['completed_cards'] / $stats['card_count']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" style="width: <?php echo $completion_rate; ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?php echo round($completion_rate); ?>% تکمیل شده</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enable delete button only when correct text is entered
document.getElementById('confirm_delete').addEventListener('input', function() {
    const deleteButton = document.getElementById('deleteButton');
    deleteButton.disabled = this.value !== 'حذف پروژه';
});
</script>

<?php include 'includes/footer.php'; ?>