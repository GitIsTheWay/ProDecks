// js/script.js - فایل عمومی جاوااسکریپت

// فعال کردن توست‌های بوت‌استرپ
document.addEventListener('DOMContentLoaded', function() {
    // فعال کردن همه توست‌ها
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl);
    });
    
    // نمایش توست‌ها
    toastList.forEach(toast => toast.show());
});

// تابع نمایش نوتیفیکیشن
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        left: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    `;
    
    const icon = getNotificationIcon(type);
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <span class="me-2">${icon}</span>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // حذف خودکار نوتیفیکیشن
    setTimeout(() => {
        if (notification.parentNode) {
            const bsAlert = new bootstrap.Alert(notification);
            bsAlert.close();
        }
    }, duration);
}

// آیکون‌های نوتیفیکیشن
function getNotificationIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
}

// تابع مدیریت تجربه کاربری
function addUserExperience(points, activity) {
    fetch('add_experience.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            points: points,
            activity: activity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.level_up) {
                showLevelUpNotification(data.new_level);
            }
            updateExperienceBar(data.new_experience);
        }
    })
    .catch(error => {
        console.error('Error adding experience:', error);
    });
}

// نمایش اطلاعیه افزایش سطح
function showLevelUpNotification(newLevel) {
    const popup = document.createElement('div');
    popup.className = 'achievement-popup';
    popup.innerHTML = `
        <div class="text-center">
            <div class="mb-2">
                <i class="fas fa-trophy fa-2x text-warning"></i>
            </div>
            <h5 class="mb-1">تبریک! 🎉</h5>
            <p class="mb-0">شما به سطح <strong>${newLevel}</strong> رسیدید!</p>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    setTimeout(() => {
        if (popup.parentNode) {
            popup.parentNode.removeChild(popup);
        }
    }, 5000);
}

// به‌روزرسانی نوار تجربه
function updateExperienceBar(experience) {
    const experiencePercentage = (experience % 100);
    const experienceBar = document.querySelector('.experience-fill');
    const experienceText = document.querySelector('.experience-text');
    
    if (experienceBar) {
        experienceBar.style.width = `${experiencePercentage}%`;
    }
    
    if (experienceText) {
        experienceText.textContent = `${experience} XP`;
    }
}

// تابع کمکی برای فرمت تاریخ
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        calendar: 'persian'
    };
    return new Date(dateString).toLocaleDateString('fa-IR', options);
}

// مدیریت وضعیت لودینگ
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'global-loading';
    loading.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
    loading.style.cssText = `
        background: rgba(0,0,0,0.5);
        z-index: 9999;
    `;
    loading.innerHTML = `
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('global-loading');
    if (loading) {
        loading.remove();
    }
}

// مدیریت خطاهای شبکه
function handleNetworkError(error) {
    console.error('Network error:', error);
    showNotification('خطا در ارتباط با سرور. لطفا اتصال اینترنت خود را بررسی کنید.', 'error');
}

// ثبت کلیک‌های مهم برای آنالیتیکس
function trackUserAction(action, details = {}) {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, details);
    }
    
    // ذخیره در localStorage برای آنالیز داخلی
    const userActions = JSON.parse(localStorage.getItem('user_actions') || '[]');
    userActions.push({
        action,
        details,
        timestamp: new Date().toISOString()
    });
    localStorage.setItem('user_actions', JSON.stringify(userActions));
}

// مقداردهی اولیه وقتی صفحه لود شد
document.addEventListener('DOMContentLoaded', function() {
    // ردیابی بازدید صفحه
    trackUserAction('page_view', {
        page_title: document.title,
        page_location: window.location.href
    });
    
    // اضافه کردن انیمیشن به عناصر
    const animatedElements = document.querySelectorAll('.card, .feature-card, .btn');
    animatedElements.forEach(element => {
        element.classList.add('fade-in');
    });
});