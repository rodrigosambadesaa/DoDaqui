<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$productId = trim((string) ($_GET['id'] ?? ''));

$products = [
    [
        'id' => 'product-1',
        'name' => 'Tarro de miel ecológica',
        'meta' => 'Granja Abeja Feliz',
        'category' => 'alimentacion',
        'price' => 12.50,
        'summary' => 'Miel cruda de producción local, sin mezclas industriales y con cosecha de temporada.',
        'description' => 'Este tarro de miel se obtiene en colmenas de proximidad y conserva propiedades naturales al no someterse a procesos industriales.',
    ],
    [
        'id' => 'product-2',
        'name' => 'Cesta de mimbre artesanal',
        'meta' => 'Colectivo Artesano',
        'category' => 'artesania',
        'price' => 45.00,
        'summary' => 'Pieza trenzada a mano con fibras naturales, ideal para almacenaje y decoración.',
        'description' => 'Cada pieza está elaborada a mano con mimbre local y acabados resistentes para uso diario en el hogar.',
    ],
    [
        'id' => 'product-3',
        'name' => 'Aceite de oliva prensado en frío',
        'meta' => 'Valle del Sol',
        'category' => 'alimentacion',
        'price' => 18.00,
        'summary' => 'Aceite virgen extra de primera prensada, con perfil afrutado y acidez baja.',
        'description' => 'Aceite producido en almazara tradicional, con extracción en frío para preservar aroma, sabor y calidad nutricional.',
    ],
    [
        'id' => 'product-4',
        'name' => 'Pan de masa madre',
        'meta' => 'Panadería Local',
        'category' => 'alimentacion',
        'price' => 6.50,
        'summary' => 'Pan de fermentación lenta, corteza crujiente y miga alveolada elaborado cada mañana.',
        'description' => 'Pan horneado diariamente con fermentación natural y harinas seleccionadas de productores de la zona.',
    ],
    [
        'id' => 'product-5',
        'name' => 'Queso curado artesanal',
        'meta' => 'Lácteos da Serra',
        'category' => 'alimentacion',
        'price' => 15.20,
        'summary' => 'Queso curado de leche local con maduración lenta y sabor intenso.',
        'description' => 'Queso de producción limitada con maduración controlada y notas complejas para tabla o cocina gourmet.',
    ],
    [
        'id' => 'product-6',
        'name' => 'Mermelada de frutos rojos',
        'meta' => 'Huerta Atlántica',
        'category' => 'cuidado',
        'price' => 7.90,
        'summary' => 'Elaborada en pequeños lotes con fruta de temporada y bajo contenido de azúcar.',
        'description' => 'Mermelada artesana cocinada lentamente para conservar sabor y textura, perfecta para desayunos y repostería.',
    ],
];

$product = null;
foreach ($products as $candidate) {
    if ((string) $candidate['id'] === $productId) {
        $product = $candidate;
        break;
    }
}

if ($product === null) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? safe((string) $product['name']) : 'Producto no encontrado'; ?> | DoDaqui</title>
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
                    <a href="/cart.php">Pedidos</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <?php if ($user === null): ?>
                        <a class="login-link" href="/auth.php">Iniciar sesión</a>
                    <?php else: ?>
                        <span><?php echo safe((string) $user['nome']); ?></span>
                        <a href="/logout.php">Salir</a>
                    <?php endif; ?>
                    <a href="/cart.php" aria-label="Carrito">Carrito</a>
                    <span class="badge-count" id="cart-count">0</span>
                </div>
            </header>

            <main class="store-main">
                <?php if ($product === null): ?>
                    <section class="box">
                        <h2 class="catalog-title">Producto no encontrado</h2>
                        <p class="section-sub">No existe un producto con el identificador solicitado.</p>
                        <a class="btn btn-light" href="/products.php" style="margin-top: 10px;">Volver al catálogo</a>
                    </section>
                <?php else: ?>
                    <section class="box" style="display: grid; grid-template-columns: minmax(220px, 320px) minmax(0, 1fr); gap: 16px; align-items: start;">
                        <div class="product-thumb placeholder" style="height: 260px; margin-bottom: 0;"></div>
                        <div>
                            <p class="muted-xs" style="margin-bottom: 6px; text-transform: uppercase;"><?php echo safe((string) $product['category']); ?></p>
                            <h1 class="catalog-title" style="margin-bottom: 8px;"><?php echo safe((string) $product['name']); ?></h1>
                            <p class="product-meta" style="font-size: 12px; margin-bottom: 10px;"><?php echo safe((string) $product['meta']); ?></p>
                            <p style="font-size: 14px; margin-bottom: 10px;"><?php echo safe((string) $product['description']); ?></p>
                            <p class="section-sub" style="margin-bottom: 14px;"><?php echo safe((string) $product['summary']); ?></p>
                            <div class="product-row" style="max-width: 220px; margin-bottom: 10px;">
                                <span style="font-size: 18px;"><?php echo formatoEuro((float) $product['price']); ?></span>
                                <button
                                    class="plus-btn add-cart"
                                    type="button"
                                    data-id="<?php echo safe((string) $product['id']); ?>"
                                    data-name="<?php echo safe((string) $product['name']); ?>"
                                    data-price="<?php echo safe((string) $product['price']); ?>">+</button>
                            </div>
                            <a class="btn btn-light" href="/products.php">Volver al catálogo</a>
                        </div>
                    </section>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>