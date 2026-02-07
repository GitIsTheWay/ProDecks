<?php
// tutorial.php - سیستم آموزش تعاملی سازگار با معماری جدید
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// بررسی اینکه کاربر پروژه دارد یا نه
$stmt = $pdo->prepare("
    SELECT p.id, p.name 
    FROM projects p 
    JOIN project_members pm ON p.id = pm.project_id 
    WHERE pm.user_id = ? 
    LIMIT 1
");
$stmt->execute([$user_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

$has_project = $project !== false;
$project_id = $has_project ? $project['id'] : null;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آموزش ProDecks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .tutorial-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .tutorial-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            max-width: 1000px;
        }
        .tutorial-nav {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .tutorial-nav-btn {
            padding: 1rem 2rem;
            border: none;
            background: #f7fafc;
            border-radius: 10px;
            font-weight: 600;
            color: #4a5568;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .tutorial-nav-btn.active {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .tutorial-content {
            min-height: 400px;
        }
        .tutorial-section {
            display: none;
            animation: fadeIn 0.5s ease-in;
        }
        .tutorial-section.active {
            display: block;
        }
        .feature-demo {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
            margin: 1.5rem 0;
            border-left: 4px solid #667eea;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            margin: 2rem 0;
        }
        .video-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        .step-by-step {
            counter-reset: step;
        }
        .step-item {
            position: relative;
            padding: 1.5rem 1.5rem 1.5rem 4rem;
            margin-bottom: 1rem;
            background: #f7fafc;
            border-radius: 10px;
            border-left: 4px solid #48bb78;
        }
        .step-item::before {
            counter-increment: step;
            content: counter(step);
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            background: #48bb78;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .quick-action-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .quick-action-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="tutorial-page">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="tutorial-container">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-gradient mb-3">آموزش ProDecks</h1>
                <p class="lead text-muted">یادگیری استفاده از تمام قابلیت‌های سیستم</p>
            </div>

            <!-- Tutorial Navigation -->
            <div class="tutorial-nav">
                <button class="tutorial-nav-btn active" data-section="getting-started">
                    <i class="fas fa-play-circle me-2"></i>
                    شروع سریع
                </button>
                <button class="tutorial-nav-btn" data-section="projects">
                    <i class="fas fa-folder me-2"></i>
                    مدیریت پروژه‌ها
                </button>
                <button class="tutorial-nav-btn" data-section="spaces-decks">
                    <i class="fas fa-layer-group me-2"></i>
                    Spaces و Decks
                </button>
                <button class="tutorial-nav-btn" data-section="cards">
                    <i class="fas fa-tasks me-2"></i>
                    کارت‌ها و زیرکارت‌ها
                </button>
                <button class="tutorial-nav-btn" data-section="tips">
                    <i class="fas fa-lightbulb me-2"></i>
                    نکات کاربردی
                </button>
            </div>

            <!-- Tutorial Content -->
            <div class="tutorial-content">
                <!-- Getting Started Section -->
                <div class="tutorial-section active" id="getting-started">
                    <h3 class="mb-4">شروع سریع با ProDecks</h3>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="feature-demo">
                                <h5><i class="fas fa-rocket text-primary me-2"></i>ساختار سیستم</h5>
                                <p class="mb-0">
                                    ProDecks از ۴ لایه تشکیل شده است:
                                    <strong>Projects → Spaces → Decks → Cards</strong>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-demo">
                                <h5><i class="fas fa-gamepad text-success me-2"></i>سیستم تجربه</h5>
                                <p class="mb-0">
                                    با انجام فعالیت‌ها امتیاز کسب کنید و سطح خود را افزایش دهید
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (!$has_project): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            برای شروع، نیاز به ایجاد یک پروژه دارید.
                        </div>
                    <?php endif; ?>

                    <div class="step-by-step">
                        <div class="step-item">
                            <h6>ایجاد پروژه</h6>
                            <p class="mb-0">از دکمه "پروژه جدید" در داشبورد استفاده کنید</p>
                        </div>
                        <div class="step-item">
                            <h6>افزودن Space</h6>
                            <p class="mb-0">در صفحه پروژه، Spaceهای مختلف ایجاد کنید</p>
                        </div>
                        <div class="step-item">
                            <h6>ساخت Decks</h6>
                            <p class="mb-0">در هر Space، Deckهای مورد نیاز را اضافه کنید</p>
                        </div>
                        <div class="step-item">
                            <h6>مدیریت کارها</h6>
                            <p class="mb-0">کارت‌ها را ایجاد و بین Deckها جابجا کنید</p>
                        </div>
                    </div>
                </div>

                <!-- Projects Section -->
                <div class="tutorial-section" id="projects">
                    <h3 class="mb-4">مدیریت پروژه‌ها</h3>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="feature-demo">
                                <h5><i class="fas fa-users text-info me-2"></i>پروژه‌های تیمی</h5>
                                <p>
                                    هر پروژه می‌تواند چندین عضو داشته باشد. مالک پروژه می‌تواند اعضا را مدیریت کند.
                                </p>
                                
                                <h6 class="mt-3">ویژگی‌های پروژه‌ها:</h6>
                                <ul>
                                    <li><strong>رنگ‌بندی:</strong> هر پروژه رنگ مخصوص خود را دارد</li>
                                    <li><strong>توضیحات:</strong> اطلاعات کامل درباره پروژه</li>
                                    <li><strong>آمار:</strong> پیگیری پیشرفت کلی پروژه</li>
                                    <li><strong>اعضا:</strong> مدیریت دسترسی اعضای تیم</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="quick-action-card" onclick="window.location.href='dashboard.php'">
                                <div class="action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h6>ایجاد پروژه جدید</h6>
                                <p class="text-muted small">شروع یک پروژه جدید</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Spaces & Decks Section -->
                <div class="tutorial-section" id="spaces-decks">
                    <h3 class="mb-4">سازمان‌دهی با Spaces و Decks</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-demo">
                                <h5><i class="fas fa-th-large text-warning me-2"></i>Spaces چیست؟</h5>
                                <p>
                                    Spaces بخش‌های مختلف یک پروژه هستند. برای مثال:
                                </p>
                                <ul>
                                    <li>توسعه نرم‌افزار</li>
                                    <li>طراحی رابط کاربری</li>
                                    <li>تست و کیفیت</li>
                                    <li>مستندات</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-demo">
                                <h5><i class="fas fa-columns text-success me-2"></i>Decks چیست؟</h5>
                                <p>
                                    Decks ستون‌های سازمان‌دهی کارها در هر Space هستند:
                                </p>
                                <ul>
                                    <li>انجام شود (To Do)</li>
                                    <li>در حال انجام (In Progress)</li>
                                    <li>بررسی (Review)</li>
                                    <li>انجام شده (Done)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <?php if ($has_project): ?>
                        <div class="text-center mt-4">
                            <a href="projects/index.php?id=<?php echo $project_id; ?>" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-2"></i>
                                مشاهده پروژه "<?php echo htmlspecialchars($project['name']); ?>"
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cards Section -->
                <div class="tutorial-section" id="cards">
                    <h3 class="mb-4">کارت‌ها و زیرکارت‌ها</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-demo">
                                <h5><i class="fas fa-sticky-note text-primary me-2"></i>کارت‌ها (Cards)</h5>
                                <p>هر کارت代表 یک وظیفه یا آیتم کاری است:</p>
                                <ul>
                                    <li><strong>عنوان و توضیحات</strong></li>
                                    <li><strong>اولویت (کم، متوسط، بالا)</strong></li>
                                    <li><strong>وضعیت (انجام شود/انجام شده)</strong></li>
                                    <li><strong>واگذار شده به</strong></li>
                                    <li><strong>تاریخ سررسید</strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-demo">
                                <h5><i class="fas fa-list-ul text-info me-2"></i>زیرکارت‌ها (Subcards)</h5>
                                <p>برای تجزیه کارت‌های پیچیده:</p>
                                <ul>
                                    <li>مراحل جزئی یک کار</li>
                                    <li>چک‌لیست‌های کاری</li>
                                    <li>وظایف کوچک‌تر</li>
                                    <li>مدیریت وضعیت مستقل</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="feature-demo mt-4">
                        <h5><i class="fas fa-sort text-success me-2"></i>درگ و دراپ</h5>
                        <p class="mb-3">
                            می‌توانید کارت‌ها را بین Deckهای مختلف جابجا کنید تا وضعیت آن‌ها را به روز کنید.
                        </p>
                        <div class="alert alert-warning">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>نکته:</strong> کارت‌ها به طور خودکار بر اساس اولویت مرتب می‌شوند.
                        </div>
                    </div>
                </div>

                <!-- Tips Section -->
                <div class="tutorial-section" id="tips">
                    <h3 class="mb-4">نکات کاربردی و بهترین روش‌ها</h3>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        بهترین روش‌ها
                                    </h5>
                                    <ul class="mt-3">
                                        <li class="mb-2">کارت‌ها را کوچک و قابل مدیریت نگه دارید</li>
                                        <li class="mb-2">از اولویت‌بندی برای تمرکز روی کارهای مهم استفاده کنید</li>
                                        <li class="mb-2">تاریخ‌های سررسید واقع‌بینانه تعیین کنید</li>
                                        <li class="mb-2">کارها را به اعضای مناسب تیم واگذار کنید</li>
                                        <li>وضعیت کارت‌ها را مرتب به روز کنید</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-info">
                                        <i class="fas fa-bolt me-2"></i>
                                        میانبرها
                                    </h5>
                                    <ul class="mt-3">
                                        <li class="mb-2">از سیستم اولویت برای فیلتر کردن کارها استفاده کنید</li>
                                        <li class="mb-2">کارت‌های انجام شده را بایگانی کنید</li>
                                        <li class="mb-2">از زیرکارت‌ها برای کارهای پیچیده استفاده کنید</li>
                                        <li class="mb-2">رنگ‌ها را برای تفکیک بصری استفاده کنید</li>
                                        <li>آمار پروژه را مرتب بررسی کنید</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="feature-demo">
                        <h5><i class="fas fa-graduation-cap text-primary me-2"></i>سیستم سطح و تجربه</h5>
                        <p>
                            با انجام فعالیت‌های زیر امتیاز تجربه کسب کنید:
                        </p>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <i class="fas fa-plus-circle fa-2x text-success mb-2"></i>
                                <h6>ایجاد پروژه</h6>
                                <p class="text-muted small">+10 امتیاز</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-tasks fa-2x text-info mb-2"></i>
                                <h6>اتمام کارت</h6>
                                <p class="text-muted small">+5 امتیاز</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-list-ul fa-2x text-warning mb-2"></i>
                                <h6>اتمام زیرکارت</h6>
                                <p class="text-muted small">+2 امتیاز</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-columns fa-2x text-primary mb-2"></i>
                                <h6>ایجاد Deck</h6>
                                <p class="text-muted small">+3 امتیاز</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="quick-action-card" onclick="window.location.href='dashboard.php'">
                        <div class="action-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h6>داشبورد</h6>
                        <p class="text-muted small">بازگشت به صفحه اصلی</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quick-action-card" onclick="window.location.href='onboarding.php?force=1'">
                        <div class="action-icon">
                            <i class="fas fa-redo"></i>
                        </div>
                        <h6>راهنمای شروع</h6>
                        <p class="text-muted small">مشاهده دوباره راهنمای اولیه</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quick-action-card" onclick="window.location.href='<?php echo $has_project ? "projects/index.php?id={$project_id}" : "dashboard.php"; ?>'">
                        <div class="action-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <h6>شروع کار</h6>
                        <p class="text-muted small">شروع استفاده از سیستم</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tutorial navigation
        document.querySelectorAll('.tutorial-nav-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.tutorial-nav-btn').forEach(b => {
                    b.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.tutorial-section').forEach(section => {
                    section.classList.remove('active');
                });
                
                // Show target section
                const targetSection = this.getAttribute('data-section');
                document.getElementById(targetSection).classList.add('active');
            });
        });

        // Add some interactive demos
        document.addEventListener('DOMContentLoaded', function() {
            // Animate step items
            const stepItems = document.querySelectorAll('.step-item');
            stepItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>