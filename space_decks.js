// js/space_decks.js - نسخه بهبود یافته
class SpaceDecksManager {
    constructor() {
        this.currentDeckId = null;
        this.draggedElement = null;
        this.init();
    }

    init() {
        this.initializeDragAndDrop();
        this.initializeSortable();
        this.setupEventListeners();
        this.handleSuccessMessages();
        console.log('SpaceDecksManager initialized');
    }

    setupEventListeners() {
        // مدیریت کلیک بر روی دکمه‌های افزودن کارت
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-card-btn') || e.target.closest('.add-card-btn')) {
                const deckId = e.target.dataset.deckId || e.target.closest('[data-deck-id]').dataset.deckId;
                this.openAddCardModal(deckId);
            }
            
            if (e.target.classList.contains('edit-deck-btn') || e.target.closest('.edit-deck-btn')) {
                const deckId = e.target.dataset.deckId || e.target.closest('[data-deck-id]').dataset.deckId;
                this.openEditDeckModal(deckId);
            }
            
            if (e.target.classList.contains('delete-deck-btn') || e.target.closest('.delete-deck-btn')) {
                const deckId = e.target.dataset.deckId || e.target.closest('[data-deck-id]').dataset.deckId;
                this.deleteDeck(deckId);
            }
        });

        // مدیریت فرم‌ها
        this.setupAjaxForms();
        
        // کلیدهای میانبر
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const firstDeck = document.querySelector('.deck-column');
                if (firstDeck) {
                    const deckId = firstDeck.dataset.deckId;
                    this.openAddCardModal(deckId);
                }
            }
        });
    }

    setupAjaxForms() {
        // فرم ایجاد دک
        const createDeckForm = document.getElementById('createDeckForm');
        if (createDeckForm) {
            createDeckForm.addEventListener('submit', (e) => this.handleCreateDeck(e));
        }

        // فرم ویرایش دک
        const editDeckForm = document.getElementById('editDeckForm');
        if (editDeckForm) {
            editDeckForm.addEventListener('submit', (e) => this.handleEditDeck(e));
        }

        // فرم افزودن کارت
        const addCardForm = document.getElementById('addCardForm');
        if (addCardForm) {
            addCardForm.addEventListener('submit', (e) => this.handleAddCard(e));
        }
    }

    async handleCreateDeck(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        this.showLoading();
        try {
            const response = await fetch('create_deck.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message || 'Deck با موفقیت ایجاد شد', 'success');
                this.addDeckToUI(data.deck);
                this.hideModal('createDeckModal');
                e.target.reset();
            } else {
                this.showNotification(data.error || 'خطا در ایجاد Deck', 'error');
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
        
        this.showLoading();
        try {
            const response = await fetch('edit_deck.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Deck با موفقیت ویرایش شد', 'success');
                this.updateDeckInUI(data.deck);
                this.hideModal('editDeckModal');
            } else {
                this.showNotification(data.error || 'خطا در ویرایش Deck', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async handleAddCard(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        this.showLoading();
        try {
            const response = await fetch('add_card.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('کارت با موفقیت افزوده شد', 'success');
                this.addCardToUI(data.card);
                this.hideModal('addCardModal');
                e.target.reset();
            } else {
                this.showNotification(data.error || 'خطا در افزودن کارت', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        } finally {
            this.hideLoading();
        }
    }

    addDeckToUI(deck) {
        const decksContainer = document.getElementById('decks-container');
        const newDeckHTML = `
            <div class="deck-column" data-deck-id="${deck.id}">
                <div class="deck-header">
                    <div class="deck-title">${deck.name}</div>
                    <div class="deck-actions">
                        <button class="btn btn-sm edit-deck-btn" data-deck-id="${deck.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm delete-deck-btn" data-deck-id="${deck.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="deck-body" data-deck-id="${deck.id}">
                    <button class="btn btn-outline add-card-btn" data-deck-id="${deck.id}">
                        + افزودن کارت
                    </button>
                </div>
            </div>
        `;
        
        decksContainer.insertAdjacentHTML('beforeend', newDeckHTML);
        this.initializeSortable(); // مجدداً مقداردهی Sortable
    }

    updateDeckInUI(deck) {
        const deckElement = document.querySelector(`[data-deck-id="${deck.id}"]`);
        if (deckElement) {
            const titleElement = deckElement.querySelector('.deck-title');
            if (titleElement) {
                titleElement.textContent = deck.name;
            }
        }
    }

    addCardToUI(card) {
        const deckBody = document.querySelector(`.deck-body[data-deck-id="${card.deck_id}"]`);
        const addCardButton = deckBody.querySelector('.add-card-btn');
        
        const cardHTML = `
            <div class="card-item" data-card-id="${card.id}" draggable="true">
                <div class="card-title">${card.title}</div>
                <div class="card-meta">
                    <span class="priority">${card.priority || 'medium'}</span>
                    ${card.due_date ? `<span class="due-date">${card.due_date}</span>` : ''}
                </div>
            </div>
        `;
        
        if (addCardButton) {
            addCardButton.insertAdjacentHTML('beforebegin', cardHTML);
        } else {
            deckBody.insertAdjacentHTML('beforeend', cardHTML);
        }
        
        this.initializeDragAndDrop(); // مجدداً مقداردهی درگ و دراپ
    }

    initializeDragAndDrop() {
        const draggableCards = document.querySelectorAll('.card-item');
        const deckBodies = document.querySelectorAll('.deck-body');

        draggableCards.forEach(card => {
            card.addEventListener('dragstart', (e) => {
                this.draggedElement = card;
                this.currentDeckId = card.closest('.deck-column').dataset.deckId;
                e.dataTransfer.setData('text/plain', card.dataset.cardId);
                card.classList.add('dragging');
                
                setTimeout(() => {
                    card.style.display = 'none';
                }, 0);
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
                card.style.display = 'block';
                this.draggedElement = null;
                
                deckBodies.forEach(deck => {
                    deck.classList.remove('drag-over');
                });
            });
        });

        deckBodies.forEach(deck => {
            deck.addEventListener('dragover', (e) => {
                e.preventDefault();
                deck.classList.add('drag-over');
            });

            deck.addEventListener('dragleave', () => {
                deck.classList.remove('drag-over');
            });

            deck.addEventListener('drop', (e) => {
                e.preventDefault();
                deck.classList.remove('drag-over');
                
                const cardId = e.dataTransfer.getData('text/plain');
                const newDeckId = deck.dataset.deckId;
                
                if (this.currentDeckId !== newDeckId && this.draggedElement) {
                    deck.insertBefore(this.draggedElement, deck.querySelector('.add-card-btn'));
                    this.updateCardDeck(cardId, newDeckId);
                }
            });
        });
    }

    initializeSortable() {
        const decksContainer = document.getElementById('decks-container');
        if (!decksContainer) return;

        new Sortable(decksContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.deck-header',
            onEnd: (evt) => {
                const deckOrder = [];
                document.querySelectorAll('.deck-column[data-deck-id]').forEach((deck, index) => {
                    deckOrder.push({
                        deck_id: deck.dataset.deckId,
                        position: index
                    });
                });
                this.updateDeckOrder(deckOrder);
            }
        });

        // قابل مرتب‌سازی کارت‌ها درون دک‌ها
        document.querySelectorAll('.deck-body').forEach(deckBody => {
            new Sortable(deckBody, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                group: 'shared-cards',
                onEnd: (evt) => {
                    const deckId = evt.to.dataset.deckId;
                    this.updateCardOrder(deckId);
                }
            });
        });
    }

    async updateCardDeck(cardId, newDeckId) {
        try {
            const formData = new FormData();
            formData.append('card_id', cardId);
            formData.append('new_deck_id', newDeckId);
            formData.append('csrf_token', this.getCsrfToken());

            const response = await fetch('update_card_deck.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('کارت با موفقیت منتقل شد', 'success');
                this.addExperience(2);
            } else {
                this.showNotification(data.error || 'خطا در انتقال کارت', 'error');
                // بازگرداندن به موقعیت قبلی در صورت خطا
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
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    deck_order: deckOrder,
                    csrf_token: this.getCsrfToken()
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                this.showNotification('خطا در به‌روزرسانی ترتیب دک‌ها', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('خطا در ارتباط با سرور', 'error');
        }
    }

    async updateCardOrder(deckId) {
        try {
            const deckBody = document.querySelector(`.deck-body[data-deck-id="${deckId}"]`);
            const cardOrder = [];
            
            deckBody.querySelectorAll('.card-item').forEach((card, index) => {
                cardOrder.push({
                    card_id: card.dataset.cardId,
                    position: index
                });
            });

            const response = await fetch('update_card_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    card_order: cardOrder,
                    csrf_token: this.getCsrfToken()
                })
            });
            
            const data = await response.json();
            
            if (!data.success) {
                console.error('Error updating card order:', data.error);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // توابع کمکی
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    openAddCardModal(deckId) {
        document.getElementById('card_deck_id').value = deckId;
        const modal = new bootstrap.Modal(document.getElementById('addCardModal'));
        modal.show();
        document.getElementById('card_title').focus();
    }

    openEditDeckModal(deckId) {
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
                this.showNotification('خطا در ارتباط با سرور', 'error');
            });
    }

    hideModal(modalId) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
        if (modal) {
            modal.hide();
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        // پیاده‌سازی ساده نوتیفیکیشن
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            z-index: 10000;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, duration);
    }

    showLoading() {
        // پیاده‌سازی ساده loading
        console.log('Loading...');
    }

    hideLoading() {
        console.log('Loading hidden');
    }
}

// مقداردهی اولیه
document.addEventListener('DOMContentLoaded', function() {
    window.spaceDecksManager = new SpaceDecksManager();
});