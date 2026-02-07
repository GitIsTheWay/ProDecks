// js/script.js - فایل اصلی جاوااسکریپت سازگار با معماری 4 لایه‌ای
class ProDecksApp {
    constructor() {
        this.currentUser = null;
        this.csrfToken = this.getCsrfToken();
        this.init();
    }

    init() {
        console.log('ProDecksApp initialized - New 4-Layer Architecture');
        this.initializeComponents();
        this.setupGlobalEventListeners();
        this.initializeNotifications();
        this.setupKeyboardShortcuts();
        this.checkUserSession();
        
        // Initialize specific page features
        this.initializePageSpecificFeatures();
    }

    initializeComponents() {
        // Initialize Bootstrap components
        this.initializeBootstrapComponents();
        
        // Initialize animations
        this.initializeAnimations();
        
        // Initialize AJAX forms
        this.setupAjaxForms();
    }

    initializeBootstrapComponents() {
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Toasts
        const toastElList = [].slice.call(document.querySelectorAll('.toast'));
        const toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
        });
        
        // Show all toasts
        toastList.forEach(toast => toast.show());
    }

    initializeAnimations() {
        // Initialize scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated-card');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe animated elements
        document.querySelectorAll('.card, .project-card, .space-card, .deck-card, .card-item')
            .forEach(element => observer.observe(element));

        // Add hover animations
        this.setupHoverEffects();
    }

    setupHoverEffects() {
        // Card hover effects
        document.querySelectorAll('.clickable-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.classList.add('hover-effect');
            });
            card.addEventListener('mouseleave', () => {
                card.classList.remove('hover-effect');
            });
        });
    }

    setupGlobalEventListeners() {
        // Global click handlers
        document.addEventListener('click', (e) => {
            // Handle dynamic modal triggers
            if (e.target.matches('[data-modal-target]')) {
                this.openDynamicModal(e.target.dataset.modalTarget);
            }

            // Handle delete confirmations
            if (e.target.matches('[data-delete-action]')) {
                this.handleDeleteAction(e);
            }

            // Handle card status toggles
            if (e.target.closest('[data-toggle-status]')) {
                this.toggleCardStatus(e);
            }
        });

        // Global form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('.ajax-form')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });
    }

    initializeNotifications() {
        // Show any pending notifications from session
        this.showPendingNotifications();
        
        // Initialize notification system
        window.showNotification = (message, type = 'info', duration = 5000) => {
            this.showNotification(message, type, duration);
        };
    }

    showPendingNotifications() {
        // Check URL for success/error messages
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('success')) {
            this.showNotification(urlParams.get('success'), 'success');
            // Clean URL
            this.cleanUrl();
        }
        
        if (urlParams.has('error')) {
            this.showNotification(urlParams.get('error'), 'error');
            this.cleanUrl();
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.focusSearch();
            }

            // Ctrl/Cmd + N for new item (context aware)
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                this.handleNewItemShortcut();
            }

            // Escape to close modals
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    checkUserSession() {
        // Check if user session is valid (optional)
        // Can be used for session timeout warnings
        const lastActivity = localStorage.getItem('lastActivity');
        if (lastActivity) {
            const now = Date.now();
            const timeDiff = now - parseInt(lastActivity);
            
            if (timeDiff > 30 * 60 * 1000) { // 30 minutes
                this.showNotification('جلسه کاری شما به زودی منقضی می‌شود', 'warning');
            }
        }
        
        // Update last activity time
        localStorage.setItem('lastActivity', Date.now().toString());
    }

    initializePageSpecificFeatures() {
        // Detect current page and initialize specific features
        const path = window.location.pathname;
        
        if (path.includes('dashboard.php')) {
            this.initializeDashboardFeatures();
        } else if (path.includes('projects/index.php')) {
            this.initializeProjectPageFeatures();
        } else if (path.includes('spaces/index.php')) {
            this.initializeSpacePageFeatures();
        } else if (path.includes('decks/index.php')) {
            this.initializeDeckPageFeatures();
        }
    }

    initializeDashboardFeatures() {
        console.log('Initializing dashboard features');
        
        // Project search
        const searchInput = document.getElementById('projectSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterProjects(e.target.value);
            });
        }

        // Project sorting
        const sortSelect = document.getElementById('projectSort');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortProjects(e.target.value);
            });
        }

        // Initialize project stats animation
        this.animateStats();
    }

    initializeProjectPageFeatures() {
        console.log('Initializing project page features');
        
        // Space management
        this.setupSpaceDragAndDrop();
        
        // Initialize space stats
        this.updateProjectStats();
    }

    initializeSpacePageFeatures() {
        console.log('Initializing space page features');
        
        // Deck management
        this.setupDeckDragAndDrop();
        
        // Initialize deck stats
        this.updateSpaceStats();
    }

    initializeDeckPageFeatures() {
        console.log('Initializing deck page features');
        
        // Card drag and drop
        this.setupCardDragAndDrop();
        
        // Priority filtering
        this.setupPriorityFilters();
        
        // Card status updates
        this.setupCardStatusHandlers();
    }

    // AJAX Form Handling
    setupAjaxForms() {
        document.querySelectorAll('.ajax-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.submitAjaxForm(form);
            });
        });
    }

    async submitAjaxForm(form) {
        const formData = new FormData(form);
        const action = form.getAttribute('action') || form.dataset.action;
        const method = form.getAttribute('method') || 'POST';
        
        if (!action) {
            console.error('No action specified for form');
            return;
        }

        this.showLoading();

        try {
            const response = await fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification(data.message || 'عملیات با موفقیت انجام شد', 'success');
                
                // Handle success actions
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else if (data.reload) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else if (form.dataset.resetOnSuccess !== 'false') {
                    form.reset();
                }
                
                // Trigger custom success callback
                if (form.dataset.successCallback) {
                    this[form.dataset.successCallback](data);
                }
            } else {
                this.showNotification(data.error || 'خطا در انجام عملیات', 'error');
                
                // Show validation errors
                if (data.errors) {
                    this.showFormErrors(form, data.errors);
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // Notification System
    showNotification(message, type = 'info', duration = 5000) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.global-notification');
        existingNotifications.forEach(n => n.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `global-notification notification ${type}`;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${icons[type] || 'fa-info-circle'}"></i>
                <span>${message}</span>
                <button class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Add close button functionality
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, duration);
        }

        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
    }

    // Loading States
    showLoading(message = 'در حال بارگذاری...') {
        let loading = document.getElementById('global-loading');
        
        if (!loading) {
            loading = document.createElement('div');
            loading.id = 'global-loading';
            loading.className = 'global-loading';
            loading.innerHTML = `
                <div class="loading-overlay"></div>
                <div class="loading-content">
                    <div class="spinner-border text-primary"></div>
                    <div class="loading-message">${message}</div>
                </div>
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

    // Drag and Drop System
    setupCardDragAndDrop() {
        if (!document.querySelector('.card-item')) return;

        // Implementation for card drag and drop
        // This would integrate with Sortable.js or similar library
        console.log('Card drag and drop initialized');
    }

    setupDeckDragAndDrop() {
        if (!document.querySelector('.deck-card')) return;
        
        console.log('Deck drag and drop initialized');
    }

    setupSpaceDragAndDrop() {
        if (!document.querySelector('.space-card')) return;
        
        console.log('Space drag and drop initialized');
    }

    // Utility Functions
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
               document.querySelector('input[name="csrf_token"]')?.value || 
               '';
    }

    cleanUrl() {
        // Remove query parameters from URL without reloading
        const url = new URL(window.location);
        const paramsToRemove = ['success', 'error', 'message'];
        
        paramsToRemove.forEach(param => {
            url.searchParams.delete(param);
        });
        
        window.history.replaceState({}, '', url);
    }

    async addExperience(points, activity = 'general') {
        try {
            const response = await fetch('add_experience.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    points: points,
                    activity: activity,
                    csrf_token: this.csrfToken
                })
            });

            const data = await response.json();
            
            if (data.success && data.level_up) {
                this.showLevelUpNotification(data.new_level);
                this.updateUserStats(data.new_level, data.new_experience);
            }
            
            return data;
        } catch (error) {
            console.error('Error adding experience:', error);
            return null;
        }
    }

    showLevelUpNotification(level) {
        const notification = document.createElement('div');
        notification.className = 'level-up-notification';
        notification.innerHTML = `
            <div class="level-up-content">
                <i class="fas fa-trophy"></i>
                <div>
                    <h4>تبریک! 🎉</h4>
                    <p>شما به سطح ${level} ارتقا یافتید!</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    updateUserStats(level, experience) {
        // Update level and experience in UI
        document.querySelectorAll('.user-level').forEach(el => {
            el.textContent = level;
        });
        
        document.querySelectorAll('.user-experience').forEach(el => {
            el.textContent = `${experience} XP`;
        });
        
        // Update experience bar
        const expPercentage = (experience % 100);
        document.querySelectorAll('.experience-fill').forEach(el => {
            el.style.width = `${expPercentage}%`;
        });
    }

    // Page-specific functions
    filterProjects(searchTerm) {
        const projects = document.querySelectorAll('.project-card');
        const lowerTerm = searchTerm.toLowerCase();
        
        projects.forEach(project => {
            const title = project.querySelector('.project-title').textContent.toLowerCase();
            const description = project.querySelector('.project-description').textContent.toLowerCase();
            
            if (title.includes(lowerTerm) || description.includes(lowerTerm)) {
                project.style.display = 'block';
            } else {
                project.style.display = 'none';
            }
        });
    }

    sortProjects(criteria) {
        const container = document.querySelector('#projects-container') || 
                         document.querySelector('.row') || 
                         document.querySelector('.project-grid');
        
        if (!container) return;
        
        const projects = Array.from(container.querySelectorAll('.project-card'));
        
        projects.sort((a, b) => {
            switch (criteria) {
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'date':
                    return new Date(b.dataset.createdAt) - new Date(a.dataset.createdAt);
                case 'progress':
                    return (parseInt(b.dataset.progress) || 0) - (parseInt(a.dataset.progress) || 0);
                default:
                    return 0;
            }
        });
        
        // Reorder projects
        projects.forEach(project => container.appendChild(project));
    }

    animateStats() {
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const target = parseInt(stat.textContent);
            let current = 0;
            const increment = target / 50; // 50 frames
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                stat.textContent = Math.floor(current);
            }, 20);
        });
    }

    setupPriorityFilters() {
        const filters = document.querySelectorAll('.priority-filter');
        filters.forEach(filter => {
            filter.addEventListener('click', (e) => {
                const priority = e.target.dataset.priority;
                this.filterCardsByPriority(priority);
            });
        });
    }

    filterCardsByPriority(priority) {
        const cards = document.querySelectorAll('.card-item');
        
        cards.forEach(card => {
            if (priority === 'all' || card.dataset.priority === priority) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    setupCardStatusHandlers() {
        document.querySelectorAll('.toggle-card-status').forEach(button => {
            button.addEventListener('click', async (e) => {
                const cardId = e.target.dataset.cardId;
                await this.toggleCardStatus(cardId);
            });
        });
    }

    async toggleCardStatus(cardId) {
        try {
            const response = await fetch('cards/toggle_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `card_id=${cardId}`
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('وضعیت کارت به‌روزرسانی شد', 'success');
                // Update UI
                this.updateCardStatusUI(cardId, data.new_status);
                
                // Add experience
                if (data.new_status === 'done') {
                    this.addExperience(5, 'card_completed');
                }
            }
        } catch (error) {
            console.error('Error toggling card status:', error);
        }
    }

    updateCardStatusUI(cardId, newStatus) {
        const card = document.querySelector(`[data-card-id="${cardId}"]`);
        if (card) {
            const statusIcon = card.querySelector('.status-icon');
            if (statusIcon) {
                statusIcon.className = `status-icon fas fa-${newStatus === 'done' ? 'check-square' : 'square'}`;
            }
            card.dataset.status = newStatus;
        }
    }

    // Keyboard shortcut handlers
    focusSearch() {
        const searchInput = document.getElementById('globalSearch') || 
                          document.getElementById('projectSearch') ||
                          document.getElementById('spaceSearch') ||
                          document.getElementById('deckSearch');
        
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    handleNewItemShortcut() {
        // Context-aware new item creation
        const path = window.location.pathname;
        
        if (path.includes('projects/index.php')) {
            // Open new space modal
            const modal = new bootstrap.Modal(document.getElementById('createSpaceModal'));
            modal.show();
        } else if (path.includes('spaces/index.php')) {
            // Open new deck modal
            const modal = new bootstrap.Modal(document.getElementById('createDeckModal'));
            modal.show();
        } else if (path.includes('decks/index.php')) {
            // Open new card modal
            const modal = new bootstrap.Modal(document.getElementById('createCardModal'));
            modal.show();
        } else if (path.includes('dashboard.php')) {
            // Open new project modal
            const modal = new bootstrap.Modal(document.getElementById('createProjectModal'));
            modal.show();
        }
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }

    // Error handling
    showFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });

        // Show new errors
        Object.entries(errors).forEach(([field, message]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = message;
                
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    // Analytics and tracking (optional)
    trackEvent(action, category, label, value) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                'event_category': category,
                'event_label': label,
                'value': value
            });
        }
        
        // Internal tracking
        const events = JSON.parse(localStorage.getItem('prodecks_events') || '[]');
        events.push({
            action,
            category,
            label,
            value,
            timestamp: new Date().toISOString(),
            page: window.location.pathname
        });
        localStorage.setItem('prodecks_events', JSON.stringify(events.slice(-100))); // Keep last 100 events
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    window.proDecksApp = new ProDecksApp();
    
    // Make app globally available
    console.log('ProDecks Application Loaded - 4 Layer Architecture Ready');
    
    // Track page view
    if (window.proDecksApp) {
        window.proDecksApp.trackEvent('page_view', 'navigation', window.location.pathname);
    }
});

// Global helper functions
window.refreshPage = function() {
    window.location.reload();
};

window.goBack = function() {
    window.history.back();
};

window.formatDate = function(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        calendar: 'persian',
        numberingSystem: 'arab'
    };
    return new Date(dateString).toLocaleDateString('fa-IR', options);
};

window.truncateText = function(text, maxLength = 100) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};