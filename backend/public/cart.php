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
            <p style="text-align: center; color: var(--text-light); padding: 40px;">
                Your cart is empty. <a href="home.php" style="color: var(--secondary);">Continue Shopping</a>
            </p>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cart as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image"></div>
                            <div class="cart-item-details">
                                <h4><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p>Quantity: <?php echo $item['quantity'] ?? 1; ?></p>
                            </div>
                            <div class="cart-item-price">$<?php echo number_format($item['price'] ?? 0, 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax</span>
                        <span>$0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>$0.00</span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>
