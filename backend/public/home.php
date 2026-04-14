<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoDaqui | Tienda local</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="page-wrap">
        <div class="main-grid">
            <div class="desktop-shell">
                <header class="top-nav">
                    <a class="brand" href="home.php">DoDaqui</a>
                    <nav class="nav-links desktop-only">
                        <a href="home.php" class="is-active">Inicio</a>
                        <a href="products.php">Categorías</a>
                        <a href="cart.php">Carrito</a>
                        <a href="#catalogo-destacados">Pedidos</a>
                    </nav>
                    <div class="nav-grow"></div>
                    <div class="nav-actions">
                        <?php if ($user === null): ?>
                            <a href="auth.php">Iniciar sesión</a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="logout.php">Salir</a>
                        <?php endif; ?>
                        <a href="cart.php" aria-label="Carrito">Carrito</a>
                        <span class="badge-count" id="cart-count">0</span>
                    </div>
                </header>

                <main class="store-main">
                    <section class="hero hero-shop">
                        <div class="hero-text center-hero-text">
                            <h2 class="section-title">DoDaquí</h2>
                            <p class="hero-tagline">Lo mejor de nuestra tierra, directo a tu puerta. Productos artesanales y locales con alma.</p>
                            <div class="hero-actions hero-actions-center">
                                <a href="products.php" class="btn btn-primary">Explorar catálogo</a>
                            </div>
                        </div>
                        <div class="hero-image sketch-image" aria-hidden="true"></div>
                    </section>

                    <section class="category-strip" aria-label="Categorías destacadas">
                        <article class="category-card">
                            <h3>Alimentación</h3>
                        </article>
                        <article class="category-card">
                            <h3>Artesanía</h3>
                        </article>
                        <article class="category-card">
                            <h3>Cuidado personal</h3>
                        </article>
                        <article class="category-card">
                            <h3>Bebidas</h3>
                        </article>
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
                            <article class="product-card" data-name="Tarro de miel ecológica" data-origin="Granja Abeja Feliz" data-price="12.50" data-summary="Miel cruda de producción local, sin mezclas industriales y con cosecha de temporada.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Tarro de miel ecológica</p>
                                <p class="product-meta">Granja Abeja Feliz</p>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(12.5); ?></span>
                                    <div class="product-row-actions">
                                        <button class="plus-btn view-product" type="button">Ver</button>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Granja Abeja Feliz</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(12.5); ?></p>
                                    <p class="detail-summary">Miel cruda de producción local, sin mezclas industriales y con cosecha de temporada.</p>
                                </div>
                            </article>

                            <article class="product-card" data-name="Cesta de mimbre artesanal" data-origin="Colectivo Artesano" data-price="45.00" data-summary="Pieza trenzada a mano con fibras naturales, ideal para almacenaje y decoración.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Cesta de mimbre artesanal</p>
                                <p class="product-meta">Colectivo Artesano</p>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(45); ?></span>
                                    <div class="product-row-actions">
                                        <button class="plus-btn view-product" type="button">Ver</button>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Colectivo Artesano</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(45); ?></p>
                                    <p class="detail-summary">Pieza trenzada a mano con fibras naturales, ideal para almacenaje y decoración.</p>
                                </div>
                            </article>

                            <article class="product-card" data-name="Aceite de oliva prensado en frío" data-origin="Valle del Sol" data-price="18.00" data-summary="Aceite virgen extra de primera prensada, con perfil afrutado y acidez baja.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Aceite de oliva prensado en frío</p>
                                <p class="product-meta">Valle del Sol</p>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(18); ?></span>
                                    <div class="product-row-actions">
                                        <button class="plus-btn view-product" type="button">Ver</button>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Valle del Sol</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(18); ?></p>
                                    <p class="detail-summary">Aceite virgen extra de primera prensada, con perfil afrutado y acidez baja.</p>
                                </div>
                            </article>

                            <article class="product-card" data-name="Pan de masa madre" data-origin="Panadería Local" data-price="6.50" data-summary="Pan de fermentación lenta, corteza crujiente y miga alveolada elaborado cada mañana.">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Pan de masa madre</p>
                                <p class="product-meta">Panadería Local</p>
                                <div class="product-row">
                                    <span><?php echo formatoEuro(6.5); ?></span>
                                    <div class="product-row-actions">
                                        <button class="plus-btn view-product" type="button">Ver</button>
                                        <button class="plus-btn add-cart" type="button">+</button>
                                    </div>
                                </div>
                                <div class="product-detail-inline" hidden>
                                    <p class="detail-meta">Origen: Panadería Local</p>
                                    <p class="detail-meta">Precio: <?php echo formatoEuro(6.5); ?></p>
                                    <p class="detail-summary">Pan de fermentación lenta, corteza crujiente y miga alveolada elaborado cada mañana.</p>
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
                        <h3>Lo que dicen nuestros clientes</h3>
                        <div class="testimonial-grid">
                            <article class="testimonial-card">
                                <p class="testimonial-author">Ana García</p>
                                <p>"El café orgánico es espectacular. Se nota la frescura y el cuidado en el tostado. ¡Repetiré seguro!"</p>
                            </article>
                            <article class="testimonial-card">
                                <p class="testimonial-author">Carlos Ruiz</p>
                                <p>"La miel artesanal tiene un sabor auténtico que no encuentro en el supermercado. DoDaquí es un gran descubrimiento."</p>
                            </article>
                            <article class="testimonial-card">
                                <p class="testimonial-author">Marta López</p>
                                <p>"Me encanta apoyar a los productores locales y que además lo traigan a casa muy fácil. El envío fue rapidísimo."</p>
                            </article>
                        </div>
                    </section>

                    <section class="footer-grid store-footer-grid">
                        <div>
                            <h4>DoDaqui</h4>
                            <p>El marketplace de referencia para productos locales, con lo mejor de tu comunidad en casa.</p>
                        </div>
                        <div>
                            <h4>Tienda</h4>
                            <ul>
                                <li>Todos los productos</li>
                                <li>Alimentación fresca</li>
                                <li>Hogar y vida</li>
                                <li>Tarjetas regalo</li>
                            </ul>
                        </div>
                        <div>
                            <h4>Empresa</h4>
                            <ul>
                                <li>Sobre nosotros</li>
                                <li>Nuestros productores</li>
                                <li>Sostenibilidad</li>
                                <li>Contacto</li>
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