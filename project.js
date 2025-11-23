// js/project.js - نسخه کامل

function openAddCardModal(deckId) {
    document.getElementById('card_deck_id').value = deckId;
    var addCardModal = new bootstrap.Modal(document.getElementById('addCardModal'));
    addCardModal.show();
}

function openEditCardModal(cardId) {
    fetch('get_card.php?id=' + cardId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editCardModalBody').innerHTML = html;
            document.getElementById('edit_card_id').value = cardId;
            var editModal = new bootstrap.Modal(document.getElementById('editCardModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('خطا در بارگذاری اطلاعات کارت', 'error');
        });
}

function deleteCard(cardId) {
    if (confirm('آیا از حذف این کارت اطمینان دارید؟ این عمل غیرقابل بازگشت است.')) {
        const formData = new FormData();
        formData.append('card_id', cardId);
        formData.append('project_id', document.querySelector('input[name="project_id"]')?.value || '');

        fetch('delete_card.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('کارت با موفقیت حذف شد', 'success');
                // Remove card from UI
                const cardElement = document.querySelector(`[data-card-id="${cardId}"]`);
                if (cardElement) {
                    cardElement.remove();
                }
                // Reload page after a delay to update counts
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification('خطا در حذف کارت: ' + (data.error || ''), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('خطا در ارتباط با سرور', 'error');
        });
    }
}

// Drag and Drop functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    initializeSortable();
    showFlashMessages();
});

function initializeDragAndDrop() {
    const draggableCards = document.querySelectorAll('.draggable-card');
    const deckBodies = document.querySelectorAll('.deck-body');

    let draggedCard = null;

    draggableCards.forEach(card => {
        card.addEventListener('dragstart', function(e) {
            draggedCard = this;
            e.dataTransfer.setData('text/plain', this.dataset.cardId);
            this.classList.add('dragging');
            this.style.opacity = '0.4';
        });

        card.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            this.style.opacity = '1';
            draggedCard = null;
        });
    });

    deckBodies.forEach(deck => {
        deck.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
            this.style.backgroundColor = '#e2e8f0';
        });

        deck.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
            this.style.backgroundColor = '';
        });

        deck.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            this.style.backgroundColor = '';

            if (draggedCard) {
                const cardId = draggedCard.dataset.cardId;
                const newDeckId = this.dataset.deckId;

                // Move the card to the new deck in the UI
                this.appendChild(draggedCard);

                // Send AJAX request to update the card's deck
                updateCardDeck(cardId, newDeckId);
            }
        });
    });
}

function initializeSortable() {
    const decksContainer = document.getElementById('decks-container');
    
    if (decksContainer) {
        new Sortable(decksContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            handle: '.deck-card .card-header',
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
            group: 'shared',
            onEnd: function(evt) {
                const cardId = evt.item.dataset.cardId;
                const newDeckId = evt.to.dataset.deckId;
                
                if (evt.from !== evt.to) {
                    // Card moved to different deck
                    updateCardDeck(cardId, newDeckId);
                } else {
                    // Card reordered within same deck
                    updateCardOrder(evt.to.dataset.deckId);
                }
            }
        });
    });
}

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
        } else {
            showNotification('خطا در انتقال کارت', 'error');
            // Optionally revert the UI change
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('خطا در ارتباط با سرور', 'error');
        location.reload();
    });
}

function updateDeckOrder(deckOrder) {
    fetch('update_deck_order.php', {
        method: 'POST',
        body: JSON.stringify({ deck_order: deckOrder }),
        headers: {
            'Content-Type': 'application/json'
        }
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
            body: JSON.stringify({ card_order: cardOrder }),
            headers: {
                'Content-Type': 'application/json'
            }
        });
    }
}

function addExperience(points) {
    fetch('add_experience.php', {
        method: 'POST',
        body: JSON.stringify({ points: points }),
        headers: {
            'Content-Type': 'application/json'
        }
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
    
    const expElements = document.querySelectorAll('.user-experience');
    expElements.forEach(el => {
        el.textContent = experience;
    });
}

function showFlashMessages() {
    // Show success/error messages from session
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        showNotification('عملیات با موفقیت انجام شد', 'success');
    }
    if (urlParams.get('error')) {
        showNotification('خطا در انجام عملیات', 'error');
    }
}

// Notification system
function showNotification(message, type = 'info') {
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
    }, 5000);
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