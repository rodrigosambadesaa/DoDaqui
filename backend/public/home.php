<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

$user = currentUser();

$productNames = [
    'product-1' => 'Tarro de miel ecológica',
    'product-2' => 'Cesta de mimbre artesanal',
    'product-3' => 'Aceite de oliva prensado en frío',
    'product-4' => 'Pan de masa madre',
    'product-5' => 'Queso curado artesanal',
    'product-6' => 'Mermelada de frutos rojos',
];

$recentOpinions = [];

try {
    $pdo = db();
    ensureOpinionsSchema($pdo);

    $query = $pdo->query(
        'SELECT o.id_produto, o.valoracion, o.opinion, o.data_opinion, u.nome
         FROM opinions_clientes o
         INNER JOIN usuarios u ON u.id_usuario = o.id_cliente
         ORDER BY o.data_opinion DESC, o.id_opinion DESC
         LIMIT 3'
    );

    $recentOpinions = $query ? ($query->fetchAll() ?: []) : [];
} catch (Throwable $exception) {
    $recentOpinions = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo safe(csrfToken()); ?>">
    <title>DoDaqui | Tienda local</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="page-wrap">
        <div class="main-grid">
            <div class="desktop-shell">
                <header class="top-nav">
                    <a class="brand" href="/home.php">DoDaqui</a>
                    <nav class="nav-links desktop-only">
                        <a href="/home.php" class="is-active">Inicio</a>
                        <a href="/products.php">Categorías</a>
                        <a href="/cart.php">Carrito</a>
                        <a href="/orders.php">Pedidos</a>
                    </nav>
                    <div class="nav-grow"></div>
                    <div class="nav-actions">
                        <?php if ($user === null): ?>
                            <a class="login-link" href="/auth.php">Iniciar sesión</a>
                        <?php else: ?>
                            <span class="avatar-mini" aria-hidden="true"></span>
                            <span><?php echo safe((string) ($user['nome'] ?? 'Usuario')); ?></span>
                            <a href="/profile.php">Perfil</a>
                            <a href="/logout.php">Salir</a>
                        <?php endif; ?>
                        <a href="/cart.php" aria-label="Carrito">Carrito</a>
                        <span class="badge-count" id="cart-count">0</span>
                    </div>
                </header>

                <main class="store-main">
                    <section class="hero hero-shop">
                        <div class="hero-text center-hero-text">
                            <h2 class="section-title">DoDaquí</h2>
                            <p class="hero-tagline">Lo mejor de nuestra tierra, directo a tu puerta. Productos artesanales y locales con alma.</p>
                            <div class="hero-actions hero-actions-center">
                                <a href="/products.php" class="btn btn-primary">Explorar catálogo</a>
                            </div>
                        </div>
                        <div class="hero-image sketch-image" aria-hidden="true">
                            <span>Ver todo ↗</span>
                        </div>
                    </section>

                    <section class="category-strip" aria-label="Categorías destacadas">
                        <a class="category-card" href="/products.php?categoria=alimentacion" aria-label="Ir a categoría Alimentación">
                            <h3>Alimentación</h3>
                        </a>
                        <a class="category-card" href="/products.php?categoria=artesania" aria-label="Ir a categoría Artesanía">
                            <h3>Artesanía</h3>
                        </a>
                        <a class="category-card" href="/products.php?categoria=cuidado" aria-label="Ir a categoría Cuidado personal">
                            <h3>Cuidado personal</h3>
                        </a>
                        <a class="category-card" href="/products.php?categoria=bebidas" aria-label="Ir a categoría Bebidas">
                            <h3>Bebidas</h3>
                        </a>
                    </section>

                    <section class="catalog" id="catalogo-destacados">
                        <div class="catalog-head">
                            <h3 class="catalog-title">Productos destacados</h3>
                            <div class="catalog-filters" aria-hidden="true">
                                <span class="chip active">Todos</span>
                                <span class="chip">Más vendidos</span>
                            </div>
                        </div>
                        <div class="catalog-grid shop-grid">
                            <article class="product-card" data-id="product-1" data-name="Café orgánico" data-origin="Tostado artesanal, 500g origen local." data-price="12.50" data-summary="Café orgánico con perfil suave y notas a cacao. Producido por cooperativas de proximidad.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Café orgánico</p>
                                <p class="product-meta">Tostado artesanal, 500g origen local.</p>
                                <button class="product-link view-product" type="button">Ver detalle</button>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(12.5); ?></span>
                                    <div class="product-row-actions">
                                        <span class="pill-stock">En stock</span>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Tostado artesanal, 500g origen local.</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(12.5); ?></p>
                                    <p class="detail-summary">Café orgánico con perfil suave y notas a cacao. Producido por cooperativas de proximidad.</p>
                                    <a class="muted-xs" href="/product.php?id=product-1">Ir a ficha y opiniones</a>
                                </div>
                            </article>

                            <article class="product-card" data-id="product-2" data-name="Miel artesanal" data-origin="Miel multifloral pura de la sierra." data-price="8.90" data-summary="Miel artesana cosechada en pequeños lotes con trazabilidad completa.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Miel artesanal</p>
                                <p class="product-meta">Miel multifloral pura de la sierra.</p>
                                <button class="product-link view-product" type="button">Ver detalle</button>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(8.9); ?></span>
                                    <div class="product-row-actions">
                                        <span class="pill-stock">En stock</span>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Miel multifloral pura de la sierra.</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(8.9); ?></p>
                                    <p class="detail-summary">Miel artesana cosechada en pequeños lotes con trazabilidad completa.</p>
                                    <a class="muted-xs" href="/product.php?id=product-2">Ir a ficha y opiniones</a>
                                </div>
                            </article>

                            <article class="product-card" data-id="product-3" data-name="Pan de masa madre" data-origin="Fermentación natural de 24 horas." data-price="4.50" data-summary="Hogaza de masa madre elaborada cada mañana por panaderías locales.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Pan de masa madre</p>
                                <p class="product-meta">Fermentación natural de 24 horas.</p>
                                <button class="product-link view-product" type="button">Ver detalle</button>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(4.5); ?></span>
                                    <div class="product-row-actions">
                                        <span class="pill-stock pill-stock-warn">Agotado</span>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Fermentación natural de 24 horas.</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(4.5); ?></p>
                                    <p class="detail-summary">Hogaza de masa madre elaborada cada mañana por panaderías locales.</p>
                                    <a class="muted-xs" href="/product.php?id=product-4">Ir a ficha y opiniones</a>
                                </div>
                            </article>

                            <article class="product-card" data-id="product-4" data-name="Jabón natural" data-origin="Lavanda y aceites esenciales." data-price="6.00" data-summary="Jabón de lavanda elaborado con ingredientes naturales y producción local.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Jabón natural</p>
                                <p class="product-meta">Lavanda y aceites esenciales.</p>
                                <button class="product-link view-product" type="button">Ver detalle</button>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(6); ?></span>
                                    <div class="product-row-actions">
                                        <span class="pill-stock pill-stock-soon">Próximamente</span>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Lavanda y aceites esenciales.</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(6); ?></p>
                                    <p class="detail-summary">Jabón de lavanda elaborado con ingredientes naturales y producción local.</p>
                                    <a class="muted-xs" href="/product.php?id=product-6">Ir a ficha y opiniones</a>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="newsletter-band" aria-label="Suscripción a boletín">
                        <div>
                            <h3>Suscríbete a nuestra newsletter</h3>
                            <p>Recibe ofertas exclusivas de productores locales y noticias sobre nuevos lanzamientos de DoDaquí.</p>
                        </div>
                        <form class="newsletter-form" action="#" method="post" onsubmit="return false;">
                            <input type="email" placeholder="Tu correo electrónico" aria-label="Correo electrónico">
                            <button class="btn btn-primary" type="submit">Suscribirme</button>
                        </form>
                    </section>

                    <section class="testimonials" aria-label="Opiniones de clientes">
                        <div class="catalog-head" style="margin-bottom: 10px;">
                            <h3 style="margin: 0;">Lo que dicen nuestros clientes</h3>
                            <a class="muted-xs" href="/opinions.php">Ver todas</a>
                        </div>
                        <?php if (count($recentOpinions) === 0): ?>
                            <article class="testimonial-card">
                                <p class="testimonial-author">Todavía no hay opiniones</p>
                                <p>Las valoraciones de clientes aparecerán aquí en cuanto se publiquen.</p>
                            </article>
                        <?php else: ?>
                            <div class="testimonial-grid">
                                <?php foreach ($recentOpinions as $entry): ?>
                                    <?php
                                    $rating = (int) ($entry['valoracion'] ?? 0);
                                    $stars = str_repeat('★', max(0, $rating)) . str_repeat('☆', max(0, 5 - $rating));
                                    $productId = (string) ($entry['id_produto'] ?? '');
                                    ?>
                                    <article class="testimonial-card">
                                        <p class="testimonial-author"><?php echo safe((string) ($entry['nome'] ?? 'Cliente')); ?></p>
                                        <p class="muted-xs" style="margin-bottom: 4px;"><?php echo safe($stars); ?></p>
                                        <p style="margin-bottom: 6px;"><?php echo safe((string) ($entry['opinion'] ?? '')); ?></p>
                                        <a class="muted-xs" href="/product.php?id=<?php echo urlencode($productId); ?>">
                                            <?php echo safe($productNames[$productId] ?? $productId); ?>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="footer-grid store-footer-grid" id="store-footer">
                        <div id="sobre-nosotros">
                            <h4>DoDaqui</h4>
                            <p>El marketplace de referencia para productos locales, con lo mejor de tu comunidad en casa.</p>
                        </div>
                        <div>
                            <h4>Tienda</h4>
                            <ul>
                                <li><a href="/products.php">Todos los productos</a></li>
                                <li><a href="/products.php?categoria=alimentacion">Alimentación fresca</a></li>
                                <li><a href="/products.php?categoria=hogar">Hogar y vida</a></li>
                                <li><a href="/products.php?categoria=regalo">Tarjetas regalo</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4>Empresa</h4>
                            <ul>
                                <li><a href="/home.php#sobre-nosotros">Sobre nosotros</a></li>
                                <li><a href="/home.php#catalogo-destacados">Nuestros productores</a></li>
                                <li><a href="/home.php#store-footer">Sostenibilidad</a></li>
                                <li><a href="/auth.php">Contacto</a></li>
                            </ul>
                        </div>
                        <div></div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>