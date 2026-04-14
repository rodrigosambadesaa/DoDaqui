<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

$user = currentUser();

$products = [
    [
        'id' => 'product-1',
        'name' => 'Tarro de miel ecológica',
        'meta' => 'Granja Abeja Feliz',
        'category' => 'alimentacion',
        'price' => '12.50',
        'summary' => 'Miel cruda de producción local, sin mezclas industriales y con cosecha de temporada.',
    ],
    [
        'id' => 'product-2',
        'name' => 'Cesta de mimbre artesanal',
        'meta' => 'Colectivo Artesano',
        'category' => 'artesania',
        'price' => '45.00',
        'summary' => 'Pieza trenzada a mano con fibras naturales, ideal para almacenaje y decoración.',
    ],
    [
        'id' => 'product-3',
        'name' => 'Aceite de oliva prensado en frío',
        'meta' => 'Valle del Sol',
        'category' => 'alimentacion',
        'price' => '18.00',
        'summary' => 'Aceite virgen extra de primera prensada, con perfil afrutado y acidez baja.',
    ],
    [
        'id' => 'product-4',
        'name' => 'Pan de masa madre',
        'meta' => 'Panadería Local',
        'category' => 'alimentacion',
        'price' => '6.50',
        'summary' => 'Pan de fermentación lenta, corteza crujiente y miga alveolada elaborado cada mañana.',
    ],
    [
        'id' => 'product-5',
        'name' => 'Queso curado artesanal',
        'meta' => 'Lácteos da Serra',
        'category' => 'alimentacion',
        'price' => '15.20',
        'summary' => 'Queso curado de leche local con maduración lenta y sabor intenso.',
    ],
    [
        'id' => 'product-6',
        'name' => 'Mermelada de frutos rojos',
        'meta' => 'Huerta Atlántica',
        'category' => 'cuidado',
        'price' => '7.90',
        'summary' => 'Elaborada en pequeños lotes con fruta de temporada y bajo contenido de azúcar.',
    ],
];

$category = strtolower(trim((string) ($_GET['categoria'] ?? '')));
$allowedCategories = ['alimentacion', 'artesania', 'cuidado', 'bebidas', 'hogar', 'regalo'];

$filteredProducts = $products;
if ($category !== '' && in_array($category, $allowedCategories, true)) {
    $filteredProducts = array_values(array_filter(
        $products,
        static fn(array $product): bool => (string) ($product['category'] ?? '') === $category
    ));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo safe(csrfToken()); ?>">
    <title>Productos | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="page-wrap">
        <div class="desktop-shell">
            <header class="top-nav">
                <a class="brand" href="/home.php">DoDaqui</a>
                <nav class="nav-links desktop-only">
                    <a href="/home.php">Inicio</a>
                    <a href="/products.php" class="is-active">Categorías</a>
                    <a href="/cart.php">Carrito</a>
                    <a href="/home.php#store-footer">Pedidos</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <?php if ($user === null): ?>
                        <a class="login-link" href="/auth.php">Iniciar sesión</a>
                    <?php else: ?>
                        <span><?php echo safe((string) ($user['nome'] ?? 'Usuario')); ?></span>
                        <a href="/logout.php">Salir</a>
                    <?php endif; ?>
                    <a href="/cart.php" aria-label="Carrito">Carrito</a>
                    <span class="badge-count" id="cart-count">0</span>
                </div>
            </header>

            <main class="store-main">
                <section class="catalog" id="catalogo-completo" style="margin-top: 0;">
                    <div class="catalog-head">
                        <h2 class="catalog-title">
                            <?php echo $category !== '' ? 'Categoría: ' . safe(ucfirst($category)) : 'Todos los productos'; ?>
                        </h2>
                        <a href="/home.php" class="muted-xs">Volver a inicio</a>
                    </div>
                    <div class="catalog-grid">
                        <?php foreach ($filteredProducts as $product): ?>
                            <article class="product-card" data-id="<?php echo safe($product['id']); ?>" data-name="<?php echo safe($product['name']); ?>" data-origin="<?php echo safe($product['meta']); ?>" data-price="<?php echo safe($product['price']); ?>" data-summary="<?php echo safe($product['summary']); ?>">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name"><?php echo safe($product['name']); ?></p>
                                <p class="product-meta"><?php echo safe($product['meta']); ?></p>
                                <button class="product-link view-product" type="button">Ver detalle</button>
                                <div class="product-row">
                                    <span><?php echo formatoEuro((float) $product['price']); ?></span>
                                    <div class="product-row-actions">
                                        <span class="pill-stock">En stock</span>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: <?php echo safe($product['meta']); ?></p>
                                    <p class="detail-meta">Categoría: <?php echo safe((string) ($product['category'] ?? '')); ?></p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro((float) $product['price']); ?></p>
                                    <p class="detail-summary"><?php echo safe($product['summary']); ?></p>
                                    <a class="muted-xs" href="/product.php?id=<?php echo urlencode((string) $product['id']); ?>">Ir a ficha y opiniones</a>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <?php if (count($filteredProducts) === 0): ?>
                            <article class="box" style="grid-column: 1 / -1;">
                                <p class="section-sub">No hay productos en esta categoría todavía.</p>
                                <a class="btn btn-light" href="/products.php" style="margin-top: 8px;">Ver todo el catálogo</a>
                            </article>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>