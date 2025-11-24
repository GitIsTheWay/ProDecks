// js/spaces_manager.js - سیستم مدیریت Spaces
class SpacesManager {
    constructor() {
        this.currentSpaceId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeAnimations();
        this.loadSpaces();
        console.log('SpacesManager initialized');
    }

    setupEventListeners() {
        // مدیریت فرم ایجاد Space
        const createSpaceForm = document.getElementById('createSpaceForm');
        if (createSpaceForm) {
            createSpaceForm.addEventListener('submit', (e) => this.handleCreateSpace(e));
        }

        // مدیریت جستجو
        const searchInput = document.getElementById('spaceSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e));
        }

        // مدیریت کلیک‌ها
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-space-btn') || e.target.closest('.edit-space-btn')) {
                const spaceId = e.target.dataset.spaceId || e.target.closest('[data-space-id]').dataset.spaceId;
                this.openEditSpaceModal(spaceId);
            }
            
            if (e.target.classList.contains('delete-space-btn') || e.target.closest('.delete-space-btn')) {
                const spaceId = e.target.dataset.spaceId || e.target.closest('[data-space-id]').dataset.spaceId;
                this.deleteSpace(spaceId);
            }
            
            if (e.target.classList.contains('join-space-btn') || e.target.closest('.join-space-btn')) {
                this.openJoinSpaceModal();
            }
        });
    }

    initializeAnimations() {
        // انیمیشن برای کارت‌های Space
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.space-card-codecks').forEach(card => {
            observer.observe(card);
        });
    }

    async loadSpaces() {
        try {
            const response = await fetch('spaces_manager.php?action=get_spaces');
            const data = await response.json();
            
            if (data.success) {
                this.renderSpaces(data.spaces);
            } else {
                this.showNotification('خطا در بارگذاری Spaces', 'error');
            }
        } catch (error) {
            console.error('Error loading spaces:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }

    renderSpaces(spaces) {
        const container = document.getElementById('spaces-container');
        if (!container) return;

        container.innerHTML = spaces.map(space => `
            <div class="space-card-codecks fade-in-up" data-space-id="${space.id}">
                <div class="space-card-header" style="background: ${space.color || '#667eea'}">
                    <h5 class="text-white mb-0">${space.name}</h5>
                </div>
                <div class="space-card-body">
                    <p class="text-muted">${space.description || 'بدون توضیحات'}</p>
                    <div class="space-stats">
                        <div class="stat-item">
                            <div class="stat-value">${space.decks_count || 0}</div>
                            <div class="stat-label">Decks</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${space.members_count || 1}</div>
                            <div class="stat-label">اعضا</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${space.cards_count || 0}</div>
                            <div class="stat-label">کارت‌ها</div>
                        </div>
                    </div>
                    <div class="space-actions mt-3">
                        <a href="space_decks.php?space_id=${space.id}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> مشاهده
                        </a>
                        <button class="btn btn-outline btn-sm edit-space-btn" data-space-id="${space.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${space.is_owner ? `
                            <button class="btn btn-outline btn-sm delete-space-btn" data-space-id="${space.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `).join('');
    }

    async handleCreateSpace(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        this.showLoading();
        try {
            const response = await fetch('create_space.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Space با موفقیت ایجاد شد', 'success');
                this.loadSpaces(); // بارگذاری مجدد Spaces
                this.hideModal('createSpaceModal');
                e.target.reset();
            } else {
                this.showNotification(data.error || 'خطا در ایجاد Space', 'error');
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
        const spaceCards = document.querySelectorAll('.space-card-codecks');
        
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
                    document.getElementById('edit_space_description').value = data.space.description || '';
                    document.getElementById('edit_space_color').value = data.space.color || '#667eea';
                    
                    const modal = new bootstrap.Modal(document.getElementById('editSpaceModal'));
                    modal.show();
                } else {
                    this.showNotification('خطا در بارگذاری اطلاعات Space', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('خطا در ارتباط با سرور', 'error');
            });
    }

    async deleteSpace(spaceId) {
        if (!confirm('آیا از حذف این Space اطمینان دارید؟ تمام Decks و Cards مربوطه نیز حذف خواهند شد.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('space_id', spaceId);
            formData.append('csrf_token', this.getCsrfToken());

            const response = await fetch('delete_space.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Space با موفقیت حذف شد', 'success');
                // حذف Space از UI
                const spaceElement = document.querySelector(`[data-space-id="${spaceId}"]`);
                if (spaceElement) {
                    spaceElement.style.opacity = '0';
                    setTimeout(() => spaceElement.remove(), 300);
                }
            } else {
                this.showNotification(data.error || 'خطا در حذف Space', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }

    async joinSpace(inviteCode = null) {
        if (!inviteCode) {
            inviteCode = document.getElementById('space_code').value;
        }
        
        if (!inviteCode) {
            this.showNotification('لطفا کد دعوت را وارد کنید', 'warning');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('invite_code', inviteCode);
            formData.append('csrf_token', this.getCsrfToken());

            const response = await fetch('join_space.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('با موفقیت به Space پیوستید', 'success');
                this.hideModal('joinSpaceModal');
                this.loadSpaces(); // بارگذاری مجدد Spaces
            } else {
                this.showNotification(data.error || 'خطا در پیوستن به Space', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }

    openJoinSpaceModal() {
        const modal = new bootstrap.Modal(document.getElementById('joinSpaceModal'));
        modal.show();
        document.getElementById('space_code').focus();
    }

    // توابع کمکی
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : 'fa-info'} me-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, duration);
    }

    showLoading() {
        let loading = document.getElementById('global-loading');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'global-loading';
            loading.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            loading.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
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

    hideModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    }
}

// مقداردهی اولیه
document.addEventListener('DOMContentLoaded', function() {
    window.spacesManager = new SpacesManager();
});