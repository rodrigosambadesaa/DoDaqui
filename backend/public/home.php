<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="gl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoDaquí | Produtos Locais</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <h1 class="logo">DoDaquí</h1>
            </div>
            <div class="navbar-menu">
                <a href="#featured" class="nav-link">Featured</a>
                <a href="#products" class="nav-link">Local Products</a>
                <a href="#about" class="nav-link">About Us</a>
            </div>
            <div class="navbar-actions">
                <?php if ($user === null): ?>
                    <a href="auth.php" class="nav-link">Sign In</a>
                    <a href="auth.php" class="btn btn-primary">Sign Up</a>
                <?php else: ?>
                    <span class="nav-user"><?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="api/logout.php" class="btn btn-secondary">Sign Out</a>
                <?php endif; ?>
                <a href="cart.php" class="cart-link">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-count" id="cart-count">0</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-badge">DIRECTLY FROM THE SOURCE</span>
            <h2>Support Local Craftsmen & Farmers</h2>
            <p>Discover unique products made right in your neighborhood. Quality you can trust, people you know.</p>
            <div class="hero-actions">
                <a href="#featured" class="btn btn-primary btn-lg">Shop Now</a>
                <a href="#about" class="btn btn-secondary btn-lg">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="placeholder-img hero-img-large"></div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section id="featured" class="featured-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Featured Products</h2>
                    <p style="color: var(--text-light); margin-top: 8px;">Handpicked items from local artisans and farmers</p>
                </div>
                <a href="#products" class="view-all">View all →</a>
            </div>
            <div class="products-grid">
                <article class="product-card">
                    <div class="product-image placeholder-img"></div>
                    <h3>Organic Honey Jar</h3>
                    <p class="product-meta">Beekeeper Collection</p>
                    <p class="product-price">$12.50</p>
                    <div class="product-actions">
                        <button class="btn btn-light">View</button>
                        <button class="btn btn-primary add-cart">Add to Cart</button>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image placeholder-img"></div>
                    <h3>Hand-Woven Basket</h3>
                    <p class="product-meta">Artisan Collection</p>
                    <p class="product-price">$45.00</p>
                    <div class="product-actions">
                        <button class="btn btn-light">View</button>
                        <button class="btn btn-primary add-cart">Add to Cart</button>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image placeholder-img"></div>
                    <h3>Cold-Pressed Olive Oil</h3>
                    <p class="product-meta">Farm Classics</p>
                    <p class="product-price">$18.00</p>
                    <div class="product-actions">
                        <button class="btn btn-light">View</button>
                        <button class="btn btn-primary add-cart">Add to Cart</button>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image placeholder-img"></div>
                    <h3>Artisan Sourdough</h3>
                    <p class="product-meta">The Local Bakery</p>
                    <p class="product-price">$6.50</p>
                    <div class="product-actions">
                        <button class="btn btn-light">View</button>
                        <button class="btn btn-primary add-cart">Add to Cart</button>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Info Cards Section -->
    <section class="info-cards-section">
        <div class="container">
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-icon">📦</div>
                    <h3>Local Delivery</h3>
                    <p>Fast and friendly delivery within your community limits.</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🌱</div>
                    <h3>Sustainable Sourcing</h3>
                    <p>Reducing waste and supporting eco-friendly, local products.</p>
                </div>
                <div class="info-card">
                    <div class="info-icon">❤️</div>
                    <h3>Support Neighbors</h3>
                    <p>Every purchase directly supports local craftspeople and families.</p>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/app.js" defer></script>
</body>

</html>
