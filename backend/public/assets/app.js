document.addEventListener('DOMContentLoaded', () => {
    wireCartCount();
    wirePlusButtons();
    wireCheckoutBits();
});

function getCart() {
    return JSON.parse(localStorage.getItem('cart') || '[]');
}

function setCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
    localStorage.setItem('cartCount', String(cart.length));
}

function wireCartCount() {
    const counter = document.getElementById('cart-count');
    if (!counter) return;

    const count = Number(localStorage.getItem('cartCount') || '0');
    counter.textContent = String(count);
}

function wirePlusButtons() {
    const buttons = document.querySelectorAll('.add-cart');
    if (buttons.length === 0) return;

    buttons.forEach((button, index) => {
        button.addEventListener('click', () => {
            const card = button.closest('.product-card');
            if (!card) return;

            const nameEl = card.querySelector('.product-name');
            const priceText = card.querySelector('.product-row span')?.textContent || '$0.00';
            const price = Number(priceText.replace('$', '')) || 0;
            const id = `product-${index + 1}`;

            const cart = getCart();
            const existing = cart.find((item) => item.id === id);

            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({
                    id,
                    name: nameEl ? nameEl.textContent : 'Product',
                    price,
                    quantity: 1,
                });
            }

            setCart(cart);
            wireCartCount();
            flashButton(button);
        });
    });
}

function flashButton(button) {
    const original = button.textContent;
    button.textContent = '✓';
    button.disabled = true;

    setTimeout(() => {
        button.textContent = original;
        button.disabled = false;
    }, 550);
}

function wireCheckoutBits() {
    const backBtn = document.getElementById('back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.location.href = 'cart.php';
        });
    }

    const completeBtn = document.getElementById('complete-btn');
    if (completeBtn) {
        completeBtn.addEventListener('click', () => {
            const form = document.getElementById('checkout-form');
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            completeBtn.textContent = 'Processing...';
            completeBtn.disabled = true;
            setTimeout(() => {
                localStorage.removeItem('cart');
                localStorage.setItem('cartCount', '0');
                window.location.href = 'home.php';
            }, 900);
        });
    }
}
