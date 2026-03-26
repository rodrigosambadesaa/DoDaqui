// Configuración de API compartida entre home y checkout.
const API_BASE = window.APP_CONFIG?.API_BASE_URL || 'http://localhost:8080/api';

// Estado exclusivo del checkout: usuario autenticado, método de pago y datos de carrito.
const state = {
    user: null,
    paymentMethod: 'card',
    cart: { items: [], total: 0 },
};

// Clave de sesión unificada para persistencia entre recargas y entre páginas.
const SESSION_STORAGE_KEY = 'dodaqui_user_session';

// Atajo por id para mantener el código legible.
const $ = (id) => document.getElementById(id);

// Muestra la URL de API efectiva para depuración del entorno.
$('api-url').textContent = API_BASE;

// Nodo raíz de notificaciones tipo toast.
const feedbackRoot = document.createElement('div');
feedbackRoot.className = 'feedback-root';
document.body.appendChild(feedbackRoot);

// Utilidad para mostrar importes con formato uniforme.
function formatEUR(value) {
    return `${Number(value).toFixed(2)} EUR`;
}

// Utilidad para mostrar cantidades admitiendo enteros y decimales.
function formatQuantity(value) {
    return Number(value).toString();
}

function updateConfirmOrderState() {
    const confirmButton = $('mobile-confirm-order');
    if (!confirmButton) {
        return;
    }

    const isEmpty = state.cart.items.length === 0;
    confirmButton.disabled = isEmpty;
    confirmButton.title = isEmpty ? 'Añade productos al carrito para poder confirmar.' : 'Confirmar pedido';
}

// Toast reutilizable para confirmaciones, errores y avisos informativos.
function showMessage(type, text, timeout = 3200) {
    const toast = document.createElement('div');
    toast.className = `feedback feedback-${type}`;
    toast.textContent = text;
    feedbackRoot.appendChild(toast);

    const dismiss = () => {
        toast.classList.add('feedback-hide');
        setTimeout(() => toast.remove(), 220);
    };

    setTimeout(dismiss, timeout);
    toast.addEventListener('click', dismiss);
}

// Carga de sesión desde localStorage con tolerancia a errores de parseo.
function loadSession() {
    const raw = localStorage.getItem(SESSION_STORAGE_KEY);
    if (!raw) {
        return;
    }

    try {
        const parsed = JSON.parse(raw);
        if (parsed && parsed.id_usuario) {
            state.user = parsed;
        }
    } catch (_error) {
        localStorage.removeItem(SESSION_STORAGE_KEY);
    }
}

// Render del nombre de usuario en cabecera de checkout.
function renderUser() {
    const logoutButton = $('logout-button');

    if (!state.user) {
        $('signin-state').textContent = 'Iniciar sesión';
        logoutButton?.classList.add('hidden');
        return;
    }

    $('signin-state').textContent = state.user.nome;
    logoutButton?.classList.remove('hidden');
}

// Cliente HTTP para la API con normalización de errores.
async function api(path, options = {}) {
    const response = await fetch(`${API_BASE}${path}`, {
        headers: { 'Content-Type': 'application/json' },
        ...options,
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.error || 'Error en la API');
    }

    return data;
}

// Conmuta UI de método de pago y muestra solo campos relevantes.
function setPaymentMethod(method) {
    state.paymentMethod = method;

    document.querySelectorAll('.pay-option').forEach((el) => {
        const isActive = el.dataset.method === method;
        el.classList.toggle('active', isActive);
    });

    $('card-fields').classList.toggle('payment-hidden', method !== 'card');
    $('pix-fields').classList.toggle('payment-hidden', method !== 'pix');
    $('boleto-fields').classList.toggle('payment-hidden', method !== 'boleto');
}

// Valida datos mínimos según método de pago elegido.
function validatePayment() {
    if (state.paymentMethod === 'card') {
        const holder = $('cardholder-name').value.trim();
        const number = $('card-number').value.replace(/\s+/g, '');
        const expiry = $('card-expiry').value.trim();
        const cvv = $('card-cvv').value.trim();

        if (holder.length < 3) {
            return { ok: false, message: 'Introduce el titular de la tarjeta.' };
        }
        if (!/^\d{16}$/.test(number)) {
            return { ok: false, message: 'El número de tarjeta debe tener 16 dígitos.' };
        }
        if (!/^(0[1-9]|1[0-2])\/(\d{2})$/.test(expiry)) {
            return { ok: false, message: 'La caducidad debe tener formato MM/AA.' };
        }
        if (!/^\d{3,4}$/.test(cvv)) {
            return { ok: false, message: 'El CVV debe tener 3 o 4 dígitos.' };
        }

        return {
            ok: true,
            payload: {
                method: 'card',
                holder,
                cardNumber: number,
                expiry,
            },
        };
    }

    if (state.paymentMethod === 'pix') {
        const email = $('pix-email').value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            return { ok: false, message: 'Introduce un email válido para Pix.' };
        }

        return {
            ok: true,
            payload: { method: 'pix', email },
        };
    }

    if (state.paymentMethod === 'boleto') {
        const id = $('boleto-id').value.trim();
        if (id.length < 6) {
            return { ok: false, message: 'Introduce un DNI/NIF válido para boleto.' };
        }

        return {
            ok: true,
            payload: { method: 'boleto', id },
        };
    }

    return { ok: false, message: 'Selecciona un método de pago válido.' };
}

// Simulación de pasarela de pago para entorno de pruebas docentes.
async function processTestGateway(paymentPayload, amount) {
    await new Promise((resolve) => setTimeout(resolve, 900));

    if (amount <= 0) {
        return { ok: false, message: 'El carrito está vacío.' };
    }

    if (paymentPayload.method === 'card' && paymentPayload.cardNumber === '4000000000000002') {
        return { ok: false, message: 'Pasarela de prueba: transacción rechazada.' };
    }

    const reference = `TST-${Date.now()}`;
    return { ok: true, reference };
}

// Render completo del carrito en la página de checkout.
function renderCart() {
    const list = $('mobile-cart-items');
    list.innerHTML = '';

    if (state.cart.items.length === 0) {
        const li = document.createElement('li');
        li.textContent = 'El carrito está vacío.';
        list.appendChild(li);
    }

    state.cart.items.forEach((item) => {
        const li = document.createElement('li');
        li.textContent = `${item.nome} x${formatQuantity(item.cantidade)} - ${formatEUR(item.subtotal)}`;
        list.appendChild(li);
    });

    $('mobile-cart-count').textContent = String(state.cart.items.length);
    $('mobile-subtotal').textContent = formatEUR(state.cart.total);
    $('mobile-total').textContent = formatEUR(state.cart.total);
    updateConfirmOrderState();
}

// Carga datos del carrito del usuario autenticado.
async function refreshCart() {
    if (!state.user) {
        state.cart = { items: [], total: 0 };
        renderCart();
        return;
    }

    try {
        const data = await api(`/cart?user_id=${state.user.id_usuario}`);
        state.cart = {
            items: data.items || [],
            total: Number(data.total || 0),
        };
        renderCart();
    } catch (error) {
        showMessage('error', error.message);
    }
}

// Manejador principal de confirmación: valida pago, simula gateway y crea pedido.
$('mobile-confirm-order').addEventListener('click', async () => {
    if (!state.user) {
        showMessage('info', 'Inicia sesión en la portada antes de confirmar el pago.');
        return;
    }

    if (state.cart.items.length === 0 || state.cart.total <= 0) {
        showMessage('info', 'Tu carrito está vacío. Añade productos antes de confirmar.');
        return;
    }

    const paymentValidation = validatePayment();
    if (!paymentValidation.ok) {
        showMessage('error', paymentValidation.message);
        return;
    }

    const gatewayResult = await processTestGateway(paymentValidation.payload, state.cart.total);
    if (!gatewayResult.ok) {
        showMessage('error', gatewayResult.message);
        return;
    }

    try {
        const data = await api('/orders', {
            method: 'POST',
            body: JSON.stringify({ user_id: state.user.id_usuario }),
        });

        showMessage('success', `Pago OK (${gatewayResult.reference}) y pedido creado: ${data.codigo_pedido}`);
        await refreshCart();
    } catch (error) {
        showMessage('error', error.message);
    }
});

// Cambio de método de pago desde radio buttons.
document.querySelectorAll('input[name="payment-method"]').forEach((input) => {
    input.addEventListener('change', () => {
        setPaymentMethod(input.value);
    });
});

// Acción de retorno a la portada para seguir comprando.
$('go-home').addEventListener('click', () => {
    window.location.href = 'index.html';
});

$('logout-button').addEventListener('click', async () => {
    localStorage.removeItem(SESSION_STORAGE_KEY);
    state.user = null;
    renderUser();
    await refreshCart();
    showMessage('info', 'Sesión cerrada correctamente.');
});

// Arranque del checkout: sesión, UI y recarga inicial de carrito.
loadSession();
renderUser();
setPaymentMethod(state.paymentMethod);
refreshCart();
