// js/script.js - فایل عمومی جاوااسکریپت
class ProDecksApp {
    constructor() {
        this.init();
    }

    init() {
        this.initializeToasts();
        this.setupGlobalEventListeners();
        this.initializeAnimations();
        console.log('ProDecksApp initialized');
    }

    initializeToasts() {
        // فعال کردن همه توست‌های بوت‌استرپ
        const toastElList = [].slice.call(document.querySelectorAll('.toast'));
        const toastList = toastElList.map(function(toastEl) {
            return new bootstrap.Toast(toastEl);
        });
        
        // نمایش توست‌ها
        toastList.forEach(toast => toast.show());
    }

    setupGlobalEventListeners() {
        // مدیریت فرم‌های عمومی
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'addSubcardForm') {
                e.preventDefault();
                this.handleAddSubcard(e);
            }
        });

        // مدیریت کلیک‌های عمومی
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('global-add-card')) {
                const deckId = e.target.dataset.deckId;
                if (window.spaceDecksManager) {
                    window.spaceDecksManager.openAddCardModal(deckId);
                }
            }
        });

        // کلیدهای میانبر جهانی
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K برای جستجو
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('spaceSearch') || document.getElementById('globalSearch');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    }

    initializeAnimations() {
        // انیمیشن برای عناصر هنگام اسکرول
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        // مشاهده عناصر دارای کلاس animated
        document.querySelectorAll('.card, .feature-card, .btn-primary').forEach(element => {
            observer.observe(element);
        });
    }

    async handleAddSubcard(e) {
        const formData = new FormData(e.target);
        
        this.showLoading();
        try {
            const response = await fetch('add_subcard.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('زیرکارت با موفقیت افزوده شد', 'success');
                this.hideModal('addSubcardModal');
                e.target.reset();
                
                // ریلود صفحه برای نمایش تغییرات
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(data.error || 'خطا در افزودن زیرکارت', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // تابع نمایش نوتیفیکیشن (برای استفاده عمومی)
    showNotification(message, type = 'info', duration = 5000) {
        // حذف نوتیفیکیشن‌های قبلی
        const existingNotifications = document.querySelectorAll('.global-notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = `global-notification notification ${type}`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${this.getNotificationIcon(type)} me-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // حذف خودکار نوتیفیکیشن
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, duration);
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
    }

    // تابع مدیریت تجربه کاربری
    async addUserExperience(points, activity = 'general') {
        try {
            const response = await fetch('add_experience.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    points: points,
                    activity: activity,
                    csrf_token: this.getCsrfToken()
                })
            });
            
            const data = await response.json();
            
            if (data.success && data.level_up) {
                this.showLevelUpNotification(data.new_level);
            }
            
            this.updateExperienceBar(data.new_experience, data.new_level);
            
        } catch (error) {
            console.error('Error adding experience:', error);
        }
    }

    // نمایش اطلاعیه افزایش سطح
    showLevelUpNotification(newLevel) {
        const popup = document.createElement('div');
        popup.className = 'achievement-popup bounce-in';
        popup.innerHTML = `
            <div class="text-center">
                <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                <h4>تبریک! 🎉</h4>
                <p>شما به سطح <strong>${newLevel}</strong> ارتقا یافتید!</p>
            </div>
        `;
        
        popup.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 10000;
            text-align: center;
            min-width: 300px;
        `;
        
        document.body.appendChild(popup);
        
        setTimeout(() => {
            if (popup.parentNode) {
                popup.parentNode.removeChild(popup);
            }
        }, 4000);
    }

    // به‌روزرسانی نوار تجربه
    updateExperienceBar(experience, level = null) {
        const experienceBar = document.querySelector('.experience-fill');
        const experienceText = document.querySelector('.experience-text');
        const levelElement = document.querySelector('.user-level');
        
        if (experienceBar) {
            // محاسبه درصد تجربه (فرض: هر سطح 100 امتیاز نیاز دارد)
            const expInLevel = experience % 100;
            const percentage = Math.min((expInLevel / 100) * 100, 100);
            experienceBar.style.width = `${percentage}%`;
        }
        
        if (experienceText) {
            experienceText.textContent = `${experience} XP`;
        }
        
        if (levelElement && level) {
            levelElement.textContent = `سطح ${level}`;
        }
    }

    // تابع کمکی برای فرمت تاریخ
    formatDate(dateString) {
        if (!dateString) return '-';
        
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            calendar: 'persian',
            numberingSystem: 'arab'
        };
        
        try {
            return new Date(dateString).toLocaleDateString('fa-IR', options);
        } catch (error) {
            return dateString;
        }
    }

    // مدیریت وضعیت لودینگ
    showLoading(message = 'در حال بارگذاری...') {
        let loading = document.getElementById('global-loading');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'global-loading';
            loading.innerHTML = `
                <div class="loading-content text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">${message}</p>
                </div>
            `;
            loading.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255,255,255,0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            `;
            document.body.appendChild(loading);
        }
        loading.style.display = 'flex';
    }

    hideLoading() {
        const loading = document.getElementById('global-loading');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    // مدیریت خطاهای شبکه
    handleNetworkError(error) {
        console.error('Network error:', error);
        this.showNotification('خطا در ارتباط با سرور. لطفا اتصال اینترنت خود را بررسی کنید.', 'error', 10000);
    }

    // تابع دریافت CSRF Token
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    hideModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    }
}

// تابع برای ردیابی رویدادها (آنالیتیکس)
function trackUserAction(action, details = {}) {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, details);
    }
    
    // ذخیره در localStorage برای آنالیز داخلی
    try {
        const userActions = JSON.parse(localStorage.getItem('prodecks_user_actions') || '[]');
        userActions.push({
            action: action,
            details: details,
            timestamp: new Date().toISOString(),
            page: window.location.pathname
        });
        localStorage.setItem('prodecks_user_actions', JSON.stringify(userActions));
    } catch (error) {
        console.error('Error tracking action:', error);
    }
}

// مقداردهی اولیه وقتی صفحه لود شد
document.addEventListener('DOMContentLoaded', function() {
    window.proDecksApp = new ProDecksApp();
    
    // ردیابی بازدید صفحه
    trackUserAction('page_view', {
        page_title: document.title,
        page_location: window.location.href
    });
});