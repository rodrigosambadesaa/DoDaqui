// Configuración de API compartida con el resto del frontend.
const API_BASE = window.APP_CONFIG?.API_BASE_URL || 'http://localhost:8080/api';

// Banco de imágenes reutilizado para mostrar visuales de producto como en la portada.
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

// Utilidad de selección por id.
const $ = (id) => document.getElementById(id);

function formatEUR(value) {
    return `${Number(value).toFixed(2)} EUR`;
}

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

function renderAllProducts(items) {
    const container = $('all-products-list');
    if (!container) {
        return;
    }

    container.innerHTML = '';

    if (!items || items.length === 0) {
        const emptyState = document.createElement('p');
        emptyState.className = 'empty-products';
        emptyState.textContent = 'No hay productos disponibles en este momento.';
        container.appendChild(emptyState);
        return;
    }

    items.forEach((item) => {
        const isWeightBased = item.prezo_kg !== null && item.prezo_kg !== undefined;
        const images = PRODUCT_IMAGE_BANK[item.slug] || PRODUCT_IMAGE_BANK.fallback;
        const card = document.createElement('article');
        card.className = 'product-card';
        card.innerHTML = `
            <div class="product-media">
                <img class="product-image" src="${images[0]}" alt="${item.nome}">
                <button class="media-nav media-prev" type="button" title="Imagen anterior" aria-label="Imagen anterior">&#10094;</button>
                <button class="media-nav media-next" type="button" title="Imagen siguiente" aria-label="Imagen siguiente">&#10095;</button>
                <span class="media-counter">1 / ${images.length}</span>
            </div>
            <h3 class="product-name">${item.nome}</h3>
            <p class="product-producer">${item.produtor} · ${item.categoria}</p>
            <p class="product-producer">${item.descricion_curta}</p>
            <div class="product-bottom">
                <span class="product-price">${formatEUR(item.prezo)}</span>
                <span class="product-producer">Stock: ${item.stock}</span>
            </div>
            ${isWeightBased ? `<p class="price-per-kg">${formatEUR(item.prezo_kg)} / kg</p>` : ''}
        `;

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

        container.appendChild(card);
    });
}

async function loadAllProducts() {
    const container = $('all-products-list');
    if (!container) {
        return;
    }

    container.innerHTML = '<p class="empty-products">Cargando productos...</p>';

    try {
        const data = await api('/products');
        renderAllProducts(data.items || []);
    } catch (error) {
        container.innerHTML = `<p class="empty-products">No se pudieron cargar los productos: ${error.message}</p>`;
    }
}

$('reload-all-products')?.addEventListener('click', loadAllProducts);

loadAllProducts();
