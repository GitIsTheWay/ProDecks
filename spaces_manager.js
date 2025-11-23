// js/spaces_manager.js - مدیریت Spaces
class SpacesManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeAnimations();
    }

    setupEventListeners() {
        // مدیریت فرم ایجاد Space
        const createSpaceForm = document.getElementById('createSpaceForm');
        if (createSpaceForm) {
            createSpaceForm.addEventListener('submit', this.handleCreateSpace.bind(this));
        }

        // مدیریت جستجو
        const searchInput = document.getElementById('spaceSearch');
        if (searchInput) {
            searchInput.addEventListener('input', this.handleSearch.bind(this));
        }
    }

    initializeAnimations() {
        // انیمیشن برای کارت‌های Space
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-scale');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.space-card').forEach(card => {
            observer.observe(card);
        });
    }

    async handleCreateSpace(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        try {
            this.showLoading();
            const response = await fetch('create_space.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                window.location.href = response.url;
            } else {
                this.showNotification('خطا در ایجاد Space', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    handleSearch(e) {
        const searchTerm = e.target.value.toLowerCase();
        const spaceCards = document.querySelectorAll('.space-card');

        spaceCards.forEach(card => {
            const spaceName = card.querySelector('h5').textContent.toLowerCase();
            const spaceDescription = card.querySelector('.card-text').textContent.toLowerCase();
            
            if (spaceName.includes(searchTerm) || spaceDescription.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    openEditSpaceModal(spaceId) {
        fetch(`get_space.php?id=${spaceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_space_id').value = data.space.id;
                    document.getElementById('edit_space_name').value = data.space.name;
                    document.getElementById('edit_space_description').value = data.space.description;
                    document.getElementById('edit_space_color').value = data.space.color;
                    
                    const modal = new bootstrap.Modal(document.getElementById('editSpaceModal'));
                    modal.show();
                } else {
                    this.showNotification('خطا در بارگذاری اطلاعات Space', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('خطا در بارگذاری اطلاعات Space', 'error');
            });
    }

    async deleteSpace(spaceId) {
        if (confirm('آیا از حذف این Space اطمینان دارید؟ تمام Decks و Cards مربوطه نیز حذف خواهند شد.')) {
            try {
                this.showLoading();
                const response = await fetch('delete_space.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `space_id=${spaceId}`
                });

                const result = await response.json();
                this.hideLoading();

                if (result.success) {
                    this.showNotification('Space با موفقیت حذف شد', 'success');
                    // حذف Space از UI
                    const spaceElement = document.querySelector(`[data-space-id="${spaceId}"]`);
                    if (spaceElement) {
                        spaceElement.style.transition = 'all 0.3s ease';
                        spaceElement.style.opacity = '0';
                        spaceElement.style.height = '0';
                        spaceElement.style.margin = '0';
                        
                        setTimeout(() => {
                            spaceElement.remove();
                        }, 300);
                    }
                } else {
                    this.showNotification('خطا در حذف Space: ' + result.error, 'error');
                }
            } catch (error) {
                this.hideLoading();
                console.error('Error:', error);
                this.showNotification('خطا در ارتباط با سرور', 'error');
            }
        }
    }

    joinSpace(inviteCode) {
        if (!inviteCode) {
            inviteCode = document.getElementById('space_code').value;
        }

        if (!inviteCode) {
            this.showNotification('لطفا کد دعوت را وارد کنید', 'warning');
            return;
        }

        this.showLoading();
        fetch('join_space.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `invite_code=${inviteCode}`
        })
        .then(response => {
            if (response.ok) {
                window.location.href = response.url;
            } else {
                this.showNotification('خطا در پیوستن به Space', 'error');
            }
        })
        .catch(error => {
            this.hideLoading();
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        });
    }

    // Utility functions
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            left: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        `;
        
        const icon = this.getNotificationIcon(type);
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <span class="me-2">${icon}</span>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                const bsAlert = new bootstrap.Alert(notification);
                bsAlert.close();
            }
        }, duration);
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    showLoading() {
        let loading = document.getElementById('global-loading');
        if (!loading) {
            loading = document.createElement('div');
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
    }

    hideLoading() {
        const loading = document.getElementById('global-loading');
        if (loading) {
            loading.remove();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.spacesManager = new SpacesManager();
});