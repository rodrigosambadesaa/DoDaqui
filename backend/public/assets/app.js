// ============================================================
// DODAQUÍ APP - Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    initializeCart();
    initializeProductActions();
});

// ============================================================
// Cart Management
// ============================================================

function initializeCart() {
    updateCartCount();
}

function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        const count = localStorage.getItem('cartCount') || '0';
        cartCount.textContent = count;
    }
}

function addToCart(productId, productName, productPrice) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    localStorage.setItem('cartCount', String(cart.length));
    updateCartCount();
    
    // Show feedback
    showNotification(`${productName} added to cart!`, 'success');
}

function removeFromCart(productId) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    localStorage.setItem('cartCount', String(cart.length));
    updateCartCount();
}

// ============================================================
// Product Actions
// ============================================================

function initializeProductActions() {
    // Add to cart buttons
    const addCartButtons = document.querySelectorAll('.add-cart');
    addCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.product-card');
            const productName = card.querySelector('h3').textContent;
            const productPrice = parseFloat(card.querySelector('.product-price').textContent.replace('$', ''));
            const productId = Math.random().toString(36).substr(2, 9); // Generate simple ID
            
            addToCart(productId, productName, productPrice);
        });
    });

    // View buttons
    const viewButtons = document.querySelectorAll('.product-card .btn-light');
    viewButtons.forEach(button => {
        if (!button.closest('.product-card .btn-light[role="checkbox"]')) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const card = this.closest('.product-card');
                const productName = card.querySelector('h3').textContent;
                const productPrice = card.querySelector('.product-price').textContent;
                const productMeta = card.querySelector('.product-meta').textContent;
                
                showProductModal(productName, productPrice, productMeta);
            });
        }
    });
}

// ============================================================
// Notifications
// ============================================================

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${type === 'success' ? '#4caf50' : '#2196f3'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        font-weight: 500;
        font-size: 14px;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ============================================================
// Modal
// ============================================================

function showProductModal(name, price, meta) {
    const modal = document.createElement('div');
    modal.className = 'product-detail-modal';
    modal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <div class="modal-body">
                <div class="modal-image placeholder-img" style="height: 300px; margin-bottom: 24px;"></div>
                <h2>${name}</h2>
                <p style="color: var(--text-light); margin: 8px 0 16px;">${meta}</p>
                <p style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 24px;">${price}</p>
                <button class="btn btn-primary" style="width: 100%; margin-bottom: 12px;">Add to Cart</button>
                <button class="modal-close-btn btn btn-light" style="width: 100%;">Close</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    const overlay = modal.querySelector('.modal-overlay');
    const closeBtn = modal.querySelector('.modal-close');
    const closeBtnAlt = modal.querySelector('.modal-close-btn');
    
    function closeModal() {
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 300);
    }
    
    overlay.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);
    closeBtnAlt.addEventListener('click', closeModal);
}

// ============================================================
// Animations (CSS)
// ============================================================

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .product-detail-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        position: relative;
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: var(--text-light);
    }
    
    .modal-close:hover {
        color: var(--text-dark);
    }
`;
document.head.appendChild(style);
