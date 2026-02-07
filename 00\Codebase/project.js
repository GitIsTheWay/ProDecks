// js/project.js - اسکریپت‌های مدیریت پروژه سازگار با معماری جدید
class ProjectManager {
    constructor() {
        this.currentProjectId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeModals();
        this.setupAjaxHandlers();
        console.log('ProjectManager initialized');
    }

    setupEventListeners() {
        // مدیریت کلیک بر روی کارت‌های پروژه
        document.addEventListener('click', (e) => {
            if (e.target.closest('.project-card')) {
                const projectCard = e.target.closest('.project-card');
                const projectId = projectCard.dataset.projectId;
                this.openProject(projectId);
            }

            // مدیریت دکمه ویرایش پروژه
            if (e.target.closest('.edit-project-btn')) {
                e.preventDefault();
                const projectId = e.target.closest('.edit-project-btn').dataset.projectId;
                this.openEditProjectModal(projectId);
            }

            // مدیریت دکمه حذف پروژه
            if (e.target.closest('.delete-project-btn')) {
                e.preventDefault();
                const projectId = e.target.closest('.delete-project-btn').dataset.projectId;
                this.deleteProject(projectId);
            }
        });

        // مدیریت فرم‌ها
        const createProjectForm = document.getElementById('createProjectForm');
        if (createProjectForm) {
            createProjectForm.addEventListener('submit', (e) => this.handleCreateProject(e));
        }

        const editProjectForm = document.getElementById('editProjectForm');
        if (editProjectForm) {
            editProjectForm.addEventListener('submit', (e) => this.handleEditProject(e));
        }

        // کلیدهای میانبر
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + N برای ایجاد پروژه جدید
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                this.openCreateProjectModal();
            }
        });
    }

    initializeModals() {
        // مقداردهی اولیه مودال‌های پروژه
        const projectModals = ['createProjectModal', 'editProjectModal'];
        projectModals.forEach(modalId => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                modalElement.addEventListener('show.bs.modal', () => {
                    this.onModalShow(modalId);
                });
                modalElement.addEventListener('hidden.bs.modal', () => {
                    this.onModalHide(modalId);
                });
            }
        });
    }

    setupAjaxHandlers() {
        // تنظیم هدر CSRF برای درخواست‌های AJAX
        $.ajaxSetup({
            beforeSend: function(xhr) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
            }
        });
    }

    // توابع مدیریت پروژه
    async openProject(projectId) {
        window.location.href = `projects/index.php?id=${projectId}`;
    }

    openCreateProjectModal() {
        const modal = new bootstrap.Modal(document.getElementById('createProjectModal'));
        modal.show();
    }

    async openEditProjectModal(projectId) {
        try {
            const response = await fetch(`get_project.php?id=${projectId}`);
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('edit_project_id').value = data.project.id;
                document.getElementById('edit_project_name').value = data.project.name;
                document.getElementById('edit_project_description').value = data.project.description || '';
                document.getElementById('edit_project_color').value = data.project.color || '#667eea';
                
                const modal = new bootstrap.Modal(document.getElementById('editProjectModal'));
                modal.show();
            } else {
                this.showNotification('خطا در بارگذاری اطلاعات پروژه', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }

    async handleCreateProject(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        this.showLoading('در حال ایجاد پروژه...');
        
        try {
            const response = await fetch('projects/create.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('پروژه با موفقیت ایجاد شد', 'success');
                this.hideModal('createProjectModal');
                e.target.reset();
                
                // ریدایرکت به صفحه پروژه جدید
                setTimeout(() => {
                    window.location.href = `projects/index.php?id=${data.project_id}`;
                }, 1000);
            } else {
                this.showNotification(data.error || 'خطا در ایجاد پروژه', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async handleEditProject(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const projectId = formData.get('project_id');
        
        this.showLoading('در حال به‌روزرسانی پروژه...');
        
        try {
            const response = await fetch('projects/edit.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                this.showNotification('پروژه با موفقیت ویرایش شد', 'success');
                this.hideModal('editProjectModal');
                
                // به‌روزرسانی UI
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification('خطا در ویرایش پروژه', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async deleteProject(projectId) {
        if (!confirm('آیا از حذف این پروژه اطمینان دارید؟ تمام Spaces، Decks و Cards مربوطه نیز حذف خواهند شد.')) {
            return;
        }
        
        this.showLoading('در حال حذف پروژه...');
        
        try {
            const formData = new FormData();
            formData.append('project_id', projectId);
            
            const response = await fetch('projects/delete.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                this.showNotification('پروژه با موفقیت حذف شد', 'success');
                
                // حذف از UI
                const projectElement = document.querySelector(`[data-project-id="${projectId}"]`);
                if (projectElement) {
                    projectElement.style.opacity = '0';
                    setTimeout(() => {
                        projectElement.remove();
                    }, 300);
                }
                
                // اگر در صفحه پروژه بودیم، به داشبورد برگردیم
                if (window.location.pathname.includes('projects/index.php')) {
                    setTimeout(() => {
                        window.location.href = '../dashboard.php';
                    }, 1000);
                }
            } else {
                this.showNotification('خطا در حذف پروژه', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // توابع کمکی
    showNotification(message, type = 'info', duration = 5000) {
        // استفاده از سیستم نوتیفیکیشن موجود
        if (window.proDecksApp && typeof window.proDecksApp.showNotification === 'function') {
            window.proDecksApp.showNotification(message, type, duration);
        } else {
            // پیاده‌سازی ساده
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid') || document.querySelector('.container');
            container.prepend(notification);
            
            setTimeout(() => {
                notification.remove();
            }, duration);
        }
    }

    showLoading(message = 'در حال بارگذاری...') {
        // استفاده از سیستم لودینگ موجود
        if (window.proDecksApp && typeof window.proDecksApp.showLoading === 'function') {
            window.proDecksApp.showLoading(message);
        } else {
            console.log('Loading:', message);
        }
    }

    hideLoading() {
        if (window.proDecksApp && typeof window.proDecksApp.hideLoading === 'function') {
            window.proDecksApp.hideLoading();
        } else {
            console.log('Loading hidden');
        }
    }

    hideModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        }
    }

    onModalShow(modalId) {
        // اجرای کدهای لازم هنگام نمایش مودال
        console.log(`Modal ${modalId} shown`);
    }

    onModalHide(modalId) {
        // اجرای کدهای لازم هنگام بسته شدن مودال
        console.log(`Modal ${modalId} hidden`);
    }

    // توابع آماری و گزارش‌گیری
    async getProjectStats(projectId) {
        try {
            const response = await fetch(`get_project_stats.php?id=${projectId}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching project stats:', error);
            return null;
        }
    }

    // توابع مرتب‌سازی و فیلتر
    sortProjects(sortBy = 'name') {
        const projectsContainer = document.getElementById('projects-container');
        if (!projectsContainer) return;

        const projects = Array.from(projectsContainer.querySelectorAll('.project-card'));
        
        projects.sort((a, b) => {
            switch (sortBy) {
                case 'name':
                    return a.querySelector('.project-title').textContent.localeCompare(b.querySelector('.project-title').textContent);
                case 'date':
                    return new Date(b.dataset.createdAt) - new Date(a.dataset.createdAt);
                case 'progress':
                    const progressA = parseInt(a.dataset.progress) || 0;
                    const progressB = parseInt(b.dataset.progress) || 0;
                    return progressB - progressA;
                default:
                    return 0;
            }
        });

        // مرتب‌سازی مجدد پروژه‌ها
        projects.forEach(project => projectsContainer.appendChild(project));
    }

    filterProjects(searchTerm) {
        const projects = document.querySelectorAll('.project-card');
        
        projects.forEach(project => {
            const title = project.querySelector('.project-title').textContent.toLowerCase();
            const description = project.querySelector('.project-description').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                project.style.display = 'block';
            } else {
                project.style.display = 'none';
            }
        });
    }
}

// مقداردهی اولیه
document.addEventListener('DOMContentLoaded', function() {
    window.projectManager = new ProjectManager();
    
    // تنظیمات اضافی برای صفحه پروژه
    if (document.querySelector('.project-page')) {
        initializeProjectPage();
    }
});

function initializeProjectPage() {
    // راه‌اندازی ویژگی‌های خاص صفحه پروژه
    const searchInput = document.getElementById('projectSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            window.projectManager.filterProjects(e.target.value.toLowerCase());
        });
    }

    // راه‌اندازی مرتب‌سازی
    const sortSelect = document.getElementById('projectSort');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            window.projectManager.sortProjects(e.target.value);
        });
    }
}

// توابع عمومی برای استفاده در سایر اسکریپت‌ها
function refreshProjectStats(projectId) {
    if (window.projectManager) {
        return window.projectManager.getProjectStats(projectId);
    }
}

function exportProjectData(projectId, format = 'json') {
    // پیاده‌سازی خروجی گرفتن از داده‌های پروژه
    console.log(`Exporting project ${projectId} in ${format} format`);
}