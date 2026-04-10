<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function ensureCartTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS carrito_items (
            id_item INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            product_id VARCHAR(80) NOT NULL,
            name VARCHAR(150) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_product (id_usuario, product_id)
        )"
    );
}

function getDbCartCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM carrito_items WHERE id_usuario = :id_usuario');
    $stmt->execute(['id_usuario' => $userId]);
    $row = $stmt->fetch();

    return (int) ($row['total'] ?? 0);
}

if ($action === 'count') {
    $user = currentUser();

    if ($user !== null) {
        try {
            $pdo = db();
            ensureCartTable($pdo);
            $count = getDbCartCount($pdo, (int) $user['id_usuario']);
            echo json_encode(['count' => $count], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Throwable $exception) {
            // fallback a sesion
        }
    }

    echo json_encode(['count' => count($_SESSION['cart'])], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'clear') {
    $user = currentUser();
    if ($user !== null) {
        try {
            $pdo = db();
            ensureCartTable($pdo);
            $delete = $pdo->prepare('DELETE FROM carrito_items WHERE id_usuario = :id_usuario');
            $delete->execute(['id_usuario' => (int) $user['id_usuario']]);
        } catch (Throwable $exception) {
            // fallback a sesion
        }
    }

    $_SESSION['cart'] = [];
    echo json_encode(['ok' => true, 'count' => 0], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '[]', true);

    $id = (string) ($payload['id'] ?? '');
    $name = trim((string) ($payload['name'] ?? 'Producto'));
    $price = (float) ($payload['price'] ?? 0);

    if ($id === '' || $price < 0) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => 'Datos de producto inválidos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $user = currentUser();

    if ($user !== null) {
        try {
            $pdo = db();
            ensureCartTable($pdo);

            $upsert = $pdo->prepare(
                'INSERT INTO carrito_items (id_usuario, product_id, name, price, quantity)
                 VALUES (:id_usuario, :product_id, :name, :price, 1)
                 ON DUPLICATE KEY UPDATE quantity = quantity + 1, name = VALUES(name), price = VALUES(price)'
            );

            $upsert->execute([
                'id_usuario' => (int) $user['id_usuario'],
                'product_id' => $id,
                'name' => $name,
                'price' => $price,
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
        'count' => count($_SESSION['cart']),
        'item' => $_SESSION['cart'][$id],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(404);
echo json_encode(['ok' => false, 'message' => 'Acción no soportada.'], JSON_UNESCAPED_UNICODE);
