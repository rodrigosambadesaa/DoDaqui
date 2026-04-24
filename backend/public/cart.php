<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

$user = currentUser();
$cart = $_SESSION['cart'] ?? [];

if ($user !== null) {
    try {
        $pdo = db();
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS carrito_items (
                id_item INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                id_produto VARCHAR(80) NOT NULL,
                nome_produto VARCHAR(150) NOT NULL,
                prezo_unitario DECIMAL(10,2) NOT NULL,
                cantidade INT NOT NULL DEFAULT 1,
                actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_product (id_usuario, id_produto)
            )"
        );

        $columnCheck = $pdo->query('SHOW COLUMNS FROM carrito_items');
        $columns = array_column($columnCheck->fetchAll(), 'Field');

        if (in_array('product_id', $columns, true) && !in_array('id_produto', $columns, true)) {
            $pdo->exec('ALTER TABLE carrito_items CHANGE COLUMN product_id id_produto VARCHAR(80) NOT NULL');
        }

        if (in_array('name', $columns, true) && !in_array('nome_produto', $columns, true)) {
            $pdo->exec('ALTER TABLE carrito_items CHANGE COLUMN name nome_produto VARCHAR(150) NOT NULL');
        }

        if (in_array('price', $columns, true) && !in_array('prezo_unitario', $columns, true)) {
            $pdo->exec('ALTER TABLE carrito_items CHANGE COLUMN price prezo_unitario DECIMAL(10,2) NOT NULL');
        }

        if (in_array('quantity', $columns, true) && !in_array('cantidade', $columns, true)) {
            $pdo->exec('ALTER TABLE carrito_items CHANGE COLUMN quantity cantidade INT NOT NULL DEFAULT 1');
        }

        $stmt = $pdo->prepare(
            'SELECT id_produto, nome_produto, prezo_unitario, cantidade
             FROM carrito_items
             WHERE id_usuario = :id_usuario
             ORDER BY actualizado_en DESC, id_item DESC'
        );
        $stmt->execute(['id_usuario' => (int) $user['id_usuario']]);

        $cart = [];
        foreach (($stmt->fetchAll() ?: []) as $row) {
            $id = (string) ($row['id_produto'] ?? '');
            if ($id === '') {
                continue;
            }

            $cart[$id] = [
                'id' => $id,
                'name' => (string) ($row['nome_produto'] ?? ''),
                'price' => (float) ($row['prezo_unitario'] ?? 0),
                'quantity' => (int) ($row['cantidade'] ?? 0),
            ];
        }

        // Mantiene el resto del flujo compatible con sesion.
        $_SESSION['cart'] = $cart;
    } catch (Throwable $exception) {
        // Si falla DB, seguimos usando la sesion actual.
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo safe(csrfToken()); ?>">
    <title>Carrito | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <?php
    $subtotal = 0.0;
    foreach ($cart as $item) {
        $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
    }
    $tax = $subtotal * 0.21;
    $shipping = count($cart) > 0 ? 0.0 : 0.0;
    $total = $subtotal + $tax + $shipping;
    ?>

    <div class="page-wrap">
        <div class="desktop-shell">
            <header class="top-nav">
                <a class="brand" href="/home.php">ShopFlow</a>
                <nav class="nav-links desktop-only">
                    <a href="/products.php">Producto</a>
                    <a href="/cart.php" class="is-active">Carrito</a>
                    <a href="#">Pedidos</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <?php if ($user === null): ?>
                        <a class="login-link" href="/auth.php">Iniciar sesión</a>
                    <?php else: ?>
                        <span class="avatar-mini" aria-hidden="true"></span>
                        <span class="muted-xs"><?php echo safe((string) ($user['nome'] ?? 'Usuario')); ?></span>
                        <a href="/logout.php">Salir</a>
                    <?php endif; ?>
                    <a href="/cart.php" aria-label="Carrito">Carrito</a>
                    <span class="badge-count" id="cart-count">0</span>
                </div>
            </header>

            <main class="store-main cart-layout">
                <section>
                    <div class="cart-header-row">
                        <div>
                            <h1 class="cart-title">Mi Carrito</h1>
                            <p class="section-sub">Revisa tus productos y completa los datos de envío para finalizar.</p>
                        </div>
                        <a class="btn btn-light" href="/products.php">Seguir comprando</a>
                    </div>

                    <article class="box cart-box">
                        <h3>Productos en el carrito</h3>

                        <?php if (count($cart) === 0): ?>
                            <p class="section-sub">Tu carrito está vacío. Añade productos para continuar.</p>
                        <?php else: ?>
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $item): ?>
                                        <?php
                                        $qty = (int) ($item['quantity'] ?? 1);
                                        $unitPrice = (float) ($item['price'] ?? 0);
                                        $lineTotal = $qty * $unitPrice;
                                        ?>
                                        <tr data-product-id="<?php echo safe((string) ($item['id'] ?? '')); ?>">
                                            <td>
                                                <div class="cart-product-cell">
                                                    <span class="cart-thumb placeholder" aria-hidden="true"></span>
                                                    <div>
                                                        <strong><?php echo safe((string) ($item['name'] ?? 'Producto')); ?></strong>
                                                        <p class="muted-xs">Local</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="qty-controls">
                                                    <button type="button" class="qty-btn" data-action="minus">−</button>
                                                    <span class="qty-value"><?php echo $qty; ?></span>
                                                    <button type="button" class="qty-btn" data-action="plus">+</button>
                                                </div>
                                            </td>
                                            <td><?php echo formatoEuro($unitPrice); ?></td>
                                            <td><strong><?php echo formatoEuro($lineTotal); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </article>

                    <article class="box cart-box">
                        <h3>Datos de envío</h3>
                        <form id="shipping-form">
                            <?php echo csrfInput(); ?>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="nome_facturacion">Nombre</label>
                                    <input id="nome_facturacion" name="nome_facturacion" value="<?php echo safe((string) ($user['nome'] ?? '')); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="correo_cliente">Correo</label>
                                    <input id="correo_cliente" name="correo_cliente" type="email" value="" placeholder="ana.garcia@example.com" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 8px;">
                                <label for="telefono_cliente">Teléfono</label>
                                <input
                                    id="telefono_cliente"
                                    name="telefono_cliente"
                                    type="tel"
                                    inputmode="tel"
                                    value="<?php echo safe((string) ($user['telefono'] ?? '')); ?>"
                                    placeholder="+34 600 000 000"
                                    minlength="9"
                                    maxlength="30"
                                    required>
                            </div>
                            <div class="form-group" style="margin-top: 8px;">
                                <label for="enderezo_facturacion">Dirección de envío</label>
                                <input id="enderezo_facturacion" name="enderezo_facturacion" placeholder="Calle, número, piso, puerta" required>
                            </div>
                            <div class="form-grid-2" style="margin-top: 8px;">
                                <div class="form-group">
                                    <label for="cidade_facturacion">Ciudad</label>
                                    <input id="cidade_facturacion" name="cidade_facturacion" value="Madrid" required>
                                </div>
                                <div class="form-group">
                                    <label for="codigo_postal_facturacion">Código</label>
                                    <input id="codigo_postal_facturacion" name="codigo_postal_facturacion" value="28001" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 8px;">
                                <label for="observacions">Notas del pedido</label>
                                <input id="observacions" name="observacions" placeholder="Instrucciones especiales para el repartidor...">
                            </div>
                            <input type="hidden" name="pais_facturacion" value="España">
                        </form>
                    </article>
                </section>

                <aside class="box cart-summary-box">
                    <h3>Resumen del Pedido</h3>
                    <div class="summary-item"><span>Subtotal</span><strong><?php echo formatoEuro($subtotal); ?></strong></div>
                    <div class="summary-item"><span>Envío</span><strong class="ok-text">Gratis</strong></div>
                    <div class="summary-item"><span>Impuestos</span><strong><?php echo formatoEuro($tax); ?></strong></div>

                    <div class="summary-total compact">
                        <span>Total</span>
                        <span><?php echo formatoEuro($total); ?></span>
                    </div>

                    <div class="coupon-row">
                        <input type="text" placeholder="Código de descuento" aria-label="Código de descuento">
                        <button type="button" class="btn btn-light">Aplicar</button>
                    </div>

                    <button id="complete-btn" class="btn btn-dark" style="width: 100%; margin-top: 12px;" <?php echo count($cart) === 0 ? 'disabled' : ''; ?>>Completar compra</button>
                    <p class="security-note">Pago seguro procesado por la plataforma.</p>
                </aside>
            </main>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>