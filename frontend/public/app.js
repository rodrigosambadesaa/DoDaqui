// URL base de la API: llega inyectada desde /config.js y cae a localhost para entorno local.
const API_BASE = window.APP_CONFIG?.API_BASE_URL || 'http://localhost:8080/api';

// Estado reactivo mínimo del home: usuario en sesión, catálogo cargado y resumen del carrito.
const state = {
    user: null,
    products: [],
    searchTerm: '',
    cart: { items: [], total: 0 },
    orders: [],
};

// Clave compartida con checkout.js para persistir sesión entre páginas.
const SESSION_STORAGE_KEY = 'dodaqui_user_session';

// Banco de imágenes por producto (fallback si un slug no tiene galería dedicada).
const PRODUCT_IMAGE_BANK = {
    'tomate-eco': [
        'https://images.pexels.com/photos/1327838/pexels-photo-1327838.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/533280/pexels-photo-533280.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/2893635/pexels-photo-2893635.jpeg?auto=compress&cs=tinysrgb&w=900',
    ],
    'queixo-artesan': [
        'https://images.pexels.com/photos/773253/pexels-photo-773253.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/248412/pexels-photo-248412.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/1435735/pexels-photo-1435735.jpeg?auto=compress&cs=tinysrgb&w=900',
    ],
    'mexillon-escabeche': [
        'https://images.pexels.com/photos/566345/pexels-photo-566345.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/3296279/pexels-photo-3296279.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/1552630/pexels-photo-1552630.jpeg?auto=compress&cs=tinysrgb&w=900',
    ],
    fallback: [
        'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/1435907/pexels-photo-1435907.jpeg?auto=compress&cs=tinysrgb&w=900',
        'https://images.pexels.com/photos/264537/pexels-photo-264537.jpeg?auto=compress&cs=tinysrgb&w=900',
    ],
};

// Atajo para seleccionar por id y helpers de nodos opcionales.
const $ = (id) => document.getElementById(id);
const apiUrlLabel = $('api-url');

if (apiUrlLabel) {
    apiUrlLabel.textContent = API_BASE;
}

// Contenedor global de toasts reutilizable para feedback no bloqueante.
const feedbackRoot = document.createElement('div');
feedbackRoot.className = 'feedback-root';
document.body.appendChild(feedbackRoot);

// Formateo uniforme de importes para toda la UI.
function formatEUR(value) {
    return `${Number(value).toFixed(2)} EUR`;
}

// Formateo neutro de cantidades (admite enteros y decimales).
function formatQuantity(value) {
    return Number(value).toString();
}

// Normalización de texto para búsqueda tolerante a mayúsculas y acentos.
function normalizeText(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

// Componente toast con autocierre y cierre manual por click.
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

// Persistencia de sesión para mantener login al recargar o cambiar de página.
function saveSession() {
    if (state.user) {
        localStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(state.user));
    } else {
        localStorage.removeItem(SESSION_STORAGE_KEY);
    }
}

// Carga segura de sesión: si el JSON está corrupto se limpia la clave para evitar bucles.
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

// Pinta cabecera y panel de estado según haya usuario autenticado o no.
function renderUser() {
    const registerForm = $('register-form');
    const loginForm = $('login-form');
    const recoverForm = $('recover-form');
    const logoutButton = $('logout-button');
    const userDropdown = $('user-dropdown');

    if (!state.user) {
        $('user-info').textContent = 'Sin usuario autenticado.';
        $('signin-state').textContent = 'Iniciar sesión';

        registerForm?.classList.remove('hidden');
        loginForm?.classList.remove('hidden');
        recoverForm?.classList.remove('hidden');
        userDropdown?.classList.add('hidden');
        logoutButton?.classList.add('hidden');
        return;
    }

    $('user-info').textContent = `Usuario: ${state.user.nome} (#${state.user.id_usuario})`;
    $('signin-state').textContent = state.user.nome;

    registerForm?.classList.add('hidden');
    loginForm?.classList.add('hidden');
    recoverForm?.classList.add('hidden');
    userDropdown?.classList.remove('hidden');
    logoutButton?.classList.remove('hidden');
}

// Guardia de autenticación para acciones que modifican carrito.
function withAuth() {
    if (!state.user) {
        showMessage('info', 'Inicia sesión primero para gestionar el carrito.');
        return false;
    }
    return true;
}

// Cliente HTTP genérico contra la API REST.
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

// Render del catálogo con búsqueda local, carrusel y selector de cantidades.
function renderProducts() {
    const container = $('featured-products');
    container.innerHTML = '';

    const term = normalizeText(state.searchTerm);
    const filtered = state.products.filter((item) => {
        if (!term) {
            return true;
        }

        const searchable = [
            item.nome,
            item.produtor,
            item.categoria,
            item.descricion_curta,
        ]
            .map(normalizeText)
            .join(' ');

        return searchable.includes(term);
    });

    if (filtered.length === 0) {
        const emptyState = document.createElement('p');
        emptyState.className = 'empty-products';
        emptyState.textContent = 'No hay productos que coincidan con la búsqueda.';
        container.appendChild(emptyState);
        return;
    }

    filtered.forEach((item) => {
        // Si tiene prezo_kg se trata como producto por peso y la cantidad es decimal.
        const images = PRODUCT_IMAGE_BANK[item.slug] || PRODUCT_IMAGE_BANK.fallback;
        const isWeightBased = item.prezo_kg !== null && item.prezo_kg !== undefined;
        const qtyStep = isWeightBased ? 0.1 : 1;
        const qtyMin = isWeightBased ? 0.1 : 1;
        const qtyMax = Math.max(qtyMin, Number(item.stock) || qtyMin);
        const qtyDefault = isWeightBased ? 0.5 : 1;

        const card = document.createElement('article');
        card.className = 'product-card';

        const pricePerKg = item.prezo_kg ? `<p class="price-per-kg">${formatEUR(item.prezo_kg)} / kg</p>` : '';

        card.innerHTML = `
            <div class="product-media">
                <img class="product-image" src="${images[0]}" alt="${item.nome}">
                <button class="media-nav media-prev" title="Imagen anterior" aria-label="Imagen anterior">&#10094;</button>
                <button class="media-nav media-next" title="Imagen siguiente" aria-label="Imagen siguiente">&#10095;</button>
                <span class="media-counter">1 / ${images.length}</span>
            </div>
            <h3 class="product-name">${item.nome}</h3>
            <p class="product-producer">${item.produtor}</p>
            ${pricePerKg}
            <div class="product-bottom">
                <span class="product-price">${formatEUR(item.prezo)}</span>
                <div class="qty-picker" aria-label="Selector de cantidad">
                    <button class="qty-btn qty-dec" type="button" title="Reducir cantidad">-</button>
                    <input class="qty-input" type="number" min="${qtyMin}" max="${qtyMax}" step="${qtyStep}" value="${qtyDefault}" aria-label="Cantidad">
                    <button class="qty-btn qty-inc" type="button" title="Aumentar cantidad">+</button>
                </div>
                <button class="add-cart" title="Añadir al carrito">+</button>
            </div>
        `;

        // Navegación circular de imágenes para no salir de rango.
        let imageIndex = 0;
        const imageEl = card.querySelector('.product-image');
        const counterEl = card.querySelector('.media-counter');
        const prevEl = card.querySelector('.media-prev');
        const nextEl = card.querySelector('.media-next');

        const renderImage = () => {
            imageEl.src = images[imageIndex];
            counterEl.textContent = `${imageIndex + 1} / ${images.length}`;
        };

        prevEl.addEventListener('click', () => {
            imageIndex = (imageIndex - 1 + images.length) % images.length;
            renderImage();
        });

        nextEl.addEventListener('click', () => {
            imageIndex = (imageIndex + 1) % images.length;
            renderImage();
        });

        const qtyInput = card.querySelector('.qty-input');
        const qtyDec = card.querySelector('.qty-dec');
        const qtyInc = card.querySelector('.qty-inc');

        const roundQty = (value) => {
            if (isWeightBased) {
                return Math.round(value * 10) / 10;
            }
            return Math.round(value);
        };

        // Limita y corrige la cantidad para respetar reglas de stock y tipo de producto.
        const normalizeQty = () => {
            let qty = Number(qtyInput.value);
            qty = Number.isFinite(qty) ? qty : qtyDefault;
            qty = roundQty(Math.max(qtyMin, Math.min(qtyMax, qty)));
            qtyInput.value = isWeightBased ? qty.toFixed(1) : String(qty);
            return qty;
        };

        qtyInput.addEventListener('change', normalizeQty);

        qtyDec.addEventListener('click', () => {
            const current = normalizeQty();
            const nextQty = roundQty(Math.max(qtyMin, current - qtyStep));
            qtyInput.value = isWeightBased ? nextQty.toFixed(1) : String(nextQty);
        });

        qtyInc.addEventListener('click', () => {
            const current = normalizeQty();
            const nextQty = roundQty(Math.min(qtyMax, current + qtyStep));
            qtyInput.value = isWeightBased ? nextQty.toFixed(1) : String(nextQty);
        });

        // Inserta o reemplaza línea en el carrito del usuario autenticado.
        const addButton = card.querySelector('.add-cart');
        addButton.addEventListener('click', async () => {
            if (!withAuth()) {
                return;
            }

            try {
                const selectedQty = normalizeQty();
                await api('/cart/items', {
                    method: 'POST',
                    body: JSON.stringify({
                        user_id: state.user.id_usuario,
                        product_id: item.id_produto,
                        quantity: selectedQty,
                    }),
                });
                await refreshCart();
                showMessage('success', `Añadido al carrito: ${item.nome} x${selectedQty}`);
            } catch (error) {
                showMessage('error', error.message);
            }
        });

        container.appendChild(card);
    });
}

// En la home se usa el carrito para actualizar el badge de cabecera.
function renderCartBadge() {
    const cartBadge = $('cart-count-badge');
    if (!cartBadge) {
        return;
    }
    cartBadge.textContent = String(state.cart.items.length);
}

function formatOrderDate(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function renderOrders() {
    const container = $('orders-history-list');
    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!state.user) {
        return;
    }

    if (state.orders.length === 0) {
        const emptyState = document.createElement('p');
        emptyState.className = 'empty-products';
        emptyState.textContent = 'Todavía no has realizado pedidos.';
        container.appendChild(emptyState);
        return;
    }

    state.orders.forEach((order) => {
        const item = document.createElement('article');
        item.className = 'order-item';
        item.innerHTML = `
            <div>
                <strong>Código</strong>
                <span>${order.codigo_pedido}</span>
            </div>
            <div>
                <strong>Estado</strong>
                <span>${order.estado}</span>
            </div>
            <div>
                <strong>Total</strong>
                <span>${formatEUR(order.importe_total)}</span>
            </div>
            <div>
                <strong>Fecha</strong>
                <span>${formatOrderDate(order.data_pedido)}</span>
            </div>
        `;
        container.appendChild(item);
    });
}

// Recarga catálogo desde backend y repinta tarjetas.
async function refreshProducts() {
    try {
        const data = await api('/products');
        state.products = data.items || [];
        renderProducts();
    } catch (error) {
        showMessage('error', error.message);
    }
}

// Recarga carrito actual del usuario y actualiza badge superior.
async function refreshCart() {
    if (!state.user) {
        state.cart = { items: [], total: 0 };
        renderCartBadge();
        return;
    }

    try {
        const data = await api(`/cart?user_id=${state.user.id_usuario}`);
        state.cart = {
            items: data.items || [],
            total: Number(data.total || 0),
        };
        renderCartBadge();
    } catch (error) {
        showMessage('error', error.message);
    }
}

async function refreshOrders() {
    if (!state.user) {
        state.orders = [];
        renderOrders();
        return;
    }

    try {
        const data = await api(`/orders?user_id=${state.user.id_usuario}`);
        state.orders = data.items || [];
        renderOrders();
    } catch (error) {
        showMessage('error', error.message);
    }
}

// Registro y precarga de credenciales en el formulario de login para agilizar flujo.
$('register-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        await api('/register', {
            method: 'POST',
            body: JSON.stringify({
                nome: $('register-name').value,
                email: $('register-email').value,
                contrasinal: $('register-password').value,
            }),
        });

        $('email').value = $('register-email').value;
        $('password').value = $('register-password').value;
        showMessage('success', 'Usuario registrado. Ahora puedes iniciar sesión.');
    } catch (error) {
        showMessage('error', error.message);
    }
});

// Recuperación de contraseña de prueba para el prototipo.
$('recover-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const result = await api('/password/recover', {
            method: 'POST',
            body: JSON.stringify({
                email: $('recover-email').value,
                new_password: $('recover-password').value,
            }),
        });

        showMessage('success', result.message || 'Recuperación procesada correctamente.');
        $('recover-password').value = '';
    } catch (error) {
        showMessage('error', error.message);
    }
});

// Login: guarda sesión local y recarga estado de carrito para el badge.
$('login-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const data = await api('/login', {
            method: 'POST',
            body: JSON.stringify({
                email: $('email').value,
                contrasinal: $('password').value,
            }),
        });

        state.user = data.user;
        saveSession();
        renderUser();
        await refreshCart();
        await refreshOrders();
        showMessage('success', `Bienvenido, ${data.user.nome}.`);
    } catch (error) {
        showMessage('error', error.message);
    }
});

$('logout-button').addEventListener('click', async () => {
    state.user = null;
    saveSession();
    state.orders = [];
    renderUser();
    await refreshCart();
    renderOrders();
    showMessage('info', 'Sesión cerrada correctamente.');
});

// Botones de catálogo principal.
$('hero-load-products').addEventListener('click', refreshProducts);

$('reload-orders')?.addEventListener('click', refreshOrders);

// Buscador en vivo dentro del conjunto de productos ya descargado.
const homeSearchInput = document.querySelector('.topbar .search');
if (homeSearchInput) {
    homeSearchInput.addEventListener('input', (event) => {
        state.searchTerm = event.target.value;
        renderProducts();
    });
}

// El badge de carrito redirige a la página dedicada de checkout.
const cartBadgeButton = $('cart-badge');
if (cartBadgeButton) {
    cartBadgeButton.addEventListener('click', () => {
        window.location.href = 'checkout.html';
    });
}

// Secuencia de arranque del home.
loadSession();
renderUser();
renderCartBadge();
if (state.user) {
    refreshCart();
    refreshOrders();
}
renderOrders();
refreshProducts();
