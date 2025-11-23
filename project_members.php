<?php
// project_members.php - مدیریت اعضای پروژه (سیستم قدیمی)
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

// Get project members
$members = getProjectMembers($project_id, $pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['invite_email'])) {
        $email = trim($_POST['email']);
        
        if (!empty($email)) {
            // Find user by email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $target_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($target_user) {
                // Check if user is already a member
                $stmt = $pdo->prepare("SELECT id FROM project_members WHERE project_id = ? AND user_id = ?");
                $stmt->execute([$project_id, $target_user['id']]);
                
                if ($stmt->rowCount() == 0) {
                    // Add user to project
                    $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (?, ?, 'member')");
                    $stmt->execute([$project_id, $target_user['id']]);
                    
                    $_SESSION['success_message'] = "کاربر با موفقیت به پروژه اضافه شد";
                } else {
                    $_SESSION['error_message'] = "این کاربر قبلاً عضو پروژه است";
                }
            } else {
                $_SESSION['error_message'] = "کاربری با این ایمیل یافت نشد";
            }
        }
    } elseif (isset($_POST['remove_member'])) {
        $member_id = $_POST['member_id'];
        
        // Don't allow removing owner
        if ($member_id != $user_id) {
            $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = ? AND user_id = ?");
            $stmt->execute([$project_id, $member_id]);
            
            $_SESSION['success_message'] = "عضو با موفقیت حذف شد";
        } else {
            $_SESSION['error_message'] = "نمی‌توانید مالک پروژه را حذف کنید";
        }
    }
    
    header("Location: project_members.php?id=" . $project_id);
    exit;
}

$page_title = "مدیریت اعضای پروژه - " . $project['name'];
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
                            <li class="breadcrumb-item active">مدیریت اعضا</li>
                        </ol>
                    </nav>
                    <h1>مدیریت اعضای پروژه</h1>
                    <p class="text-muted">اعضای پروژه "<?php echo htmlspecialchars($project['name']); ?>"</p>
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
                <!-- Current Members -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">اعضای فعلی پروژه</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($members)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">هیچ عضوی در این پروژه وجود ندارد</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($members as $member): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($member['full_name'] ?: $member['username']); ?></h6>
                                                <small class="text-muted">@<?php echo $member['username']; ?></small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-<?php echo $member['role'] == 'owner' ? 'primary' : 'secondary'; ?> me-2">
                                                    <?php echo $member['role'] == 'owner' ? 'مالک' : 'عضو'; ?>
                                                </span>
                                                <?php if ($member['id'] != $user_id): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        <button type="submit" name="remove_member" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('آیا از حذف این عضو اطمینان دارید؟')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Invite Member -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">دعوت عضو جدید</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label">ایمیل کاربر</label>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           placeholder="email@example.com">
                                    <div class="form-text">ایمیلی که کاربر با آن ثبت نام کرده است</div>
                                </div>
                                <button type="submit" name="invite_email" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>ارسال دعوت
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Project Info -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">اطلاعات پروژه</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>نام پروژه:</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($project['name']); ?></p>
                            </div>
                            <div class="mb-3">
                                <strong>توضیحات:</strong>
                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($project['description']); ?></p>
                            </div>
                            <div>
                                <strong>تعداد اعضا:</strong>
                                <p class="mb-0"><?php echo count($members); ?> نفر</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>