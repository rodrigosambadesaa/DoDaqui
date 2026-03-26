<?php

declare(strict_types=1);

use App\Database;
use App\Response;

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Response.php';

// CORS abierto para facilitar pruebas desde frontend desacoplado en otro puerto.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

// Respuesta rápida a preflight para que el navegador autorice llamadas AJAX.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function request_body(): array
{
    // Lee body crudo y fuerza objeto vacío si no hay payload.
    $raw = file_get_contents('php://input') ?: '{}';
    $data = json_decode($raw, true);
    // Si el JSON no es válido devolvemos array vacío para que validen los endpoints.
    return is_array($data) ? $data : [];
}

function active_cart_id(\PDO $pdo, int $userId): int
{
    // Reutiliza carrito activo por usuario para no crear carros duplicados.
    $stmt = $pdo->prepare('SELECT id_carro FROM carros WHERE id_usuario = :user_id AND estado = "activo" LIMIT 1');
    $stmt->execute(['user_id' => $userId]);
    $cart = $stmt->fetch();

    if ($cart) {
        return (int) $cart['id_carro'];
    }

    // Si no existe, crea automáticamente un carrito activo nuevo.
    $insert = $pdo->prepare('INSERT INTO carros (id_usuario, estado) VALUES (:user_id, "activo")');
    $insert->execute(['user_id' => $userId]);
    return (int) $pdo->lastInsertId();
}

// Parseo básico de URI para routing manual por segmentos.
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$uri = trim($uriPath, '/');
$segments = $uri === '' ? [] : explode('/', $uri);

// Si no entra por /api, respondemos mensaje simple de servicio activo.
if (($segments[0] ?? '') !== 'api') {
    Response::json(['message' => 'API Tenda DoDaqui activa'], 200);
    exit;
}

// Inicialización de conexión BD y método HTTP actual.
$db = new Database();
$pdo = $db->pdo();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Healthcheck para validar disponibilidad del backend desde Docker o monitorización.
    if ($method === 'GET' && ($segments[1] ?? '') === 'health') {
        Response::json(['status' => 'ok', 'service' => 'backend']);
        exit;
    }

    // Registro de nuevo usuario con validación de campos y unicidad de email.
    if ($method === 'POST' && ($segments[1] ?? '') === 'register') {
        $body = request_body();
        $name = trim((string) ($body['nome'] ?? ''));
        $email = trim((string) ($body['email'] ?? ''));
        $password = (string) ($body['contrasinal'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            Response::json(['error' => 'Faltan campos obrigatorios'], 422);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['error' => 'Formato de email non valido'], 422);
            exit;
        }

        if (strlen($password) < 6) {
            Response::json(['error' => 'A contrasinal debe ter polo menos 6 caracteres'], 422);
            exit;
        }

        $existsStmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1');
        $existsStmt->execute(['email' => $email]);
        if ($existsStmt->fetch()) {
            Response::json(['error' => 'Xa existe unha conta con ese email'], 409);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, contrasinal_hash, rol) VALUES (:nome, :email, :hash, "cliente")');
        try {
            $stmt->execute([
                'nome' => $name,
                'email' => $email,
                'hash' => $hash,
            ]);
        } catch (\PDOException $exception) {
            if ($exception->getCode() === '23000') {
                Response::json(['error' => 'Xa existe unha conta con ese email'], 409);
                exit;
            }
            throw $exception;
        }

        $userId = (int) $pdo->lastInsertId();
        active_cart_id($pdo, $userId);

        Response::json(['message' => 'Usuario rexistrado', 'user_id' => $userId], 201);
        exit;
    }

    // Recuperación de contraseña simplificada para entorno de prototipo.
    if ($method === 'POST' && ($segments[1] ?? '') === 'password' && ($segments[2] ?? '') === 'recover') {
        $body = request_body();
        $email = trim((string) ($body['email'] ?? ''));
        $newPassword = (string) ($body['new_password'] ?? '');

        if ($email === '' || $newPassword === '') {
            Response::json(['error' => 'email e new_password son obrigatorios'], 422);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['error' => 'Formato de email non valido'], 422);
            exit;
        }

        if (strlen($newPassword) < 6) {
            Response::json(['error' => 'A nova contrasinal debe ter polo menos 6 caracteres'], 422);
            exit;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE usuarios SET contrasinal_hash = :hash WHERE email = :email');
        $stmt->execute([
            'hash' => $hash,
            'email' => $email,
        ]);

        Response::json([
            'message' => 'Se o email existe, a contrasinal foi actualizada correctamente.'
        ], 200);
        exit;
    }

    // Login por email + contraseña hash (bcrypt).
    if ($method === 'POST' && ($segments[1] ?? '') === 'login') {
        $body = request_body();
        $email = trim((string) ($body['email'] ?? ''));
        $password = (string) ($body['contrasinal'] ?? '');

        $stmt = $pdo->prepare('SELECT id_usuario, nome, email, rol, contrasinal_hash FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['contrasinal_hash'])) {
            Response::json(['error' => 'Credenciais incorrectas'], 401);
            exit;
        }

        active_cart_id($pdo, (int) $user['id_usuario']);

        Response::json([
            'message' => 'Login correcto',
            'user' => [
                'id_usuario' => (int) $user['id_usuario'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'rol' => $user['rol'],
            ],
        ]);
        exit;
    }

    // Listado completo de productos con joins para mostrar categoría y productor.
    if ($method === 'GET' && ($segments[1] ?? '') === 'products' && !isset($segments[2])) {
        $stmt = $pdo->query(
            'SELECT p.id_produto, p.nome, p.slug, p.descricion_curta, p.prezo, p.prezo_kg, p.stock, c.nome AS categoria, pr.nome_comercial AS produtor '
                . 'FROM produtos p '
                . 'INNER JOIN categorias c ON c.id_categoria = p.id_categoria '
                . 'INNER JOIN produtores pr ON pr.id_produtor = p.id_produtor '
                . 'ORDER BY p.id_produto ASC'
        );

        Response::json(['items' => $stmt->fetchAll()]);
        exit;
    }

    // Detalle de producto concreto por id numérico.
    if ($method === 'GET' && ($segments[1] ?? '') === 'products' && isset($segments[2])) {
        $productId = (int) $segments[2];

        $stmt = $pdo->prepare(
            'SELECT p.id_produto, p.nome, p.slug, p.descricion_curta, p.prezo, p.prezo_kg, p.stock, c.nome AS categoria, pr.nome_comercial AS produtor '
                . 'FROM produtos p '
                . 'INNER JOIN categorias c ON c.id_categoria = p.id_categoria '
                . 'INNER JOIN produtores pr ON pr.id_produtor = p.id_produtor '
                . 'WHERE p.id_produto = :id LIMIT 1'
        );
        $stmt->execute(['id' => $productId]);
        $item = $stmt->fetch();

        if (!$item) {
            Response::json(['error' => 'Produto non atopado'], 404);
            exit;
        }

        Response::json($item);
        exit;
    }

    // Consulta de carrito actual para un usuario.
    if ($method === 'GET' && ($segments[1] ?? '') === 'cart') {
        $userId = (int) ($_GET['user_id'] ?? 0);

        if ($userId <= 0) {
            Response::json(['error' => 'user_id obrigatorio'], 422);
            exit;
        }

        $cartId = active_cart_id($pdo, $userId);

        $stmt = $pdo->prepare(
            'SELECT cl.id_produto, p.nome, cl.cantidade, cl.prezo_unitario, cl.subtotal '
                . 'FROM carro_linas cl '
                . 'INNER JOIN produtos p ON p.id_produto = cl.id_produto '
                . 'WHERE cl.id_carro = :cart_id'
        );
        $stmt->execute(['cart_id' => $cartId]);
        $items = $stmt->fetchAll();

        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) $item['subtotal'];
        }

        Response::json([
            'id_carro' => $cartId,
            'items' => $items,
            'total' => round($total, 2),
        ]);
        exit;
    }

    // Alta o actualización de línea de carrito (misma combinación carro-producto se reemplaza).
    if ($method === 'POST' && ($segments[1] ?? '') === 'cart' && ($segments[2] ?? '') === 'items') {
        $body = request_body();
        $userId = (int) ($body['user_id'] ?? 0);
        $productId = (int) ($body['product_id'] ?? 0);
        $rawQuantity = (float) ($body['quantity'] ?? 0);

        if ($userId <= 0 || $productId <= 0) {
            Response::json(['error' => 'user_id e product_id son obrigatorios'], 422);
            exit;
        }

        $prodStmt = $pdo->prepare('SELECT id_produto, prezo, prezo_kg, stock FROM produtos WHERE id_produto = :id LIMIT 1');
        $prodStmt->execute(['id' => $productId]);
        $product = $prodStmt->fetch();

        if (!$product) {
            Response::json(['error' => 'Produto non atopado'], 404);
            exit;
        }

        // Si prezo_kg tiene valor, tratamos la cantidad como peso decimal (kg).
        $isWeightBased = $product['prezo_kg'] !== null;

        if ($isWeightBased) {
            $quantity = round($rawQuantity, 3);
            if ($quantity < 0.1) {
                Response::json(['error' => 'A cantidade minima para este produto e 0.1 kg'], 422);
                exit;
            }
        } else {
            $quantity = (float) round($rawQuantity);
            if (abs($rawQuantity - $quantity) > 0.0001 || $quantity < 1) {
                Response::json(['error' => 'Este produto solo admite unidades enteiras'], 422);
                exit;
            }
        }

        if ($quantity > (float) $product['stock']) {
            Response::json(['error' => 'Stock insuficiente'], 409);
            exit;
        }

        // El precio unitario depende de si el producto es por unidad o por kilogramo.
        $cartId = active_cart_id($pdo, $userId);
        $price = $isWeightBased ? (float) $product['prezo_kg'] : (float) $product['prezo'];
        $subtotal = round($price * $quantity, 2);

        $upsert = $pdo->prepare(
            'INSERT INTO carro_linas (id_carro, id_produto, cantidade, prezo_unitario, subtotal) '
                . 'VALUES (:cart_id, :product_id, :quantity, :price, :subtotal) '
                . 'ON DUPLICATE KEY UPDATE cantidade = VALUES(cantidade), prezo_unitario = VALUES(prezo_unitario), subtotal = VALUES(subtotal)'
        );

        $upsert->execute([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
        ]);

        Response::json(['message' => 'Carriño actualizado']);
        exit;
    }

    // Eliminación explícita de una línea de carrito.
    if ($method === 'DELETE' && ($segments[1] ?? '') === 'cart' && ($segments[2] ?? '') === 'items') {
        $body = request_body();
        $userId = (int) ($body['user_id'] ?? 0);
        $productId = (int) ($body['product_id'] ?? 0);

        if ($userId <= 0 || $productId <= 0) {
            Response::json(['error' => 'user_id e product_id son obrigatorios'], 422);
            exit;
        }

        $cartId = active_cart_id($pdo, $userId);

        $stmt = $pdo->prepare('DELETE FROM carro_linas WHERE id_carro = :cart_id AND id_produto = :product_id');
        $stmt->execute(['cart_id' => $cartId, 'product_id' => $productId]);

        Response::json(['message' => 'Produto eliminado do carriño']);
        exit;
    }

    // Conversión de carrito a pedido con transacción para mantener consistencia.
    if ($method === 'POST' && ($segments[1] ?? '') === 'orders') {
        $body = request_body();
        $userId = (int) ($body['user_id'] ?? 0);

        if ($userId <= 0) {
            Response::json(['error' => 'user_id obrigatorio'], 422);
            exit;
        }

        $cartId = active_cart_id($pdo, $userId);

        $itemsStmt = $pdo->prepare('SELECT * FROM carro_linas WHERE id_carro = :cart_id');
        $itemsStmt->execute(['cart_id' => $cartId]);
        $items = $itemsStmt->fetchAll();

        if (count($items) === 0) {
            Response::json(['error' => 'Carriño baleiro'], 409);
            exit;
        }

        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) $item['subtotal'];
        }

        // La transacción garantiza atomicidad: o se crea todo o no se crea nada.
        $pdo->beginTransaction();

        $code = 'PED-' . date('YmdHis') . '-' . random_int(100, 999);
        $insertOrder = $pdo->prepare(
            'INSERT INTO pedidos (id_usuario, codigo_pedido, estado, importe_total) '
                . 'VALUES (:user_id, :code, "pendente", :total)'
        );
        $insertOrder->execute([
            'user_id' => $userId,
            'code' => $code,
            'total' => $total,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $insertLine = $pdo->prepare(
            'INSERT INTO pedido_linas (id_pedido, id_produto, nome_produto_snapshot, prezo_unitario, cantidade, subtotal) '
                . 'SELECT :order_id, p.id_produto, p.nome, cl.prezo_unitario, cl.cantidade, cl.subtotal '
                . 'FROM carro_linas cl '
                . 'INNER JOIN produtos p ON p.id_produto = cl.id_produto '
                . 'WHERE cl.id_carro = :cart_id'
        );
        $insertLine->execute([
            'order_id' => $orderId,
            'cart_id' => $cartId,
        ]);

        $clearCart = $pdo->prepare('DELETE FROM carro_linas WHERE id_carro = :cart_id');
        $clearCart->execute(['cart_id' => $cartId]);

        // Confirmación final de operaciones en base de datos.
        $pdo->commit();

        Response::json([
            'message' => 'Pedido creado',
            'id_pedido' => $orderId,
            'codigo_pedido' => $code,
            'importe_total' => round($total, 2),
        ], 201);
        exit;
    }

    // Listado histórico de pedidos por usuario.
    if ($method === 'GET' && ($segments[1] ?? '') === 'orders') {
        $userId = (int) ($_GET['user_id'] ?? 0);

        if ($userId <= 0) {
            Response::json(['error' => 'user_id obrigatorio'], 422);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id_pedido, codigo_pedido, estado, importe_total, data_pedido FROM pedidos WHERE id_usuario = :user_id ORDER BY id_pedido DESC');
        $stmt->execute(['user_id' => $userId]);

        Response::json(['items' => $stmt->fetchAll()]);
        exit;
    }

    // Fallback para endpoints no definidos.
    Response::json(['error' => 'Endpoint non atopado'], 404);
} catch (\Throwable $exception) {
    // Seguridad transaccional: si algo falla dentro de una transacción, hacemos rollback.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // En prototipo se devuelven detalles del error para acelerar depuración.
    Response::json([
        'error' => 'Erro interno no servidor',
        'details' => $exception->getMessage(),
    ], 500);
}
