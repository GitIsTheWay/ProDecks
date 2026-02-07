<?php
// onboarding.php - سیستم راهنمای اولیه سازگار با معماری جدید
require_once 'includes/config.php';
require_once 'includes/auth.php';

// اگر کاربر لاگین نکرده، به صفحه ورود هدایت شود
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// بررسی اینکه کاربر قبلاً onboarding را دیده یا نه
// یا اگر پروژه دارد، مستقیماً به داشبورد هدایت شود
$stmt = $pdo->prepare("
    SELECT COUNT(*) as project_count 
    FROM project_members 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// اگر کاربر حداقل یک پروژه دارد، onboarding را نشان نده
if ($result['project_count'] > 0 && !isset($_GET['force'])) {
    header('Location: dashboard.php');
    exit;
}

// اگر از طریق ثبت‌نام آمده، یک پروژه پیش‌فرض ایجاد کن
$auto_create = isset($_GET['new_user']) && $_GET['new_user'] == '1';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>راهنمای شروع - ProDecks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .onboarding-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .onboarding-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            max-width: 900px;
        }
        .onboarding-step {
            display: none;
            animation: fadeIn 0.5s ease-in;
        }
        .onboarding-step.active {
            display: block;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            gap: 1rem;
        }
        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e2e8f0;
            transition: all 0.3s ease;
        }
        .step-dot.active {
            background: #667eea;
            transform: scale(1.2);
        }
        .step-content {
            text-align: center;
            padding: 2rem;
        }
        .step-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            color: #667eea;
        }
        .step-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2d3748;
        }
        .step-description {
            font-size: 1.1rem;
            color: #718096;
            margin-bottom: 2rem;
            line-height: 1.8;
        }
        .onboarding-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            gap: 1rem;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .feature-card {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 12px;
            background: #f7fafc;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .feature-icon {
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
<body class="onboarding-page">
    <div class="container">
        <div class="onboarding-container">
            <!-- Step Indicators -->
            <div class="step-indicator">
                <div class="step-dot active" data-step="1"></div>
                <div class="step-dot" data-step="2"></div>
                <div class="step-dot" data-step="3"></div>
                <div class="step-dot" data-step="4"></div>
            </div>

            <!-- Step 1: Welcome -->
            <div class="onboarding-step active" id="step-1">
                <div class="step-content">
                    <div class="step-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h1 class="step-title">به ProDecks خوش آمدید! 🎉</h1>
                    <p class="step-description">
                        ProDecks یک سیستم مدیریت پروژه پیشرفته است که به شما کمک می‌کند<br>
                        کارهای تیمی و شخصی خود را به صورت سازمان‌یافته مدیریت کنید.
                    </p>
                    
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <h5>ساختار ۴ لایه</h5>
                            <p class="text-muted">Projects → Spaces → Decks → Cards</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h5>مدیریت کارها</h5>
                            <p class="text-muted">سیستم اولویت‌بندی و وضعیت پیشرفته</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <h5>گیمیفیکیشن</h5>
                            <p class="text-muted">سیستم سطح و تجربه کاربری</p>
                        </div>
                    </div>

                    <div class="onboarding-actions">
                        <div></div> <!-- Empty div for spacing -->
                        <button class="btn btn-primary btn-lg" onclick="nextStep()">
                            شروع راهنما
                            <i class="fas fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Projects -->
            <div class="onboarding-step" id="step-2">
                <div class="step-content">
                    <div class="step-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <h2 class="step-title">پروژه‌ها (Projects)</h2>
                    <p class="step-description">
                        پروژه‌ها بالاترین سطح سازمان‌دهی هستند.<br>
                        هر پروژه می‌تواند برای یک تیم، محصول یا هدف خاص ایجاد شود.
                    </p>

                    <div class="row text-start mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-check text-success me-2 mt-1"></i>
                                <div>
                                    <strong>مدیریت تیم</strong>
                                    <p class="text-muted mb-0">اعضای تیم را اضافه و مدیریت کنید</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-check text-success me-2 mt-1"></i>
                                <div>
                                    <strong>رنگ‌بندی</strong>
                                    <p class="text-muted mb-0">هر پروژه رنگ مخصوص خود را دارد</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-check text-success me-2 mt-1"></i>
                                <div>
                                    <strong>فضاهای متعدد</strong>
                                    <p class="text-muted mb-0">هر پروژه می‌تواند چندین Space داشته باشد</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-check text-success me-2 mt-1"></i>
                                <div>
                                    <strong>آمار پیشرفت</strong>
                                    <p class="text-muted mb-0">پیگیری پیشرفت کلی پروژه</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="onboarding-actions">
                        <button class="btn btn-outline-secondary btn-lg" onclick="prevStep()">
                            <i class="fas fa-arrow-right me-2"></i>
                            قبلی
                        </button>
                        <button class="btn btn-primary btn-lg" onclick="nextStep()">
                            بعدی
                            <i class="fas fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Spaces & Decks -->
            <div class="onboarding-step" id="step-3">
                <div class="step-content">
                    <div class="step-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h2 class="step-title">Spaces و Decks</h2>
                    <p class="step-description">
                        Spaces بخش‌های مختلف پروژه و Decks ستون‌های سازمان‌دهی کارها هستند.
                    </p>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-th-large text-primary fa-3x mb-3"></i>
                                    <h5>Spaces</h5>
                                    <p class="text-muted">
                                        برای بخش‌های مختلف مانند: 
                                        توسعه، طراحی، مارکتینگ
                                    </p>
                                    <ul class="list-unstyled text-start">
                                        <li><i class="fas fa-check text-success me-2"></i>مدیریت مستقل</li>
                                        <li><i class="fas fa-check text-success me-2"></i>رنگ‌بندی جداگانه</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Decks متعدد</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-columns text-info fa-3x mb-3"></i>
                                    <h5>Decks</h5>
                                    <p class="text-muted">
                                        ستون‌های کاری مانند: 
                                        انجام شود، در حال انجام، انجام شده
                                    </p>
                                    <ul class="list-unstyled text-start">
                                        <li><i class="fas fa-check text-success me-2"></i>سازمان‌دهی کارها</li>
                                        <li><i class="fas fa-check text-success me-2"></i>درگ و دراپ</li>
                                        <li><i class="fas fa-check text-success me-2"></i>ردیابی پیشرفت</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="onboarding-actions">
                        <button class="btn btn-outline-secondary btn-lg" onclick="prevStep()">
                            <i class="fas fa-arrow-right me-2"></i>
                            قبلی
                        </button>
                        <button class="btn btn-primary btn-lg" onclick="nextStep()">
                            بعدی
                            <i class="fas fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Cards & Getting Started -->
            <div class="onboarding-step" id="step-4">
                <div class="step-content">
                    <div class="step-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <h2 class="step-title">آماده شروع هستید! 🚀</h2>
                    <p class="step-description">
                        حالا می‌توانید اولین پروژه خود را ایجاد کنید و کارتان را شروع کنید.
                    </p>

                    <div class="row text-start mt-4">
                        <div class="col-md-8 mx-auto">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">راهنمای شروع سریع:</h5>
                                    <ol class="mt-3">
                                        <li class="mb-2">پروژه جدید ایجاد کنید</li>
                                        <li class="mb-2">Spaceهای مختلف برای بخش‌های کار اضافه کنید</li>
                                        <li class="mb-2">در هر Space، Deckهای مورد نیاز بسازید</li>
                                        <li class="mb-2">کارت‌های کاری خود را ایجاد و مدیریت کنید</li>
                                        <li>از سیستم اولویت‌بندی و وضعیت استفاده کنید</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="onboarding-actions">
                        <button class="btn btn-outline-secondary btn-lg" onclick="prevStep()">
                            <i class="fas fa-arrow-right me-2"></i>
                            قبلی
                        </button>
                        <?php if ($auto_create): ?>
                            <button class="btn btn-success btn-lg" onclick="createSampleProject()">
                                <i class="fas fa-magic me-2"></i>
                                ایجاد پروژه نمونه
                            </button>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-primary btn-lg">
                                رفتن به داشبورد
                                <i class="fas fa-arrow-left ms-2"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.onboarding-step').forEach(el => {
                el.classList.remove('active');
            });
            
            // Show current step
            document.getElementById(`step-${step}`).classList.add('active');
            
            // Update step indicators
            document.querySelectorAll('.step-dot').forEach((dot, index) => {
                if (index + 1 <= step) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        async function createSampleProject() {
            try {
                const response = await fetch('projects/create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'name=پروژه%20نمونه&description=این%20یک%20پروژه%20نمونه%20است&color=%23667eea'
                });
                
                if (response.ok) {
                    window.location.href = 'dashboard.php?onboarding=completed';
                } else {
                    alert('خطا در ایجاد پروژه نمونه');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('خطا در ارتباط با سرور');
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                nextStep();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                prevStep();
            }
        });
    </script>
</body>
</html>