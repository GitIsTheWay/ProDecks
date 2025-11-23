// js/space_decks.js - سیستم پیشرفته مدیریت Decks و Cards - نسخه اصلاح شده
class SpaceDecksManager {
    constructor() {
        this.draggedElement = null;
        this.currentDeckId = null;
        this.init();
    }

    init() {
        this.initializeDragAndDrop();
        this.initializeSortable();
        this.setupEventListeners();
        this.showWelcomeNotification();
        this.handleSuccessMessages();
    }

    handleSuccessMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success') === 'deck_created') {
            this.showNotification('Deck جدید با موفقیت ایجاد شد', 'success');
        }
        if (urlParams.get('success') === 'deck_updated') {
            this.showNotification('Deck با موفقیت ویرایش شد', 'success');
        }
    }

    initializeDragAndDrop() {
        const draggableCards = document.querySelectorAll('.card-item');
        const deckBodies = document.querySelectorAll('.deck-body-advanced');

        draggableCards.forEach(card => {
            card.addEventListener('dragstart', (e) => {
                this.draggedElement = card;
                this.currentDeckId = card.closest('.deck-body-advanced').dataset.deckId;
                e.dataTransfer.setData('text/plain', card.dataset.cardId);
                card.classList.add('dragging');
                
                setTimeout(() => {
                    card.style.opacity = '0.4';
                }, 0);
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
                card.style.opacity = '1';
                this.draggedElement = null;
                this.currentDeckId = null;
                
                deckBodies.forEach(deck => {
                    deck.classList.remove('drag-over');
                });
            });
        });

        deckBodies.forEach(deck => {
            deck.addEventListener('dragover', (e) => {
                e.preventDefault();
                deck.classList.add('drag-over');
                deck.style.backgroundColor = 'rgba(255,255,255,0.1)';
            });

            deck.addEventListener('dragleave', () => {
                deck.classList.remove('drag-over');
                deck.style.backgroundColor = '';
            });

            deck.addEventListener('drop', (e) => {
                e.preventDefault();
                deck.classList.remove('drag-over');
                deck.style.backgroundColor = '';

                if (this.draggedElement) {
                    const cardId = this.draggedElement.dataset.cardId;
                    const newDeckId = deck.dataset.deckId;

                    if (this.currentDeckId !== newDeckId) {
                        this.draggedElement.style.transition = 'all 0.3s ease';
                        deck.appendChild(this.draggedElement);
                        this.updateCardDeck(cardId, newDeckId);
                    }
                }
            });
        });
    }

    initializeSortable() {
        const decksContainer = document.getElementById('decks-container');
        
        if (decksContainer) {
            new Sortable(decksContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.deck-header-advanced',
                onEnd: (evt) => {
                    const deckOrder = [];
                    document.querySelectorAll('.deck-card-advanced[data-deck-id]').forEach((deck, index) => {
                        if (deck.dataset.deckId) {
                            deckOrder.push({
                                deck_id: deck.dataset.deckId,
                                position: index + 1
                            });
                        }
                    });
                    
                    this.updateDeckOrder(deckOrder);
                }
            });
        }

        document.querySelectorAll('.deck-body-advanced').forEach(deckBody => {
            new Sortable(deckBody, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                group: 'shared-cards',
                onEnd: (evt) => {
                    const cardId = evt.item.dataset.cardId;
                    const newDeckId = evt.to.dataset.deckId;
                    const oldDeckId = evt.from.dataset.deckId;
                    
                    if (oldDeckId !== newDeckId) {
                        this.updateCardDeck(cardId, newDeckId);
                    } else {
                        this.updateCardOrder(newDeckId);
                    }
                }
            });
        });
    }

    setupEventListeners() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const firstDeck = document.querySelector('.deck-card-advanced');
                if (firstDeck) {
                    const deckId = firstDeck.dataset.deckId;
                    this.openAddCardModal(deckId);
                }
            }
            
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    const modal = bootstrap.Modal.getInstance(openModal);
                    modal.hide();
                }
            }
        });

        // Form submissions with AJAX
        this.setupAjaxForms();
    }

    setupAjaxForms() {
        // Create Deck Form
        const createDeckForm = document.getElementById('createDeckForm');
        if (createDeckForm) {
            createDeckForm.addEventListener('submit', this.handleCreateDeck.bind(this));
        }

        // Edit Deck Form
        const editDeckForm = document.getElementById('editDeckForm');
        if (editDeckForm) {
            editDeckForm.addEventListener('submit', this.handleEditDeck.bind(this));
        }
    }

    async handleCreateDeck(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            this.showLoading();
            const response = await fetch('create_deck.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                window.location.href = await response.url;
            } else {
                this.showNotification('خطا در ایجاد Deck', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async handleEditDeck(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            this.showLoading();
            const response = await fetch('edit_deck.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                window.location.href = await response.url;
            } else {
                this.showNotification('خطا در ویرایش Deck', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    // Modal Functions
    openAddCardModal(deckId) {
        document.getElementById('card_deck_id').value = deckId;
        const modal = new bootstrap.Modal(document.getElementById('addCardModal'));
        modal.show();
        
        setTimeout(() => {
            document.getElementById('card_title').focus();
        }, 500);
    }

    openEditDeckModal(deckId) {
        this.showLoading();
        fetch(`get_deck.php?id=${deckId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_deck_id').value = data.deck.id;
                    document.getElementById('edit_deck_name').value = data.deck.name;
                    document.getElementById('edit_deck_description').value = data.deck.description || '';
                    document.getElementById('edit_deck_color').value = data.deck.color || '#4a5568';
                    
                    const modal = new bootstrap.Modal(document.getElementById('editDeckModal'));
                    modal.show();
                } else {
                    this.showNotification('خطا در بارگذاری اطلاعات Deck', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('خطا در بارگذاری اطلاعات Deck', 'error');
            })
            .finally(() => {
                this.hideLoading();
            });
    }

    openEditCardModal(cardId) {
        this.showLoading();
        fetch(`get_card.php?id=${cardId}&type=card`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editCardModalBody').innerHTML = html;
                document.getElementById('edit_card_id').value = cardId;
                const modal = new bootstrap.Modal(document.getElementById('editCardModal'));
                modal.show();
                this.hideLoading();
            })
            .catch(error => {
                console.error('Error:', error);
                this.showNotification('خطا در بارگذاری اطلاعات کارت', 'error');
                this.hideLoading();
            });
    }

    openAddSubcardModal(parentCardId) {
        document.getElementById('subcard_parent_id').value = parentCardId;
        const modal = new bootstrap.Modal(document.getElementById('addSubcardModal'));
        modal.show();
        
        setTimeout(() => {
            document.getElementById('subcard_title').focus();
        }, 500);
    }

    // API Functions
    async updateCardDeck(cardId, newDeckId) {
        try {
            const formData = new FormData();
            formData.append('card_id', cardId);
            formData.append('new_deck_id', newDeckId);

            const response = await fetch('update_card_deck.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('کارت با موفقیت منتقل شد', 'success');
                this.addExperience(2);
                this.updateDeckCounts();
            } else {
                this.showNotification('خطا در انتقال کارت', 'error');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
            setTimeout(() => location.reload(), 1000);
        }
    }

    async updateDeckOrder(deckOrder) {
        try {
            const response = await fetch('update_deck_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ deck_order: deckOrder })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                this.showNotification('خطا در به‌روزرسانی ترتیب دک‌ها', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    async updateCardOrder(deckId) {
        const cardOrder = [];
        const deckBody = document.querySelector(`.deck-body-advanced[data-deck-id="${deckId}"]`);
        
        if (deckBody) {
            deckBody.querySelectorAll('.card-item').forEach((card, index) => {
                cardOrder.push({
                    card_id: card.dataset.cardId,
                    position: index + 1
                });
            });
            
            try {
                await fetch('update_card_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ card_order: cardOrder })
                });
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }

    async deleteDeck(deckId) {
        if (confirm('آیا از حذف این Deck اطمینان دارید؟ تمام Cards مربوطه نیز حذف خواهند شد.')) {
            this.showLoading();
            
            try {
                const formData = new FormData();
                formData.append('deck_id', deckId);

                const response = await fetch('delete_deck.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                this.hideLoading();
                
                if (data.success) {
                    this.showNotification('Deck با موفقیت حذف شد', 'success');
                    const deckElement = document.querySelector(`[data-deck-id="${deckId}"]`);
                    if (deckElement) {
                        deckElement.style.transition = 'all 0.3s ease';
                        deckElement.style.opacity = '0';
                        deckElement.style.height = '0';
                        deckElement.style.margin = '0';
                        
                        setTimeout(() => {
                            deckElement.remove();
                        }, 300);
                    }
                } else {
                    this.showNotification('خطا در حذف Deck: ' + (data.error || ''), 'error');
                }
            } catch (error) {
                this.hideLoading();
                console.error('Error:', error);
                this.showNotification('خطا در ارتباط با سرور', 'error');
            }
        }
    }

    async deleteCard(cardId) {
        if (confirm('آیا از حذف این کارت اطمینان دارید؟ این عمل غیرقابل بازگشت است.')) {
            this.showLoading();
            
            try {
                const formData = new FormData();
                formData.append('card_id', cardId);

                const response = await fetch('delete_card.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                this.hideLoading();
                
                if (data.success) {
                    this.showNotification('کارت با موفقیت حذف شد', 'success');
                    const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
                    if (cardElement) {
                        cardElement.style.transition = 'all 0.3s ease';
                        cardElement.style.opacity = '0';
                        cardElement.style.height = '0';
                        cardElement.style.margin = '0';
                        
                        setTimeout(() => {
                            cardElement.remove();
                            this.updateDeckCounts();
                        }, 300);
                    }
                } else {
                    this.showNotification('خطا در حذف کارت: ' + (data.error || ''), 'error');
                }
            } catch (error) {
                this.hideLoading();
                console.error('Error:', error);
                this.showNotification('خطا در ارتباط با سرور', 'error');
            }
        }
    }

    async deleteSubcard(subcardId) {
        if (confirm('آیا از حذف این زیرکارت اطمینان دارید؟')) {
            try {
                const formData = new FormData();
                formData.append('subcard_id', subcardId);

                const response = await fetch('delete_subcard.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('زیرکارت با موفقیت حذف شد', 'success');
                    const subcardElement = document.querySelector(`[data-subcard-id="${subcardId}"]`);
                    if (subcardElement) {
                        subcardElement.remove();
                    }
                } else {
                    this.showNotification('خطا در حذف زیرکارت', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('خطا در ارتباط با سرور', 'error');
            }
        }
    }

    // Utility Functions
    updateDeckCounts() {
        setTimeout(() => {
            document.querySelectorAll('.deck-card-advanced').forEach(deck => {
                const deckBody = deck.querySelector('.deck-body-advanced');
                const countBadge = deck.querySelector('.badge');
                if (deckBody && countBadge) {
                    const cardCount = deckBody.querySelectorAll('.card-item').length;
                    countBadge.textContent = cardCount;
                }
            });
        }, 100);
    }

    async addExperience(points) {
        try {
            const response = await fetch('add_experience.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ points: points })
            });
            
            const data = await response.json();
            
            if (data.level_up) {
                this.showLevelUpNotification(data.new_level);
                this.updateUserStats(data.new_level, data.new_experience);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    showLevelUpNotification(newLevel) {
        const popup = document.createElement('div');
        popup.className = 'achievement-popup fade-in-scale';
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

    showWelcomeNotification() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('new_user')) {
            setTimeout(() => {
                this.showNotification('به ProDecks پیشرفته خوش آمدید! 🚀 از Spaces و Decks جدید لذت ببرید.', 'success', 8000);
            }, 1000);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.spaceDecksManager = new SpaceDecksManager();
    
    // Global functions for onclick events
    window.openAddCardModal = function(deckId) {
        window.spaceDecksManager.openAddCardModal(deckId);
    };
    
    window.openEditDeckModal = function(deckId) {
        window.spaceDecksManager.openEditDeckModal(deckId);
    };
    
    window.openEditCardModal = function(cardId) {
        window.spaceDecksManager.openEditCardModal(cardId);
    };
    
    window.openAddSubcardModal = function(parentCardId) {
        window.spaceDecksManager.openAddSubcardModal(parentCardId);
    };
    
    window.deleteDeck = function(deckId) {
        window.spaceDecksManager.deleteDeck(deckId);
    };
    
    window.deleteCard = function(cardId) {
        window.spaceDecksManager.deleteCard(cardId);
    };
    
    window.deleteSubcard = function(subcardId) {
        window.spaceDecksManager.deleteSubcard(subcardId);
    };
});