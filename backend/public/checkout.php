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

$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
}
$tax = $subtotal * 0.1;
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="gl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="checkout-wrap" style="grid-template-columns: 1fr;">
        <main class="checkout-main">
            <div class="stepbar">
                <span>Cart</span>
                <span class="active">Payment</span>
                <span>Success</span>
            </div>

            <div class="checkout-grid">
                <section>
                    <article class="box">
                        <h3>Payment Method</h3>

                        <label class="radio-row">
                            <span><strong>Credit / Debit Card</strong><br><span class="muted-xs">Pay with encrypted and secure gateway</span></span>
                            <input type="radio" name="payment" checked>
                        </label>

                        <label class="radio-row alt">
                            <span><strong>PayPal</strong><br><span class="muted-xs">Pay with your PayPal balance or linked bank account</span></span>
                            <input type="radio" name="payment">
                        </label>

                        <form id="checkout-form">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="cardname">Cardholder Name</label>
                                    <input id="cardname" name="cardname" value="John Doe" required>
                                </div>
                                <div class="form-group">
                                    <label for="cardnumber">Card Number</label>
                                    <input id="cardnumber" name="cardnumber" value="0000 0000 0000 0000" required>
                                </div>
                            </div>

                            <div class="form-grid-2" style="margin-top: 8px;">
                                <div class="form-group">
                                    <label for="expiry">Expiry Date</label>
                                    <input id="expiry" name="expiry" placeholder="MM / YY" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input id="cvv" name="cvv" placeholder="123" required>
                                </div>
                            </div>
                        </form>
                    </article>

                    <article class="box">
                        <h3>Billing Address</h3>
                        <div class="radio-row alt" style="margin-bottom: 0;">
                            <span><strong>John Doe</strong><br><span class="muted-xs">123 Business Ave, Suite 456<br>San Francisco, CA 94107</span></span>
                            <button type="button" class="btn btn-light" style="padding: 4px 10px;">Edit</button>
                        </div>
                    </article>
                </section>

                <aside class="box">
                    <h3>Order Summary</h3>

                    <?php if (count($cart) > 0): ?>
                        <?php foreach ($cart as $item): ?>
                            <?php $line = ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)); ?>
                            <div class="summary-item">
                                <span><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong>$<?php echo number_format($line, 2); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="summary-item"><span>Premium Subscription</span><strong>$49.00</strong></div>
                        <div class="summary-item"><span>Add-on Feature Pack</span><strong>$15.00</strong></div>
                    <?php endif; ?>

                    <div style="margin-top: 10px; border-top: 1px solid var(--line); padding-top: 10px;">
                        <div class="summary-item"><span>Subtotal</span><span>$<?php echo number_format($subtotal > 0 ? $subtotal : 64.00, 2); ?></span></div>
                        <div class="summary-item"><span>Tax (8%)</span><span>$<?php echo number_format($subtotal > 0 ? $tax : 5.12, 2); ?></span></div>
                        <div class="summary-item"><span>Discount</span><span>-$10.00</span></div>
                    </div>

                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($subtotal > 0 ? $total - 10 : 69.12, 2); ?></span>
                    </div>

                    <button id="complete-btn" class="btn btn-dark" style="width: 100%; margin-top: 12px;">Complete Purchase</button>
                    <button id="back-btn" class="btn btn-ghost" style="width: 100%; margin-top: 8px;">Back to Cart</button>
                </aside>
            </div>
        </main>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>
