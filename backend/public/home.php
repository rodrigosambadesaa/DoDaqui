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
                        <a href="#">Producto fresco</a>
                        <a href="#">Artesanía</a>
                        <a href="#">Especialidades</a>
                        <a href="#">Bebidas</a>
                    </nav>
                    <div class="nav-grow"></div>
                    <input class="search" type="search" placeholder="Buscar productos locales...">
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

                <main style="padding: 16px 18px 18px;">
                    <section class="hero">
                        <div class="hero-text">
                            <span class="hero-kicker">DIRECTLY FROM THE SOURCE</span>
                            <h2 class="section-title">Apoya a artesanos<br>y productores<br>locales</h2>
                            <p class="section-sub">Descubre productos únicos de tu entorno. Calidad de confianza, personas de tu comunidad.</p>
                            <div class="hero-actions">
                                <button class="btn btn-dark">Comprar</button>
                                <button class="btn btn-light">Saber más</button>
                            </div>
                        </div>
                        <div class="hero-image placeholder"></div>
                    </section>

                    <section class="catalog" id="catalogo-destacados">
                        <div class="catalog-head">
                            <h3 style="font-size: 20px; font-weight: 750;">Productos destacados</h3>
                            <a href="products.php" class="muted-xs" id="ver-todo-productos">Ver todo</a>
                        </div>
                        <div class="catalog-grid">
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

                    <section class="benefits">
                        <article class="benefit">
                            <p>Delivery</p>
                            <h4>Entrega local</h4>
                            <p>Entrega rápida y sostenible dentro de tu comunidad.</p>
                        </article>
                        <article class="benefit">
                            <p>Green</p>
                            <h4>Abastecimiento sostenible</h4>
                            <p>Reducimos huella de carbono conectándote con productores cercanos.</p>
                        </article>
                        <article class="benefit">
                            <p>Care</p>
                            <h4>Apoya al barrio</h4>
                            <p>Cada compra impulsa a pequeños negocios de tu zona.</p>
                        </article>
                    </section>

                    <section class="footer-grid">
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
                        <div>
                            <h4>Newsletter</h4>
                            <p>Recibe novedades de temporada.</p>
                            <div class="news-row">
                                <input type="email" placeholder="Correo electrónico">
                                <button class="btn btn-dark">Unirme</button>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>
