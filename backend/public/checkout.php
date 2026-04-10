<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();

if ($user === null) {
    header('Location: auth.php');
    exit;
}

function ensureCheckoutSchema(PDO $pdo): void
{
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

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS pedidos (
            id_pedido INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            estado_pedido VARCHAR(32) NOT NULL DEFAULT 'confirmado',
            metodo_pagamento VARCHAR(24) NOT NULL,
            importe_subtotal DECIMAL(10,2) NOT NULL,
            importe_ive DECIMAL(10,2) NOT NULL,
            importe_total DECIMAL(10,2) NOT NULL,
            nome_facturacion VARCHAR(120) NOT NULL,
            enderezo_facturacion VARCHAR(200) NOT NULL,
            cidade_facturacion VARCHAR(120) NOT NULL,
            codigo_postal_facturacion VARCHAR(20) NOT NULL,
            pais_facturacion VARCHAR(80) NOT NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_pedido_usuario
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
                ON DELETE CASCADE
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS pedido_linas (
            id_lina INT AUTO_INCREMENT PRIMARY KEY,
            id_pedido INT NOT NULL,
            id_produto VARCHAR(80) NOT NULL,
            nome_produto VARCHAR(150) NOT NULL,
            prezo_unitario DECIMAL(10,2) NOT NULL,
            cantidade INT NOT NULL,
            CONSTRAINT fk_lina_pedido
                FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
                ON DELETE CASCADE
        )"
    );
}

function obterCarriñoUsuario(PDO $pdo, int $idUsuario): array
{
    $stmt = $pdo->prepare(
        'SELECT id_produto, nome_produto, prezo_unitario, cantidade
         FROM carrito_items
         WHERE id_usuario = :id_usuario
         ORDER BY actualizado_en DESC, id_item DESC'
    );
    $stmt->execute(['id_usuario' => $idUsuario]);

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

    return $cart;
}

$pdo = db();
ensureCheckoutSchema($pdo);

$cart = obterCarriñoUsuario($pdo, (int) $user['id_usuario']);
$_SESSION['cart'] = $cart;

$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
}
$tax = $subtotal * 0.1;
$total = $subtotal + $tax;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'realizar_pedido') {
    header('Content-Type: application/json; charset=utf-8');

    if (count($cart) === 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'El carrito está vacío.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $metodoPagamento = (string) ($_POST['metodo_pagamento'] ?? 'tarxeta');
    $nomeFacturacion = trim((string) ($_POST['nome_facturacion'] ?? ''));
    $enderezoFacturacion = trim((string) ($_POST['enderezo_facturacion'] ?? ''));
    $cidadeFacturacion = trim((string) ($_POST['cidade_facturacion'] ?? ''));
    $codigoPostalFacturacion = trim((string) ($_POST['codigo_postal_facturacion'] ?? ''));
    $paisFacturacion = trim((string) ($_POST['pais_facturacion'] ?? ''));

    if (!in_array($metodoPagamento, ['tarxeta', 'paypal'], true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Método de pago no válido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($nomeFacturacion === '' || $enderezoFacturacion === '' || $cidadeFacturacion === '' || $codigoPostalFacturacion === '' || $paisFacturacion === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Completa la dirección de facturación.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($metodoPagamento === 'tarxeta') {
        $tarxetaNumero = preg_replace('/\s+/', '', (string) ($_POST['tarxeta_numero'] ?? ''));
        $tarxetaCaducidade = trim((string) ($_POST['tarxeta_caducidade'] ?? ''));
        $tarxetaCvv = trim((string) ($_POST['tarxeta_cvv'] ?? ''));

        if (strlen($tarxetaNumero) < 12 || $tarxetaCaducidade === '' || strlen($tarxetaCvv) < 3) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Datos de tarjeta de prueba incompletos.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    if ($metodoPagamento === 'paypal') {
        $paypalCorreo = trim((string) ($_POST['paypal_correo'] ?? ''));
        if ($paypalCorreo === '' || !filter_var($paypalCorreo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Introduce un correo válido de PayPal.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    try {
        $pdo->beginTransaction();

        $insertPedido = $pdo->prepare(
            'INSERT INTO pedidos (
                id_usuario, estado_pedido, metodo_pagamento, importe_subtotal, importe_ive, importe_total,
                nome_facturacion, enderezo_facturacion, cidade_facturacion, codigo_postal_facturacion, pais_facturacion
             ) VALUES (
                :id_usuario, :estado_pedido, :metodo_pagamento, :importe_subtotal, :importe_ive, :importe_total,
                :nome_facturacion, :enderezo_facturacion, :cidade_facturacion, :codigo_postal_facturacion, :pais_facturacion
             )'
        );

        $insertPedido->execute([
            'id_usuario' => (int) $user['id_usuario'],
            'estado_pedido' => 'confirmado',
            'metodo_pagamento' => $metodoPagamento,
            'importe_subtotal' => $subtotal,
            'importe_ive' => $tax,
            'importe_total' => $total,
            'nome_facturacion' => $nomeFacturacion,
            'enderezo_facturacion' => $enderezoFacturacion,
            'cidade_facturacion' => $cidadeFacturacion,
            'codigo_postal_facturacion' => $codigoPostalFacturacion,
            'pais_facturacion' => $paisFacturacion,
        ]);

        $idPedido = (int) $pdo->lastInsertId();

        $insertLina = $pdo->prepare(
            'INSERT INTO pedido_linas (id_pedido, id_produto, nome_produto, prezo_unitario, cantidade)
             VALUES (:id_pedido, :id_produto, :nome_produto, :prezo_unitario, :cantidade)'
        );

        foreach ($cart as $item) {
            $insertLina->execute([
                'id_pedido' => $idPedido,
                'id_produto' => (string) ($item['id'] ?? ''),
                'nome_produto' => (string) ($item['name'] ?? ''),
                'prezo_unitario' => (float) ($item['price'] ?? 0),
                'cantidade' => (int) ($item['quantity'] ?? 0),
            ]);
        }

        $clearCart = $pdo->prepare('DELETE FROM carrito_items WHERE id_usuario = :id_usuario');
        $clearCart->execute(['id_usuario' => (int) $user['id_usuario']]);

        $pdo->commit();

        $_SESSION['cart'] = [];

        echo json_encode([
            'ok' => true,
            'message' => 'Pedido realizado correctamente.',
            'id_pedido' => $idPedido,
            'total' => formatoEuro($total),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo completar el pedido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
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
    <div class="checkout-wrap checkout-single">
        <main class="checkout-main checkout-main-full">
            <div class="stepbar">
                <span>Carrito</span>
                <span class="active">Pago</span>
                <span>Confirmación</span>
            </div>

            <div class="checkout-grid">
                <section>
                    <article class="box">
                        <h3>Método de pago de prueba</h3>

                        <form id="checkout-form">
                            <label class="radio-row">
                                <span><strong>Tarjeta de crédito / débito</strong><br><span class="muted-xs">Prueba local sin pasarela real</span></span>
                                <input type="radio" name="metodo_pagamento" value="tarxeta" checked>
                            </label>

                            <label class="radio-row alt">
                                <span><strong>PayPal</strong><br><span class="muted-xs">Simulación con correo de PayPal</span></span>
                                <input type="radio" name="metodo_pagamento" value="paypal">
                            </label>

                            <div id="card-fields">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="tarxeta_titular">Titular de la tarjeta</label>
                                    <input id="tarxeta_titular" name="tarxeta_titular" value="Usuario Demo" required>
                                </div>
                                <div class="form-group">
                                    <label for="tarxeta_numero">Número de tarjeta</label>
                                    <input id="tarxeta_numero" name="tarxeta_numero" value="4242 4242 4242 4242" required>
                                </div>
                            </div>

                            <div class="form-grid-2" style="margin-top: 8px;">
                                <div class="form-group">
                                    <label for="tarxeta_caducidade">Fecha de caducidad</label>
                                    <input id="tarxeta_caducidade" name="tarxeta_caducidade" placeholder="MM / YY" value="12/30" required>
                                </div>
                                <div class="form-group">
                                    <label for="tarxeta_cvv">CVV</label>
                                    <input id="tarxeta_cvv" name="tarxeta_cvv" placeholder="123" value="123" required>
                                </div>
                            </div>
                            </div>

                            <div id="paypal-fields" hidden>
                                <div class="form-group" style="margin-top: 8px;">
                                    <label for="paypal_correo">Correo de PayPal</label>
                                    <input id="paypal_correo" name="paypal_correo" type="email" value="demo@paypal.test" disabled>
                                </div>
                            </div>
                        </form>
                    </article>

                    <article class="box">
                        <h3>Dirección de facturación</h3>
                        <div class="radio-row alt" style="margin-bottom: 8px; align-items: flex-start;">
                            <span>
                                <strong id="billing-summary-name"><?php echo safe((string) ($user['nome'] ?? 'Cliente')); ?></strong><br>
                                <span class="muted-xs" id="billing-summary-address">Rúa da Mostra 1, Santiago de Compostela, 15701, España</span>
                            </span>
                            <button type="button" id="billing-edit-btn" class="btn btn-light" style="padding: 4px 10px;">Editar</button>
                        </div>

                        <div id="billing-fields" hidden>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="nome_facturacion">Nombre completo</label>
                                    <input id="nome_facturacion" form="checkout-form" name="nome_facturacion" value="<?php echo safe((string) ($user['nome'] ?? '')); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="pais_facturacion">País</label>
                                    <input id="pais_facturacion" form="checkout-form" name="pais_facturacion" value="España" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 8px;">
                                <label for="enderezo_facturacion">Dirección</label>
                                <input id="enderezo_facturacion" form="checkout-form" name="enderezo_facturacion" value="Rúa da Mostra 1" required>
                            </div>
                            <div class="form-grid-2" style="margin-top: 8px;">
                                <div class="form-group">
                                    <label for="cidade_facturacion">Ciudad</label>
                                    <input id="cidade_facturacion" form="checkout-form" name="cidade_facturacion" value="Santiago de Compostela" required>
                                </div>
                                <div class="form-group">
                                    <label for="codigo_postal_facturacion">Código postal</label>
                                    <input id="codigo_postal_facturacion" form="checkout-form" name="codigo_postal_facturacion" value="15701" required>
                                </div>
                            </div>
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
                                <strong><?php echo formatoEuro($line); ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="summary-item"><span>No hay artículos en carrito</span><strong><?php echo formatoEuro(0); ?></strong></div>
                    <?php endif; ?>

                    <div style="margin-top: 10px; border-top: 1px solid var(--line); padding-top: 10px;">
                        <div class="summary-item"><span>Subtotal</span><span><?php echo formatoEuro($subtotal); ?></span></div>
                        <div class="summary-item"><span>IVA (10%)</span><span><?php echo formatoEuro($tax); ?></span></div>
                        <div class="summary-item"><span>Descuento</span><span><?php echo formatoEuro(0); ?></span></div>
                    </div>

                    <div class="summary-total">
                        <span>Total</span>
                        <span><?php echo formatoEuro($total); ?></span>
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
