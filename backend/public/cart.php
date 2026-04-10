<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

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
    <title>Carrito | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="checkout-wrap">
        <aside class="checkout-side">
            <header class="top-nav" style="height: 46px; border: none; padding: 0; background: transparent; margin-bottom: 8px;">
                <a class="brand" href="home.php">DoDaqui</a>
                <div class="nav-grow"></div>
                <span class="muted-xs">Carrito / Envío / Pago</span>
            </header>

            <h2 style="font-size: 30px; line-height: 1.05; margin-bottom: 10px;">Tu carrito (<?php echo count($cart); ?>)</h2>

            <?php if (count($cart) === 0): ?>
                <div class="box" style="text-align: center;">
                    <p style="font-size: 48px;">Carrito</p>
                    <p class="section-sub">Tu carrito está vacío</p>
                    <a href="home.php" class="btn btn-dark" style="margin-top: 8px;">Empezar a comprar</a>
                </div>
            <?php else: ?>
                <?php
                $subtotal = 0;
                foreach ($cart as $item):
                    $qty = (int) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['price'] ?? 0);
                    $itemTotal = $unitPrice * $qty;
                    $subtotal += $itemTotal;
                ?>
                    <article class="box" style="display: grid; grid-template-columns: 42px 1fr auto; gap: 10px; align-items: center;">
                        <div class="placeholder" style="height: 42px;"></div>
                        <div>
                            <h4 style="font-size: 12px;"><?php echo htmlspecialchars($item['name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="muted-xs">Cant. <?php echo $qty; ?> · $<?php echo number_format($unitPrice, 2); ?></p>
                        </div>
                        <strong style="font-size: 12px;">$<?php echo number_format($itemTotal, 2); ?></strong>
                    </article>
                <?php endforeach; ?>

                <div class="box">
                    <div class="summary-item"><span>Subtotal</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
                    <div class="summary-item"><span>IVA (10%)</span><span>$<?php echo number_format($subtotal * 0.1, 2); ?></span></div>
                    <div class="summary-item"><span>Envío</span><span>$0.00</span></div>
                    <div class="summary-total" style="font-size: 20px;"><span>Total</span><span>$<?php echo number_format($subtotal * 1.1, 2); ?></span></div>
                    <a href="checkout.php" class="btn btn-dark" style="width: 100%; margin-top: 10px;">Continuar al pago</a>
                </div>
            <?php endif; ?>
        </aside>

        <section class="checkout-main" style="display: grid; place-items: center; min-height: 640px; background: #f7f8fa;">
            <div style="width: min(720px, 96%); border: 1px solid var(--line); border-radius: 10px; padding: 14px; background: #fff;">
                <div class="stepbar" style="justify-content: flex-start; margin-bottom: 8px;">
                    <span>Cart</span>
                    <span class="active">Pago</span>
                    <span>Confirmación</span>
                </div>
                <h3 style="font-size: 28px; margin-bottom: 8px;">Siguiente paso: detalles de pago</h3>
                <p class="section-sub" style="margin-bottom: 14px;">Pantalla de pago compacta y clara siguiendo el diseño del prototipo.</p>
                <a href="checkout.php" class="btn btn-dark">Abrir pantalla de pago</a>
            </div>
        </section>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>
