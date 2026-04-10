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

    const form = document.getElementById('checkout-form');
    const paymentRadios = document.querySelectorAll('input[name="metodo_pagamento"]');
    const cardFields = document.getElementById('card-fields');
    const paypalFields = document.getElementById('paypal-fields');

    function syncPaymentUI() {
        if (!form || paymentRadios.length === 0) return;

        const selected = form.querySelector('input[name="metodo_pagamento"]:checked')?.value;
        const cardInputs = cardFields ? cardFields.querySelectorAll('input') : [];
        const paypalInputs = paypalFields ? paypalFields.querySelectorAll('input') : [];

        const isCard = selected !== 'paypal';

        if (cardFields) {
            cardFields.hidden = !isCard;
            cardInputs.forEach((input) => {
                input.disabled = !isCard;
                input.required = isCard;
            });
        }

        if (paypalFields) {
            paypalFields.hidden = isCard;
            paypalInputs.forEach((input) => {
                input.disabled = isCard;
                input.required = !isCard;
            });
        }
    }

    if (paymentRadios.length > 0) {
        paymentRadios.forEach((radio) => radio.addEventListener('change', syncPaymentUI));
        syncPaymentUI();
    }

    const billingEditBtn = document.getElementById('billing-edit-btn');
    const billingFields = document.getElementById('billing-fields');
    if (billingEditBtn && billingFields) {
        billingEditBtn.addEventListener('click', () => {
            const hidden = billingFields.hasAttribute('hidden');
            if (hidden) {
                billingFields.removeAttribute('hidden');
                billingEditBtn.textContent = 'Ocultar';
            } else {
                billingFields.setAttribute('hidden', 'hidden');
                billingEditBtn.textContent = 'Editar';
            }
        });
    }

    const completeBtn = document.getElementById('complete-btn');
    if (completeBtn) {
        completeBtn.addEventListener('click', async () => {
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const payload = new URLSearchParams();
            if (form) {
                const data = new FormData(form);
                for (const [key, value] of data.entries()) {
                    payload.append(key, String(value));
                }
            }

            payload.set('action', 'realizar_pedido');

            completeBtn.textContent = 'Procesando...';
            completeBtn.disabled = true;

            try {
                const response = await fetch('checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        Accept: 'application/json',
                    },
                    body: payload.toString(),
                });

                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo completar la compra.');
                }

                alert(`Pedido #${data.id_pedido} confirmado. Total: ${data.total}`);
                window.location.href = 'home.php';
            } catch (error) {
                alert(error instanceof Error ? error.message : 'No se pudo completar la compra.');
                completeBtn.textContent = 'Completar compra';
                completeBtn.disabled = false;
            }
        });
    }
}
