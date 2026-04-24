<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

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
            metodo_pagamento VARCHAR(24) NOT NULL DEFAULT 'sin_pasarela',
            importe_subtotal DECIMAL(10,2) NOT NULL,
            importe_ive DECIMAL(10,2) NOT NULL,
            importe_envio DECIMAL(10,2) NOT NULL DEFAULT 0,
            importe_total DECIMAL(10,2) NOT NULL,
            nome_envio VARCHAR(120) NOT NULL,
            correo_envio VARCHAR(160) NOT NULL,
            telefono_envio VARCHAR(30) NOT NULL,
            enderezo_envio VARCHAR(200) NOT NULL,
            cidade_envio VARCHAR(120) NOT NULL,
            codigo_postal_envio VARCHAR(20) NOT NULL,
            pais_envio VARCHAR(80) NOT NULL,
            notas_envio VARCHAR(255) NULL,
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_pedido_usuario
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
                ON DELETE CASCADE
        )"
    );

    $columnsResult = $pdo->query('SHOW COLUMNS FROM pedidos');
    $columns = array_column($columnsResult ? $columnsResult->fetchAll() : [], 'Field');

    if (!in_array('importe_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN importe_envio DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER importe_ive');
    }

    if (!in_array('nome_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN nome_envio VARCHAR(120) NOT NULL AFTER importe_total');
    }

    if (!in_array('correo_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN correo_envio VARCHAR(160) NOT NULL AFTER nome_envio');
    }

    if (!in_array('telefono_envio', $columns, true)) {
        $pdo->exec("ALTER TABLE pedidos ADD COLUMN telefono_envio VARCHAR(30) NOT NULL DEFAULT '' AFTER correo_envio");
    }

    if (!in_array('enderezo_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN enderezo_envio VARCHAR(200) NOT NULL AFTER telefono_envio');
    }

    if (!in_array('cidade_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN cidade_envio VARCHAR(120) NOT NULL AFTER enderezo_envio');
    }

    if (!in_array('codigo_postal_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN codigo_postal_envio VARCHAR(20) NOT NULL AFTER cidade_envio');
    }

    if (!in_array('pais_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN pais_envio VARCHAR(80) NOT NULL AFTER codigo_postal_envio');
    }

    if (!in_array('notas_envio', $columns, true)) {
        $pdo->exec('ALTER TABLE pedidos ADD COLUMN notas_envio VARCHAR(255) NULL AFTER pais_envio');
    }

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

function ensureCheckoutUsersTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(120) NOT NULL,
            correo_electronico VARCHAR(160) NOT NULL UNIQUE,
            telefono VARCHAR(30) NULL,
            contrasinal VARCHAR(255) NOT NULL,
            rol_usuario ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );
}

function resolveCheckoutUserId(PDO $pdo, array $user): int
{
    $candidateId = (int) ($user['id_usuario'] ?? 0);

    if ($candidateId > 0) {
        $byId = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1');
        $byId->execute(['id_usuario' => $candidateId]);
        if ($byId->fetch()) {
            return $candidateId;
        }
    }

    $email = trim((string) ($user['email'] ?? ''));
    if ($email !== '') {
        $byEmail = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE correo_electronico = :correo LIMIT 1');
        $byEmail->execute(['correo' => $email]);
        $row = $byEmail->fetch();
        if (is_array($row)) {
            return (int) ($row['id_usuario'] ?? 0);
        }
    }

    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nome, correo_electronico, telefono, contrasinal, rol_usuario)
         VALUES (:nome, :correo_electronico, :telefono, :contrasinal, :rol_usuario)'
    );
    $insert->execute([
        'nome' => mb_substr(trim((string) ($user['nome'] ?? 'Usuario')), 0, 120),
        'correo_electronico' => mb_substr($email !== '' ? $email : 'guest-' . bin2hex(random_bytes(6)) . '@local.invalid', 0, 160),
        'telefono' => mb_substr(trim((string) ($user['telefono'] ?? '')), 0, 30),
        'contrasinal' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
        'rol_usuario' => 'cliente',
    ]);

    return (int) $pdo->lastInsertId();
}

function obterCarrinhoUsuario(PDO $pdo, int $idUsuario): array
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (string) ($_POST['action'] ?? '') !== 'realizar_pedido') {
    header('Location: cart.php');
    exit;
}

requireValidCsrfToken((string) ($_POST['csrf_token'] ?? ''));

applySecurityHeaders(true);

$dbCheckoutAvailable = true;
try {
    $pdo = db();
    ensureCheckoutUsersTable($pdo);
    ensureCheckoutSchema($pdo);
    $checkoutUserId = resolveCheckoutUserId($pdo, $user);
} catch (Throwable $exception) {
    $dbCheckoutAvailable = false;
    $checkoutUserId = (int) ($user['id_usuario'] ?? 0);
}

$sessionCart = is_array($_SESSION['cart'] ?? null) ? $_SESSION['cart'] : [];
$cookieCart = fallbackCartFromCookie();
if (count($sessionCart) === 0 && count($cookieCart) > 0) {
    $sessionCart = $cookieCart;
    $_SESSION['cart'] = $sessionCart;
}
$cart = $dbCheckoutAvailable
    ? obterCarrinhoUsuario($pdo, $checkoutUserId)
    : $sessionCart;

if ($dbCheckoutAvailable && count($cart) === 0 && count($sessionCart) > 0) {
    $cart = $sessionCart;
}
if (count($cart) === 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'El carrito está vacío.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$nomeEnvio = trim((string) ($_POST['nome_facturacion'] ?? ''));
$correoEnvio = trim((string) ($_POST['correo_cliente'] ?? ''));
$telefonoEnvio = trim((string) ($_POST['telefono_cliente'] ?? ''));
$enderezoEnvio = trim((string) ($_POST['enderezo_facturacion'] ?? ''));
$cidadeEnvio = trim((string) ($_POST['cidade_facturacion'] ?? ''));
$codigoPostalEnvio = trim((string) ($_POST['codigo_postal_facturacion'] ?? ''));
$paisEnvio = trim((string) ($_POST['pais_facturacion'] ?? 'España'));
$notasEnvio = trim((string) ($_POST['observacions'] ?? ''));

$nomeEnvio = mb_substr($nomeEnvio, 0, 120);
$correoEnvio = mb_substr($correoEnvio, 0, 160);
$enderezoEnvio = mb_substr($enderezoEnvio, 0, 200);
$cidadeEnvio = mb_substr($cidadeEnvio, 0, 120);
$codigoPostalEnvio = mb_substr($codigoPostalEnvio, 0, 20);
$paisEnvio = mb_substr($paisEnvio, 0, 80);
$notasEnvio = mb_substr($notasEnvio, 0, 255);

if ($nomeEnvio === '' || $correoEnvio === '' || $telefonoEnvio === '' || $enderezoEnvio === '' || $cidadeEnvio === '' || $codigoPostalEnvio === '' || $paisEnvio === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Completa los datos de envío.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($correoEnvio, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Introduce un correo válido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^[\p{L}\s\'-]{2,120}$/u', $nomeEnvio)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Introduce un nombre válido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^[\p{L}\d\s,.-]{3,20}$/u', $codigoPostalEnvio)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Código postal no válido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$telefonoNormalizado = preg_replace('/[^\d+\s()-]/', '', $telefonoEnvio);
$telefonoNormalizado = preg_replace('/\s+/', ' ', trim((string) $telefonoNormalizado));
$soloDigitos = preg_replace('/\D+/', '', $telefonoNormalizado);

if (strlen((string) $soloDigitos) < 9) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Introduce un teléfono válido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
}
$tax = $subtotal * 0.21;
$shipping = 0.0;
$total = $subtotal + $tax + $shipping;

if (!$dbCheckoutAvailable) {
    $_SESSION['cart'] = [];
    clearFallbackCartCookie();

    echo json_encode([
        'ok' => true,
        'message' => 'Pedido recibido correctamente.',
        'id_pedido' => 'TMP-' . (string) time(),
        'total' => formatoEuro($total),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    $insertPedido = $pdo->prepare(
        'INSERT INTO pedidos (
            id_usuario, estado_pedido, metodo_pagamento, importe_subtotal, importe_ive, importe_envio, importe_total,
                nome_envio, correo_envio, telefono_envio, enderezo_envio, cidade_envio, codigo_postal_envio, pais_envio, notas_envio
         ) VALUES (
            :id_usuario, :estado_pedido, :metodo_pagamento, :importe_subtotal, :importe_ive, :importe_envio, :importe_total,
                :nome_envio, :correo_envio, :telefono_envio, :enderezo_envio, :cidade_envio, :codigo_postal_envio, :pais_envio, :notas_envio
         )'
    );

    $insertPedido->execute([
        'id_usuario' => $checkoutUserId,
        'estado_pedido' => 'confirmado',
        'metodo_pagamento' => 'sin_pasarela',
        'importe_subtotal' => $subtotal,
        'importe_ive' => $tax,
        'importe_envio' => $shipping,
        'importe_total' => $total,
        'nome_envio' => $nomeEnvio,
        'correo_envio' => $correoEnvio,
        'telefono_envio' => $telefonoNormalizado,
        'enderezo_envio' => $enderezoEnvio,
        'cidade_envio' => $cidadeEnvio,
        'codigo_postal_envio' => $codigoPostalEnvio,
        'pais_envio' => $paisEnvio,
        'notas_envio' => $notasEnvio !== '' ? $notasEnvio : null,
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
    $clearCart->execute(['id_usuario' => $checkoutUserId]);

    $pdo->commit();

    $_SESSION['cart'] = [];
    clearFallbackCartCookie();

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
