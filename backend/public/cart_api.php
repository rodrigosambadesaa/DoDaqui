<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders(true);

$action = $_GET['action'] ?? '';
$user = currentUser();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function ensureCartTable(PDO $pdo): void
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
}

function getDbCartCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(cantidade), 0) AS total FROM carrito_items WHERE id_usuario = :id_usuario');
    $stmt->execute(['id_usuario' => $userId]);
    $row = $stmt->fetch();

    return (int) ($row['total'] ?? 0);
}

function getSessionCartCount(array $cart): int
{
    $count = 0;
    foreach ($cart as $item) {
        $count += (int) ($item['quantity'] ?? 0);
    }

    return $count;
}

if ($action === 'count') {
    if ($user === null) {
        echo json_encode(['count' => 0], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $pdo = db();
        ensureCartTable($pdo);
        $count = getDbCartCount($pdo, (int) $user['id_usuario']);
        echo json_encode(['count' => $count], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $exception) {
        // fallback a sesion
    }

    echo json_encode(['count' => getSessionCartCount($_SESSION['cart'])], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($user === null) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Debes iniciar sesión.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'clear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    requireValidCsrfTokenJson((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

    try {
        $pdo = db();
        ensureCartTable($pdo);
        $delete = $pdo->prepare('DELETE FROM carrito_items WHERE id_usuario = :id_usuario');
        $delete->execute(['id_usuario' => (int) $user['id_usuario']]);
    } catch (Throwable $exception) {
        // fallback a sesion
    }

    $_SESSION['cart'] = [];
    echo json_encode(['ok' => true, 'count' => 0], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfTokenJson((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '[]', true);

    $id = (string) ($payload['id'] ?? '');
    $name = mb_substr(trim((string) ($payload['name'] ?? 'Producto')), 0, 150);
    $price = (float) ($payload['price'] ?? 0);

    if (!preg_match('/^[a-zA-Z0-9-]{1,80}$/', $id) || $name === '' || $price < 0 || $price > 100000) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Datos de producto inválidos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $pdo = db();
        ensureCartTable($pdo);

        $upsert = $pdo->prepare(
            'INSERT INTO carrito_items (id_usuario, id_produto, nome_produto, prezo_unitario, cantidade)
             VALUES (:id_usuario, :id_produto, :nome_produto, :prezo_unitario, 1)
             ON DUPLICATE KEY UPDATE cantidade = cantidade + 1, nome_produto = VALUES(nome_produto), prezo_unitario = VALUES(prezo_unitario)'
        );

        $upsert->execute([
            'id_usuario' => (int) $user['id_usuario'],
            'id_produto' => $id,
            'nome_produto' => $name,
            'prezo_unitario' => $price,
        ]);

        $count = getDbCartCount($pdo, (int) $user['id_usuario']);

        echo json_encode([
            'ok' => true,
            'count' => $count,
            'item' => [
                'id' => $id,
                'name' => $name,
                'price' => $price,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $exception) {
        // fallback a sesion
    }

    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => 1,
        ];
    } else {
        $_SESSION['cart'][$id]['quantity']++;
    }

    echo json_encode([
        'ok' => true,
        'count' => getSessionCartCount($_SESSION['cart']),
        'item' => $_SESSION['cart'][$id],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfTokenJson((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '[]', true);

    $id = (string) ($payload['id'] ?? '');
    $delta = (int) ($payload['delta'] ?? 0);

    if (!preg_match('/^[a-zA-Z0-9-]{1,80}$/', $id) || !in_array($delta, [-1, 1], true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Actualización inválida.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $pdo = db();
        ensureCartTable($pdo);

        $update = $pdo->prepare(
            'UPDATE carrito_items
             SET cantidade = cantidade + :delta
             WHERE id_usuario = :id_usuario AND id_produto = :id_produto'
        );

        $update->execute([
            'delta' => $delta,
            'id_usuario' => (int) $user['id_usuario'],
            'id_produto' => $id,
        ]);

        $delete = $pdo->prepare('DELETE FROM carrito_items WHERE id_usuario = :id_usuario AND id_produto = :id_produto AND cantidade <= 0');
        $delete->execute([
            'id_usuario' => (int) $user['id_usuario'],
            'id_produto' => $id,
        ]);

        $fetch = $pdo->prepare('SELECT cantidade FROM carrito_items WHERE id_usuario = :id_usuario AND id_produto = :id_produto');
        $fetch->execute([
            'id_usuario' => (int) $user['id_usuario'],
            'id_produto' => $id,
        ]);
        $row = $fetch->fetch();

        echo json_encode([
            'ok' => true,
            'quantity' => (int) ($row['cantidade'] ?? 0),
            'count' => getDbCartCount($pdo, (int) $user['id_usuario']),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $exception) {
        // fallback a sesion
    }

    if (!isset($_SESSION['cart'][$id])) {
        echo json_encode(['ok' => true, 'quantity' => 0, 'count' => getSessionCartCount($_SESSION['cart'])], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $_SESSION['cart'][$id]['quantity'] = (int) ($_SESSION['cart'][$id]['quantity'] ?? 0) + $delta;
    if ((int) $_SESSION['cart'][$id]['quantity'] <= 0) {
        unset($_SESSION['cart'][$id]);
        echo json_encode(['ok' => true, 'quantity' => 0, 'count' => getSessionCartCount($_SESSION['cart'])], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'quantity' => (int) $_SESSION['cart'][$id]['quantity'],
        'count' => getSessionCartCount($_SESSION['cart']),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
echo json_encode(['ok' => false, 'message' => 'Acción no soportada.'], JSON_UNESCAPED_UNICODE);
