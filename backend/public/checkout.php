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
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                        <span style="background-color: var(--secondary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</span>
                        <h3>Billing Address</h3>
                    </div>
                    <form id="checkout-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fname">Full Name</label>
                                <input type="text" id="fname" name="fullname" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Street Address</label>
                            <input type="text" id="address" name="address" placeholder="123 Main St" required>
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
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                        <span style="background-color: var(--secondary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</span>
                        <h3>Payment Method</h3>
                    </div>
                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" id="card" name="payment" value="card" checked>
                            <label for="card">
                                <div style="font-size: 24px; margin-bottom: 8px;">💳</div>
                                Credit/Debit Card
                            </label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" id="paypal" name="payment" value="paypal">
                            <label for="paypal">
                                <div style="font-size: 24px; margin-bottom: 8px;">🅿️</div>
                                PayPal
                            </label>
                        </div>
                    </div>

                    <!-- Card Payment Fields -->
                    <div id="card-fields">
                        <div class="form-group">
                            <label for="cardname">Cardholder Name</label>
                            <input type="text" id="cardname" name="cardname" placeholder="John Doe" required>
                        </div>
                        <div class="form-group">
                            <label for="cardnumber">Card Number</label>
                            <input type="text" id="cardnumber" name="cardnumber" placeholder="0000 0000 0000 0000" pattern="[0-9\s]{19}" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Expiration Date</label>
                                <input type="text" id="expiry" name="expiry" placeholder="MM / YY" pattern="[0-9/\s]{7}" required>
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123" pattern="[0-9]{3,4}" required>
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Note -->
                    <div id="paypal-fields" style="display: none; padding: 16px; background-color: #f5f5f5; border-radius: 8px; border-left: 4px solid var(--secondary);">
                        <p style="color: var(--text-dark); font-size: 14px; margin: 0; font-weight: 500;">
                            ✓ You will be securely redirected to PayPal to complete your purchase.
                        </p>
                    </div>
                </div>

                <!-- Order Review -->
                <div class="checkout-section">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                        <span style="background-color: var(--secondary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</span>
                        <h3>Review & Place Order</h3>
                    </div>
                    <div style="padding: 16px; background-color: #fafafa; border-radius: 8px; margin-bottom: 16px;">
                        <p style="color: var(--text-dark); font-size: 14px; margin: 0;">
                            ✓ By completing this purchase, you agree to our <a href="#" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Terms of Service</a> and <a href="#" style="color: var(--secondary); text-decoration: none; font-weight: 600;">Privacy Policy</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="order-summary">
                <h3 class="order-summary-title">Order Summary</h3>

                <?php 
                $subtotal = 0;
                foreach ($cart as $item): 
                    $itemTotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                    $subtotal += $itemTotal;
                ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span>$<?php echo number_format($itemTotal, 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                    <div class="order-item">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="order-item">
                        <span>Shipping</span>
                        <span>$0.00</span>
                    </div>
                    <div class="order-item">
                        <span>Tax (10%)</span>
                        <span>$<?php echo number_format($subtotal * 0.1, 2); ?></span>
                    </div>
                </div>

                <div class="order-total">
                    <span>Total</span>
                    <span>$<?php echo number_format($subtotal * 1.1, 2); ?></span>
                </div>

                <button type="button" class="btn btn-primary complete-purchase-btn" id="complete-btn">Complete Purchase</button>
                <button type="button" class="btn btn-secondary" style="width: 100%;" id="back-btn">Back to Cart</button>
            </div>
        </div>
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
                    <li><a href="home.php#products">All Products</a></li>
                    <li><a href="home.php#featured">Featured</a></li>
                    <li><a href="home.php#new">New Arrivals</a></li>
                    <li><a href="home.php#deals">Deals</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="home.php#about">About Us</a></li>
                    <li><a href="home.php#farmers">Our Farmers</a></li>
                    <li><a href="home.php#contact">Contact</a></li>
                    <li><a href="home.php#blog">Blog</a></li>
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

        // Back button
        document.getElementById('back-btn').addEventListener('click', function() {
            window.location.href = 'cart.php';
        });

        // Complete purchase
        document.getElementById('complete-btn').addEventListener('click', function() {
            const form = document.getElementById('checkout-form');
            if (form.checkValidity()) {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.innerHTML = `
                    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); text-align: center; z-index: 10000;">
                        <div style="font-size: 64px; margin-bottom: 16px;">✓</div>
                        <h2 style="color: var(--primary); margin-bottom: 8px; font-size: 24px;">Order Placed!</h2>
                        <p style="color: var(--text-light); margin-bottom: 24px;">Thank you for your purchase. You will receive a confirmation email shortly.</p>
                        <a href="home.php" class="btn btn-primary" style="display: inline-flex;">Continue Shopping</a>
                    </div>
                `;
                document.body.appendChild(successDiv);
                
                setTimeout(() => {
                    window.location.href = 'home.php';
                }, 3000);
            } else {
                alert('Please fill in all required fields correctly.');
            }
        });
    </script>

    <script src="assets/app.js" defer></script>
</body>

</html>
