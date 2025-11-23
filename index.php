<?php
// index.php
$page_title = "خانه";
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">مدیریت پروژه های بازی سازی</h1>
        <p class="lead mb-4">ProDecks محیط مدیریت پروژه شما را به یک تجربه جذاب و تعاملی تبدیل می‌کند</p>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h5 class="feature-title" style="color: #dc3545 !important; font-weight: 700;">مدیریت کارها</h5>
                            <p class="feature-text" style="color: #dc3545 !important;">کارهای خود را به صورت کارتی مدیریت کنید</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <h5 class="feature-title" style="color: #dc3545 !important; font-weight: 700;">گیمیفیکیشن</h5>
                            <p class="feature-text" style="color: #dc3545 !important;">امتیاز بگیرید و پیشرفت خود را دنبال کنید</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="feature-title" style="color: #dc3545 !important; font-weight: 700;">همکاری تیمی</h5>
                            <p class="feature-text" style="color: #dc3545 !important;">با تیم خود به راحتی همکاری کنید</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h5 class="feature-title" style="color: #dc3545 !important; font-weight: 700;">ساختار پیشرفته</h5>
                            <p class="feature-text" style="color: #dc3545 !important;">Spaces، Decks و Cards حرفه‌ای</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-8 mx-auto">
                <div class="row text-center">
                    <div class="col-md-2">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h6 class="step-title" style="color: #dc3545 !important; font-weight: 600;">Space بسازید</h6>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="step">
                            <div class="step-number">2</div>
                            <h6 class="step-title" style="color: #dc3545 !important; font-weight: 600;">Deck ایجاد کنید</h6>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="step">
                            <div class="step-number">3</div>
                            <h6 class="step-title" style="color: #dc3545 !important; font-weight: 600;">Card اضافه کنید</h6>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="step">
                            <div class="step-number">4</div>
                            <h6 class="step-title" style="color: #dc3545 !important; font-weight: 600;">Subcard ایجاد کنید</h6>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="step">
                            <div class="step-number">5</div>
                            <h6 class="step-title" style="color: #dc3545 !important; font-weight: 600;">مدیریت کنید</h6>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="step">
                            <div class="step-number">6</div>
                            <h6 class="step-title" style="color: #dc3545 !important; font-weight: 600;">پیشرفت کنید</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3 class="stats-title" style="color: #dc3545 !important; font-weight: 700;">پیشرفته</h3>
                <p class="stats-text" style="color: #dc3545 !important;">سیستم مدیریتی مشابه Codecks</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="stats-title" style="color: #dc3545 !important; font-weight: 700;">سریع</h3>
                <p class="stats-text" style="color: #dc3545 !important;">کاربری آسان و رابط روان</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="stats-title" style="color: #dc3545 !important; font-weight: 700;">امن</h3>
                <p class="stats-text" style="color: #dc3545 !important;">حفاظت از داده‌های شما</p>
            </div>
        </div>
    </div>
</div>

<?php 
// فقط در صفحه اصلی فوتر نمایش داده شود
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page == 'index.php') {
    include 'includes/footer.php';
} else {
    echo '</main></body></html>';
}
?>