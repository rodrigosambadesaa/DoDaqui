<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
?>
    <div class="page-wrap">
        <div class="main-grid">
            <div class="desktop-shell">
                <header class="top-nav">
                    <a class="brand" href="home.php">DoDaquí</a>
                    <nav class="nav-links desktop-only">
                        <a href="#">Fresh Produce</a>
                        <a href="#">Artisan Goods</a>
                        <a href="#">Local Specialties</a>
                        <a href="#">Beverages</a>
                    </nav>
                    <div class="nav-grow"></div>
                    <input class="search" type="search" placeholder="Search local products...">
                    <div class="nav-actions">
                        <?php if ($user === null): ?>
                            <a href="auth.php">Sign in</a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                        <a href="cart.php" aria-label="Cart">Cart</a>
                        <span class="badge-count" id="cart-count">0</span>
                    </div>
                </header>

                <main style="padding: 16px 18px 18px;">
                    <section class="hero">
                        <div class="hero-text">
                            <span class="hero-kicker">DIRECTLY FROM THE SOURCE</span>
                            <h2 class="section-title">Support Local<br>Craftsmen &<br>Farmers</h2>
                            <p class="section-sub">Discover unique products made right in your neighborhood. Quality you can trust, people you know.</p>
                            <div class="hero-actions">
                                <button class="btn btn-dark">Shop Now</button>
                                <button class="btn btn-light">Learn More</button>
                            </div>
                        </div>
                        <div class="hero-image placeholder"></div>
                    </section>

                    <section class="catalog">
                        <div class="catalog-head">
                            <h3 style="font-size: 20px; font-weight: 750;">Featured Products</h3>
                            <a href="#" class="muted-xs">View all</a>
                        </div>
                        <div class="catalog-grid">
                            <article class="product-card">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Organic Honey Jar</p>
                                <p class="product-meta">Bee Happy Farm</p>
                                <div class="product-row"><span>$12.50</span><button class="plus-btn add-cart">+</button></div>
                            </article>
                            <article class="product-card">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Hand-Woven Basket</p>
                                <p class="product-meta">Artisan Collective</p>
                                <div class="product-row"><span>$45.00</span><button class="plus-btn add-cart">+</button></div>
                            </article>
                            <article class="product-card">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Cold Pressed Olive Oil</p>
                                <p class="product-meta">Sun Valley Groves</p>
                                <div class="product-row"><span>$18.00</span><button class="plus-btn add-cart">+</button></div>
                            </article>
                            <article class="product-card">
                                <div class="product-thumb placeholder"></div>
                                <p class="product-name">Artisan Sourdough</p>
                                <p class="product-meta">The Local Bakery</p>
                                <div class="product-row"><span>$6.50</span><button class="plus-btn add-cart">+</button></div>
                            </article>
                        </div>
                    </section>

                    <section class="benefits">
                        <article class="benefit">
                            <p>🚚</p>
                            <h4>Local Delivery</h4>
                            <p>Fast and eco-friendly delivery inside your community limits.</p>
                        </article>
                        <article class="benefit">
                            <p>🌿</p>
                            <h4>Sustainable Sourcing</h4>
                            <p>Reducing carbon footprint by connecting you to nearby producers.</p>
                        </article>
                        <article class="benefit">
                            <p>❤</p>
                            <h4>Support Neighbors</h4>
                            <p>Every purchase directly supports small businesses in your area.</p>
                        </article>
                    </section>

                    <section class="footer-grid">
                        <div>
                            <h4>DoDaquí</h4>
                            <p>The premier marketplace for local treasures, bringing your community best products right to your doorstep.</p>
                        </div>
                        <div>
                            <h4>Shop</h4>
                            <ul>
                                <li>All Products</li>
                                <li>Fresh Food</li>
                                <li>Home & Living</li>
                                <li>Gift Cards</li>
                            </ul>
                        </div>
                        <div>
                            <h4>Company</h4>
                            <ul>
                                <li>About Us</li>
                                <li>Our Farmers</li>
                                <li>Sustainability</li>
                                <li>Contact</li>
                            </ul>
                        </div>
                        <div>
                            <h4>Newsletter</h4>
                            <p>Get updates on seasonal arrivals.</p>
                            <div class="news-row">
                                <input type="email" placeholder="Email address">
                                <button class="btn btn-dark">Join</button>
                            </div>
                        </div>
                    </section>
                </main>
            </div>

            <aside class="phone-shell">
                <div class="phone-screen">
                    <div class="phone-top">
                        <span>☰</span>
                        <strong>NEXUS</strong>
                        <span>👜</span>
                    </div>
                    <div style="padding: 8px 4px 10px;">
                        <h3 style="font-size: 34px; line-height: 1.03; margin-bottom: 8px;">Future of Tech.</h3>
                        <p class="muted-xs" style="margin-bottom: 10px;">Experience the next generation of mobile computing and productivity tools.</p>
                        <button class="btn btn-light" style="font-size: 10px; padding: 6px 10px;">Shop Collection</button>
                    </div>
                    <div style="height: 96px;" class="placeholder"></div>

                    <h4 class="phone-section-title">Featured Categories</h4>
                    <div class="phone-card-grid">
                        <div class="phone-mini-card">Phones</div>
                        <div class="phone-mini-card">Laptops</div>
                        <div class="phone-mini-card">Audio</div>
                        <div class="phone-mini-card">Watches</div>
                    </div>

                    <h4 class="phone-section-title">New Arrivals</h4>
                    <div class="phone-list">
                        <div class="phone-list-item"><div class="phone-thumb"></div><div><strong style="font-size: 10px;">Nexus Pro 14</strong><p class="muted-xs">$1,299.00</p></div></div>
                        <div class="phone-list-item"><div class="phone-thumb"></div><div><strong style="font-size: 10px;">Sonic Buds X</strong><p class="muted-xs">$199.00</p></div></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2024 DoDaquí. All rights reserved.</p>
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
            </div>
        </div>
    </footer>
