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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="checkout-wrap">
        <aside class="checkout-side">
            <header class="top-nav" style="height: 44px; border: none; padding: 0; background: transparent; margin-bottom: 8px;">
                <a class="brand" href="home.php">DoDaqui</a>
                <div class="nav-grow"></div>
                <span class="muted-xs">Tienda</span>
            </header>

            <h3 style="font-size: 24px; margin-bottom: 10px;">Tu carrito (<?php echo count($cart); ?>)</h3>

            <?php if (count($cart) > 0): ?>
                <?php foreach ($cart as $item): ?>
                    <div class="box" style="display: grid; grid-template-columns: 38px 1fr auto; gap: 8px; align-items: center;">
                        <div class="placeholder" style="height: 36px;"></div>
                        <div>
                            <strong style="font-size: 11px;"><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></strong>
                            <p class="muted-xs">Cant. <?php echo (int) ($item['quantity'] ?? 1); ?></p>
                        </div>
                        <span style="font-size: 11px;">$<?php echo number_format((float) ($item['price'] ?? 0), 2); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="box">
                    <p class="muted-xs">No hay artículos en el carrito. Este panel conserva la estructura visual del checkout móvil.</p>
                </div>
            <?php endif; ?>

            <div class="box">
                <h4 style="font-size: 13px; margin-bottom: 8px;">Método de pago</h4>
                <div class="radio-row"><span>Tarjeta</span><span>◉</span></div>
                <div class="radio-row alt"><span>Pix</span><span>◌</span></div>
                <div class="radio-row alt" style="margin-bottom: 0;"><span>Transferencia</span><span>◌</span></div>
                <button class="btn btn-dark" style="width: 100%; margin-top: 10px;">Confirmar</button>
            </div>
        </aside>

        <main class="checkout-main">
            <div class="stepbar">
                <span>Carrito</span>
                <span class="active">Pago</span>
                <span>Confirmación</span>
            </div>

            <div class="checkout-grid">
                <section>
                    <article class="box">
                        <h3>Método de pago</h3>

                        <label class="radio-row">
                            <span><strong>Tarjeta de crédito / débito</strong><br><span class="muted-xs">Pago cifrado y seguro</span></span>
                            <input type="radio" name="payment" checked>
                        </label>

                        <label class="radio-row alt">
                            <span><strong>PayPal</strong><br><span class="muted-xs">Paga con saldo PayPal o cuenta bancaria vinculada</span></span>
                            <input type="radio" name="payment">
                        </label>

                        <form id="checkout-form">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="cardname">Titular de la tarjeta</label>
                                    <input id="cardname" name="cardname" value="John Doe" required>
                                </div>
                                <div class="form-group">
                                    <label for="cardnumber">Número de tarjeta</label>
                                    <input id="cardnumber" name="cardnumber" value="0000 0000 0000 0000" required>
                                </div>
                            </div>

                            <div class="form-grid-2" style="margin-top: 8px;">
                                <div class="form-group">
                                    <label for="expiry">Fecha de caducidad</label>
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
                        <h3>Dirección de facturación</h3>
                        <div class="radio-row alt" style="margin-bottom: 0;">
                            <span><strong>John Doe</strong><br><span class="muted-xs">123 Business Ave, Suite 456<br>San Francisco, CA 94107</span></span>
                            <button type="button" class="btn btn-light" style="padding: 4px 10px;">Edit</button>
                        </div>
                    </article>
                </section>

                <aside class="box">
                    <h3>Resumen del pedido</h3>

                    <?php if (count($cart) > 0): ?>
                        <?php foreach ($cart as $item): ?>
                            <?php $line = ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1)); ?>
                            <div class="summary-item">
                                <span><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong>$<?php echo number_format($line, 2); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="summary-item"><span>Suscripción premium</span><strong>$49.00</strong></div>
                        <div class="summary-item"><span>Pack de funcionalidades</span><strong>$15.00</strong></div>
                    <?php endif; ?>

                    <div style="margin-top: 10px; border-top: 1px solid var(--line); padding-top: 10px;">
                        <div class="summary-item"><span>Subtotal</span><span>$<?php echo number_format($subtotal > 0 ? $subtotal : 64.00, 2); ?></span></div>
                        <div class="summary-item"><span>Impuestos (8%)</span><span>$<?php echo number_format($subtotal > 0 ? $tax : 5.12, 2); ?></span></div>
                        <div class="summary-item"><span>Descuento</span><span>-$10.00</span></div>
                    </div>

                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($subtotal > 0 ? $total - 10 : 69.12, 2); ?></span>
                    </div>

                    <button id="complete-btn" class="btn btn-dark" style="width: 100%; margin-top: 12px;">Completar compra</button>
                    <button id="back-btn" class="btn btn-ghost" style="width: 100%; margin-top: 8px;">Volver al carrito</button>

                    <p class="security-note">Pago seguro · Protección al comprador incluida</p>
                </aside>
            </div>

            <footer class="checkout-footer-line">
                <span>Términos</span>
                <span>Privacidad</span>
                <span>Soporte</span>
            </footer>
        </main>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>
