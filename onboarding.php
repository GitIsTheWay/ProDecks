<?php
// onboarding.php
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$page_title = "شروع کار";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header text-center">
                <h3 class="card-title mb-0">به ProDecks خوش آمدید! 🎮</h3>
                <p class="text-muted mt-2">لطفا نوع فعالیت خود را انتخاب کنید</p>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 dev-type-card" data-type="solo">
                            <div class="card-body">
                                <div class="dev-icon mb-3">
                                    <i class="fas fa-user fa-3x text-primary"></i>
                                </div>
                                <h5>توسعه‌دهنده مستقل</h5>
                                <p class="text-muted">فردی که به تنهایی روی پروژه کار می‌کند</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 dev-type-card" data-type="indie">
                            <div class="card-body">
                                <div class="dev-icon mb-3">
                                    <i class="fas fa-users fa-3x text-success"></i>
                                </div>
                                <h5>تیم مستقل</h5>
                                <p class="text-muted">تیم کوچک با بودجه محدود</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 dev-type-card" data-type="aaa">
                            <div class="card-body">
                                <div class="dev-icon mb-3">
                                    <i class="fas fa-building fa-3x text-warning"></i>
                                </div>
                                <h5>استودیوی حرفه‌ای</h5>
                                <p class="text-muted">استودیوی بزرگ با تیم‌های متعدد</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form id="devTypeForm" action="create_first_project.php" method="post">
                    <input type="hidden" name="dev_type" id="devTypeInput">
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="continueBtn" disabled>
                            ادامه <i class="fas fa-arrow-left ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.dev-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.dev-type-card:hover {
    transform: translateY(-5px);
    border-color: #667eea;
}

.dev-type-card.selected {
    border-color: #667eea;
    background-color: #f8f9fa;
}

.dev-icon {
    transition: transform 0.3s ease;
}

.dev-type-card:hover .dev-icon {
    transform: scale(1.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const devTypeCards = document.querySelectorAll('.dev-type-card');
    const devTypeInput = document.getElementById('devTypeInput');
    const continueBtn = document.getElementById('continueBtn');
    
    devTypeCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            devTypeCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            this.classList.add('selected');
            
            // Set the value
            devTypeInput.value = this.dataset.type;
            
            // Enable continue button
            continueBtn.disabled = false;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>