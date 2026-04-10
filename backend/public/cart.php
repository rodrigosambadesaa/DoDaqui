<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="gl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | DoDaquí</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="home.php" class="logo" style="text-decoration: none;">DoDaquí</a>
            </div>
            <div class="navbar-menu">
                <a href="home.php#featured" class="nav-link">Featured</a>
                <a href="home.php#products" class="nav-link">Local Products</a>
                <a href="home.php#about" class="nav-link">About Us</a>
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
                    <span class="cart-count" id="cart-count"><?php echo count($cart); ?></span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Cart Section -->
    <div class="cart-container">
        <h1 class="cart-header">Your Shopping Cart</h1>

        <?php if (count($cart) === 0): ?>
            <div style="text-align: center; padding: 60px 20px; background-color: white; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="font-size: 64px; margin-bottom: 16px;">🛒</div>
                <h2 style="color: var(--primary); margin-bottom: 8px;">Your cart is empty</h2>
                <p style="color: var(--text-light); margin-bottom: 24px;">Start shopping to add items to your cart</p>
                <a href="home.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php 
                    $subtotal = 0;
                    foreach ($cart as $item): 
                        $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                        $subtotal += $itemTotal;
                    ?>
                        <div class="cart-item">
                            <div class="cart-item-image"></div>
                            <div class="cart-item-details">
                                <h4><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p style="font-size: 13px; color: var(--text-light); margin: 4px 0;">Quantity: <strong><?php echo $item['quantity'] ?? 1; ?></strong></p>
                                <p style="font-size: 13px; color: var(--text-light);">$<?php echo number_format($item['price'] ?? 0, 2); ?> each</p>
                            </div>
                            <div class="cart-item-price">$<?php echo number_format($itemTotal, 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (10%)</span>
                        <span>$<?php echo number_format($subtotal * 0.1, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$<?php echo number_format($subtotal * 1.1, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary checkout-btn">Proceed to Checkout</a>
                    <a href="home.php" class="btn btn-secondary" style="width: 100%; margin-top: 8px;">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>DoDaquí</h4>
                <p>The premier marketplace for local features, bringing the experience to your doorstep.</p>
                <div class="social-links">
                    <a href="#" title="Facebook">f</a>
                    <a href="#" title="Instagram">📷</a>
                    <a href="#" title="Twitter">𝕏</a>
                </div>
            </div>

            <div class="footer-section">
                <h4>Shop</h4>
                <ul>
                    <li><a href="#products">All Products</a></li>
                    <li><a href="#featured">Featured</a></li>
                    <li><a href="#new">New Arrivals</a></li>
                    <li><a href="#deals">Deals</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#farmers">Our Farmers</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#blog">Blog</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#shipping">Shipping Info</a></li>
                    <li><a href="#returns">Returns</a></li>
                    <li><a href="#help">Help Center</a></li>
                </ul>
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
