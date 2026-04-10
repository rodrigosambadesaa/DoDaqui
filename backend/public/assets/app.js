document.addEventListener('DOMContentLoaded', () => {
    wireCartCount();
    wirePlusButtons();
    wireProductInlineDetails();
    wireViewAll();
    wireCheckoutBits();
});

async function wireCartCount() {
    const counter = document.getElementById('cart-count');
    if (!counter) return;

    try {
        const response = await fetch('cart_api.php?action=count');
        const data = await response.json();
        counter.textContent = String(data.count ?? 0);
    } catch (error) {
        counter.textContent = '0';
    }
}

function wirePlusButtons() {
    const buttons = document.querySelectorAll('.add-cart');
    if (buttons.length === 0) return;

    buttons.forEach((button, index) => {
        button.addEventListener('click', () => {
            const card = button.closest('.product-card');
            if (!card) return;

            const nameEl = card.querySelector('.product-name');
            const rawPrice = card.dataset.price || '0';
            const price = Number(rawPrice) || 0;
            const id = `product-${index + 1}`;

            fetch('cart_api.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id,
                    name: nameEl ? nameEl.textContent : 'Producto',
                    price,
                }),
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.ok) {
                        wireCartCount();
                        flashButton(button);
                    }
                })
                .catch(() => {
                    flashButton(button);
                });
        });
    });
}

function wireProductInlineDetails() {
    const cards = document.querySelectorAll('.product-card');
    if (!cards.length) return;

    cards.forEach((card) => {
        const trigger = card.querySelector('.view-product');
        const detail = card.querySelector('.product-detail-inline');
        if (!trigger || !detail) return;

        trigger.addEventListener('click', () => {
            const isOpen = !detail.hasAttribute('hidden');

            cards.forEach((candidate) => {
                const section = candidate.querySelector('.product-detail-inline');
                if (!section) return;
                section.setAttribute('hidden', 'hidden');
                candidate.classList.remove('is-expanded');
            });

            if (!isOpen) {
                detail.removeAttribute('hidden');
                card.classList.add('is-expanded');
            }
        });
    });
}

function wireViewAll() {
    const link = document.getElementById('ver-todo-productos');
    if (!link) return;

    // Si enlaza a otra página, se deja navegación normal.
    if (!link.getAttribute('href') || !link.getAttribute('href').startsWith('#')) {
        return;
    }

    const section = document.querySelector(link.getAttribute('href'));
    if (!section) return;

    link.addEventListener('click', (event) => {
        event.preventDefault();
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
                fetch('cart_api.php?action=clear', { method: 'POST' })
                    .finally(() => {
                        window.location.href = 'home.php';
                    });
            }, 900);
        });
    }
}
