document.addEventListener('DOMContentLoaded', () => {
    wireCartCount();
    wirePlusButtons();
    wireProductInlineDetails();
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
            const cardLike = card || button.closest('.box');
            if (!cardLike) return;

            const nameEl = cardLike.querySelector('.product-name');
            const rawPrice = button.dataset.price || cardLike.dataset.price || '0';
            const price = Number(rawPrice) || 0;
            const id = button.dataset.id || cardLike.dataset.id || `product-${index + 1}`;
            const name = button.dataset.name || (nameEl ? nameEl.textContent : 'Producto');

            fetch('cart_api.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id,
                    name,
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

function flashButton(button) {
    const original = button.textContent;
    button.textContent = '✓';
    button.disabled = true;

    setTimeout(() => {
        button.textContent = original;
        button.disabled = false;
    }, 550);
}

function wireProductInlineDetails() {
    const cards = document.querySelectorAll('.product-card');
    if (cards.length === 0) return;

    cards.forEach((card) => {
        const trigger = card.querySelector('.view-product');
        const detail = card.querySelector('.product-detail-inline');
        if (!trigger || !detail) return;

        trigger.addEventListener('click', () => {
            const isOpen = !detail.hasAttribute('hidden');

            cards.forEach((candidate) => {
                const section = candidate.querySelector('.product-detail-inline');
                const button = candidate.querySelector('.view-product');
                if (!section || !button) return;
                section.setAttribute('hidden', 'hidden');
                candidate.classList.remove('is-expanded');
                button.textContent = 'Ver detalle';
            });

            if (!isOpen) {
                detail.removeAttribute('hidden');
                card.classList.add('is-expanded');
                trigger.textContent = 'Ocultar detalle';
            }
        });
    });
}

function wireCheckoutBits() {
    const qtyButtons = document.querySelectorAll('.qty-btn');
    if (qtyButtons.length > 0) {
        qtyButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                const row = button.closest('tr[data-product-id]');
                if (!row) return;

                const productId = row.getAttribute('data-product-id');
                const action = button.getAttribute('data-action');
                const delta = action === 'plus' ? 1 : -1;

                button.disabled = true;
                try {
                    const response = await fetch('cart_api.php?action=update', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: productId, delta }),
                    });

                    const data = await response.json();
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'No se pudo actualizar el carrito.');
                    }

                    const counter = document.getElementById('cart-count');
                    if (counter) {
                        counter.textContent = String(data.count ?? 0);
                    }

                    if ((data.quantity ?? 0) <= 0) {
                        window.location.reload();
                        return;
                    }

                    const qtyValue = row.querySelector('.qty-value');
                    if (qtyValue) {
                        qtyValue.textContent = String(data.quantity);
                    }

                    window.location.reload();
                } catch (error) {
                    alert(error instanceof Error ? error.message : 'No se pudo actualizar el carrito.');
                } finally {
                    button.disabled = false;
                }
            });
        });
    }

    const form = document.getElementById('shipping-form');
    const completeBtn = document.getElementById('complete-btn');

    if (completeBtn && form) {
        completeBtn.addEventListener('click', async () => {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const phoneInput = document.getElementById('telefono_cliente');
            if (phoneInput instanceof HTMLInputElement) {
                const normalizedPhone = normalizePhone(phoneInput.value);
                if (normalizedPhone.length < 9) {
                    phoneInput.setCustomValidity('Introduce un teléfono válido.');
                    form.reportValidity();
                    phoneInput.focus();
                    return;
                }

                phoneInput.setCustomValidity('');
                phoneInput.value = normalizedPhone;
            }

            const payload = new URLSearchParams();
            const data = new FormData(form);
            for (const [key, value] of data.entries()) {
                payload.append(key, String(value));
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

                const result = await response.json();
                if (!response.ok || !result.ok) {
                    throw new Error(result.message || 'No se pudo completar la compra.');
                }

                alert(`Pedido #${result.id_pedido} confirmado. Total: ${result.total}`);
                window.location.href = 'home.php';
            } catch (error) {
                alert(error instanceof Error ? error.message : 'No se pudo completar la compra.');
                completeBtn.textContent = 'Completar compra';
                completeBtn.disabled = false;
            }
        });
    }
}

function normalizePhone(value) {
    return String(value)
        .trim()
        .replace(/[^\d+\s()-]/g, '')
        .replace(/\s+/g, ' ');
}
