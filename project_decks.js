// js/project_decks.js - نسخه کامل برای سیستم decks

// Global variables
let draggedCard = null;
let currentDeckId = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDeckSystem();
    initializeDragAndDrop();
    initializeSortable();
    showWelcomeNotification();
});

function initializeDeckSystem() {
    // Add animation to cards when they enter viewport
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

    document.querySelectorAll('.task-card').forEach(card => {
        observer.observe(card);
    });
}

function initializeDragAndDrop() {
    const draggableCards = document.querySelectorAll('.draggable-card');
    const deckBodies = document.querySelectorAll('.deck-body');

    draggableCards.forEach(card => {
        card.addEventListener('dragstart', function(e) {
            draggedCard = this;
            currentDeckId = this.closest('.deck-body').dataset.deckId;
            e.dataTransfer.setData('text/plain', this.dataset.cardId);
            this.classList.add('dragging');
            
            // Add visual feedback
            setTimeout(() => {
                this.style.opacity = '0.4';
            }, 0);
        });

        card.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            this.style.opacity = '1';
            draggedCard = null;
            currentDeckId = null;
            
            // Remove drag-over class from all decks
            deckBodies.forEach(deck => {
                deck.classList.remove('drag-over');
            });
        });
    });

    deckBodies.forEach(deck => {
        deck.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        deck.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        deck.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (draggedCard) {
                const cardId = draggedCard.dataset.cardId;
                const newDeckId = this.dataset.deckId;

                // If moving to different deck
                if (currentDeckId !== newDeckId) {
                    // Add animation for card movement
                    draggedCard.style.transition = 'all 0.3s ease';
                    
                    // Move the card to the new deck in the UI
                    this.appendChild(draggedCard);
                    
                    // Send AJAX request to update the card's deck
                    updateCardDeck(cardId, newDeckId);
                }
            }
        });
    });
}

function initializeSortable() {
    // Make decks sortable horizontally
    const decksContainer = document.getElementById('decks-container');
    
    if (decksContainer) {
        new Sortable(decksContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.deck-header',
            onEnd: function(evt) {
                const deckOrder = [];
                document.querySelectorAll('[data-deck-id]').forEach((deck, index) => {
                    deckOrder.push({
                        deck_id: deck.dataset.deckId,
                        position: index + 1
                    });
                });
                
                updateDeckOrder(deckOrder);
            }
        });
    }

    // Make cards sortable within decks
    document.querySelectorAll('.deck-body').forEach(deckBody => {
        new Sortable(deckBody, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            group: 'shared-cards',
            onEnd: function(evt) {
                const cardId = evt.item.dataset.cardId;
                const newDeckId = evt.to.dataset.deckId;
                const oldDeckId = evt.from.dataset.deckId;
                
                if (oldDeckId !== newDeckId) {
                    // Card moved to different deck
                    updateCardDeck(cardId, newDeckId);
                } else {
                    // Card reordered within same deck
                    updateCardOrder(newDeckId);
                }
            }
        });
    });
}

// Modal Functions
function openAddCardModal(deckId) {
    document.getElementById('card_deck_id').value = deckId;
    const addCardModal = new bootstrap.Modal(document.getElementById('addCardModal'));
    addCardModal.show();
    
    // Focus on title input
    setTimeout(() => {
        document.getElementById('card_title').focus();
    }, 500);
}

function openEditCardModal(cardId) {
    showLoading();
    fetch('get_card.php?id=' + cardId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editCardModalBody').innerHTML = html;
            document.getElementById('edit_card_id').value = cardId;
            const editModal = new bootstrap.Modal(document.getElementById('editCardModal'));
            editModal.show();
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('خطا در بارگذاری اطلاعات کارت', 'error');
            hideLoading();
        });
}

function openEditDeckModal(deckId, deckName) {
    document.getElementById('edit_deck_id').value = deckId;
    document.getElementById('edit_deck_name').value = deckName;
    const editDeckModal = new bootstrap.Modal(document.getElementById('editDeckModal'));
    editDeckModal.show();
}

// API Functions
function updateCardDeck(cardId, newDeckId) {
    const formData = new FormData();
    formData.append('card_id', cardId);
    formData.append('new_deck_id', newDeckId);

    fetch('update_card_deck.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('کارت با موفقیت منتقل شد', 'success');
            addExperience(2);
            
            // Update deck counts
            updateDeckCounts();
        } else {
            showNotification('خطا در انتقال کارت', 'error');
            // Reload to sync with server state
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('خطا در ارتباط با سرور', 'error');
        setTimeout(() => location.reload(), 1000);
    });
}

function updateDeckOrder(deckOrder) {
    fetch('update_deck_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ deck_order: deckOrder })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            showNotification('خطا در به‌روزرسانی ترتیب دک‌ها', 'error');
        }
    });
}

function updateCardOrder(deckId) {
    const cardOrder = [];
    const deckBody = document.querySelector(`.deck-body[data-deck-id="${deckId}"]`);
    
    if (deckBody) {
        deckBody.querySelectorAll('.draggable-card').forEach((card, index) => {
            cardOrder.push({
                card_id: card.dataset.cardId,
                position: index + 1
            });
        });
        
        fetch('update_card_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ card_order: cardOrder })
        });
    }
}

function deleteCard(cardId) {
    if (confirm('آیا از حذف این کارت اطمینان دارید؟ این عمل غیرقابل بازگشت است.')) {
        showLoading();
        const formData = new FormData();
        formData.append('card_id', cardId);
        formData.append('project_id', document.querySelector('input[name="project_id"]')?.value || '');

        fetch('delete_card.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('کارت با موفقیت حذف شد', 'success');
                // Remove card from UI with animation
                const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
                if (cardElement) {
                    cardElement.style.transition = 'all 0.3s ease';
                    cardElement.style.opacity = '0';
                    cardElement.style.height = '0';
                    cardElement.style.margin = '0';
                    
                    setTimeout(() => {
                        cardElement.remove();
                        updateDeckCounts();
                    }, 300);
                }
            } else {
                showNotification('خطا در حذف کارت: ' + (data.error || ''), 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('خطا در ارتباط با سرور', 'error');
        });
    }
}

function deleteDeck(deckId) {
    if (confirm('آیا از حذف این دک اطمینان دارید؟ تمام کارت‌های داخل آن نیز حذف خواهند شد.')) {
        showLoading();
        const formData = new FormData();
        formData.append('deck_id', deckId);
        formData.append('project_id', document.querySelector('input[name="project_id"]')?.value || '');

        fetch('delete_deck.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('دک با موفقیت حذف شد', 'success');
                // Remove deck from UI with animation
                const deckElement = document.querySelector(`[data-deck-id="${deckId}"]`);
                if (deckElement) {
                    deckElement.style.transition = 'all 0.3s ease';
                    deckElement.style.opacity = '0';
                    deckElement.style.height = '0';
                    
                    setTimeout(() => {
                        deckElement.remove();
                    }, 300);
                }
            } else {
                showNotification('خطا در حذف دک: ' + (data.error || ''), 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('خطا در ارتباط با سرور', 'error');
        });
    }
}

// Utility Functions
function updateDeckCounts() {
    // This would update the card counts in deck headers
    // For now, we'll reload the counts after a short delay
    setTimeout(() => {
        document.querySelectorAll('.deck-card').forEach(deck => {
            const deckBody = deck.querySelector('.deck-body');
            const countBadge = deck.querySelector('.deck-count');
            if (deckBody && countBadge) {
                const cardCount = deckBody.querySelectorAll('.draggable-card').length;
                countBadge.textContent = cardCount;
            }
        });
    }, 100);
}

function addExperience(points) {
    fetch('add_experience.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ points: points })
    })
    .then(response => response.json())
    .then(data => {
        if (data.level_up) {
            showLevelUpNotification(data.new_level);
            updateUserStats(data.new_level, data.new_experience);
        }
    });
}

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

function updateUserStats(level, experience) {
    const levelElements = document.querySelectorAll('.user-level');
    levelElements.forEach(el => {
        el.textContent = level;
    });
}

function showWelcomeNotification() {
    // Show welcome message for new users
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('new_user')) {
        setTimeout(() => {
            showNotification('به ProDecks خوش آمدید! 🎮 کارت‌ها را بین دک‌ها بکشید و رها کنید.', 'success', 8000);
        }, 1000);
    }
}

function showLoading() {
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

function hideLoading() {
    const loading = document.getElementById('global-loading');
    if (loading) {
        loading.remove();
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + N for new card
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        const firstDeck = document.querySelector('.deck-card');
        if (firstDeck) {
            const deckId = firstDeck.dataset.deckId;
            openAddCardModal(deckId);
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            modal.hide();
        }
    }
});

// Notification system
function showNotification(message, type = 'info', duration = 5000) {
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
    
    const icon = getNotificationIcon(type);
    
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

function getNotificationIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
}