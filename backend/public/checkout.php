<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$cart = $_SESSION['cart'] ?? [];

if ($user === null) {
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="gl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | DoDaquí</title>
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
                <span class="nav-user"><?php echo htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="api/logout.php" class="btn btn-secondary">Sign Out</a>
                <a href="cart.php" class="cart-link">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-count" id="cart-count"><?php echo count($cart); ?></span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Checkout Container -->
    <div class="checkout-container">
        <h1 class="cart-header">Checkout</h1>

        <div class="checkout-content">
            <!-- Checkout Form -->
            <div>
                <!-- Billing Address Section -->
                <div class="checkout-section">
                    <h3>Billing Address</h3>
                    <form id="checkout-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fname">Full Name</label>
                                <input type="text" id="fname" name="fullname" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Street Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="zip">ZIP / Postal Code</label>
                                <input type="text" id="zip" name="zip" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" required>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Payment Method Section -->
                <div class="checkout-section">
                    <h3>Payment Method</h3>
                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" id="card" name="payment" value="card" checked>
                            <label for="card">💳 Credit/Debit Card</label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" id="paypal" name="payment" value="paypal">
                            <label for="paypal">🅿️ PayPal</label>
                        </div>
                    </div>

                    <!-- Card Payment Fields -->
                    <div id="card-fields">
                        <div class="form-group">
                            <label for="cardname">Cardholder Name</label>
                            <input type="text" id="cardname" name="cardname" placeholder="John Doe">
                        </div>
                        <div class="form-group">
                            <label for="cardnumber">Card Number</label>
                            <input type="text" id="cardnumber" name="cardnumber" placeholder="0000 0000 0000 0000">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Exp Date</label>
                                <input type="text" id="expiry" name="expiry" placeholder="MM / YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Note -->
                    <div id="paypal-fields" style="display: none;">
                        <p style="color: var(--text-light); font-size: 14px;">You will be redirected to PayPal to complete your purchase.</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="order-summary">
                <h3 class="order-summary-title">Order Summary</h3>

                <?php foreach ($cart as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?> x<?php echo $item['quantity'] ?? 1; ?></span>
                        <span>$<?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                    <div class="order-item">
                        <span>Subtotal</span>
                        <span>$69.12</span>
                    </div>
                    <div class="order-item">
                        <span>Shipping</span>
                        <span>$0.00</span>
                    </div>
                    <div class="order-item">
                        <span>Tax (10%)</span>
                        <span>$6.12</span>
                    </div>
                </div>

                <div class="order-total">
                    <span>Total</span>
                    <span>$75.24</span>
                </div>

                <button type="button" class="btn btn-primary complete-purchase-btn" id="complete-btn">Complete Purchase</button>
            </div>
        </div>
    </div>

    <script>
        // Payment method toggle
        const cardOption = document.getElementById('card');
        const paypalOption = document.getElementById('paypal');
        const cardFields = document.getElementById('card-fields');
        const paypalFields = document.getElementById('paypal-fields');

        cardOption.addEventListener('change', function() {
            cardFields.style.display = 'block';
            paypalFields.style.display = 'none';
        });

        paypalOption.addEventListener('change', function() {
            cardFields.style.display = 'none';
            paypalFields.style.display = 'block';
        });

        // Complete purchase
        document.getElementById('complete-btn').addEventListener('click', function() {
            const form = document.getElementById('checkout-form');
            if (form.checkValidity()) {
                alert('Order placed successfully!');
                // Here you would typically send the order to the server
                // and process payment
            } else {
                alert('Please fill in all required fields.');
            }
        });
    </script>

    <script src="assets/app.js" defer></script>
</body>

</html>
