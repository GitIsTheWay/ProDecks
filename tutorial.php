<?php
// tutorial.php
include 'includes/config.php';
include 'includes/functions.php';

$page_title = "آموزش استفاده از ProDecks";
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">فهرست آموزش</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#section1" class="list-group-item list-group-item-action">معرفی ProDecks</a>
                    <a href="#section2" class="list-group-item list-group-item-action">ایجاد پروژه</a>
                    <a href="#section3" class="list-group-item list-group-item-action">مدیریت دک‌ها</a>
                    <a href="#section4" class="list-group-item list-group-item-action">کار با کارت‌ها</a>
                    <a href="#section5" class="list-group-item list-group-item-action">سیستم امتیاز</a>
                    <a href="#section6" class="list-group-item list-group-item-action">نکات حرفه‌ای</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">آموزش کامل استفاده از ProDecks</h4>
            </div>
            <div class="card-body">
                <div id="section1" class="tutorial-section mb-5">
                    <h3>۱. معرفی ProDecks</h3>
                    <p>ProDecks یک پلتفرم مدیریت پروژه به سبک بازی‌وار شده است که به شما کمک می‌کند کارهایتان را به روشی جذاب و تعاملی مدیریت کنید.</p>
                    
                    <div class="alert alert-info">
                        <strong>نکته:</strong> با انجام هر فعالیت در سایت، امتیاز کسب می‌کنید و سطح شما افزایش می‌یابد!
                    </div>
                </div>

                <div id="section2" class="tutorial-section mb-5">
                    <h3>۲. ایجاد پروژه جدید</h3>
                    <p>برای شروع کار:</p>
                    <ol>
                        <li>وارد داشبورد خود شوید</li>
                        <li>روی دکمه "پروژه جدید" کلیک کنید</li>
                        <li>نام و توضیحات پروژه را وارد کنید</li>
                        <li>روی "ایجاد پروژه" کلیک کنید</li>
                    </ol>
                    
                    <div class="tutorial-demo bg-light p-3 rounded mb-3">
                        <i class="fas fa-plus text-success me-2"></i>
                        <strong>پاداش:</strong> با ایجاد اولین پروژه، ۱۰ امتیاز کسب می‌کنید!
                    </div>
                </div>

                <div id="section3" class="tutorial-section mb-5">
                    <h3>۳. مدیریت دک‌ها</h3>
                    <p>دک‌ها مانند ستون‌های کار در برد پروژه شما هستند:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>افزودن دک جدید</h6>
                                    <p>روی دکمه "افزودن دک" در صفحه پروژه کلیک کنید و نام دک را وارد کنید.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>ترتیب دک‌ها</h6>
                                    <p>با کشیدن و رها کردن دک‌ها، ترتیب آن‌ها را تغییر دهید.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="section4" class="tutorial-section mb-5">
                    <h3>۴. کار با کارت‌ها</h3>
                    <p>کارت‌ها نمایانگر وظایف و کارهای شما هستند:</p>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                <h6>افزودن کارت</h6>
                                <small>روی "افزودن کارت" در هر دک کلیک کنید</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-arrows-alt fa-2x text-warning mb-2"></i>
                                <h6>جابجایی کارت</h6>
                                <small>کارت‌ها را بین دک‌ها بکشید و رها کنید</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                                <h6>واگذاری کارت</h6>
                                <small>کارت‌ها را به اعضای تیم واگذار کنید</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong>تغییر وضعیت کارت:</strong> با کشیدن کارت از یک دک به دک دیگر، وضعیت آن را تغییر دهید (مثلاً از "در حال انجام" به "انجام شده")
                    </div>
                </div>

                <div id="section5" class="tutorial-section mb-5">
                    <h3>۵. سیستم امتیاز و سطح</h3>
                    <p>با فعالیت‌های زیر امتیاز کسب کنید:</p>
                    
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>فعالیت</th>
                                <th>امتیاز</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ایجاد پروژه</td>
                                <td>۱۰ امتیاز</td>
                            </tr>
                            <tr>
                                <td>افزودن دک</td>
                                <td>۵ امتیاز</td>
                            </tr>
                            <tr>
                                <td>افزودن کارت</td>
                                <td>۳ امتیاز</td>
                            </tr>
                            <tr>
                                <td>جابجایی کارت</td>
                                <td>۲ امتیاز</td>
                            </tr>
                            <tr>
                                <td>تکمیل کارت (انتقال به دک انجام شده)</td>
                                <td>۵ امتیاز</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: 65%">
                            <strong>۶۵ امتیاز از ۱۰۰</strong>
                        </div>
                    </div>
                    <p class="text-center">هر ۱۰۰ امتیاز = ۱ سطح بالاتر!</p>
                </div>

                <div id="section6" class="tutorial-section">
                    <h3>۶. نکات حرفه‌ای</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="card-title">💡 اولویت‌بندی کارت‌ها</h6>
                                    <p class="card-text">از اولویت‌های "کم"، "متوسط" و "زیاد" برای مشخص کردن اهمیت کارت‌ها استفاده کنید.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="card-title">📅 تعیین تاریخ سررسید</h6>
                                    <p class="card-text">برای کارت‌های مهم تاریخ سررسید تعیین کنید تا به موقع انجام شوند.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h6 class="card-title">👥 همکاری تیمی</h6>
                                    <p class="card-text">کارت‌ها را به اعضای تیم واگذار کنید و پیشرفت کارها را دنبال کنید.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title">🎯 هدف‌گذاری</h6>
                                    <p class="card-text">هر روز حداقل ۳ کارت را به دک "انجام شده" منتقل کنید!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <div class="alert alert-success">
                        <h5>آماده شروع هستید؟</h5>
                        <p class="mb-3">اولین پروژه خود را ایجاد کنید و تجربه مدیریت کارها به سبک بازی‌وار شده را آغاز کنید!</p>
                        <a href="dashboard.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>شروع ماجراجویی!
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tutorial-section {
    scroll-margin-top: 80px;
}

.tutorial-section h3 {
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
    margin-bottom: 20px;
    color: #667eea;
}

.list-group-item.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: #667eea;
}
</style>

<script>
// Smooth scroll for tutorial navigation
document.querySelectorAll('.list-group-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        window.scrollTo({
            top: targetElement.offsetTop - 70,
            behavior: 'smooth'
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>