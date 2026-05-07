<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

$user = requireAdminUser();

$flashError = '';
$flashOk = '';

$categories = [];
$producers = [];
$products = [];
$orders = [];
$users = [];

try {
    $pdo = db();
    ensureCatalogDataAvailable($pdo);

    $categories = fetchCatalogCategories($pdo, false);
    $producers = fetchCatalogProducers($pdo, false);
    $products = fetchCatalogProducts($pdo, false);

    $ordersStmt = $pdo->query(
        'SELECT p.id_pedido, p.estado_pedido, p.importe_total, p.creado_en, u.nome AS cliente_nome
         FROM pedidos p
         INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
         ORDER BY p.creado_en DESC, p.id_pedido DESC
         LIMIT 100'
    );
    $orders = $ordersStmt ? ($ordersStmt->fetchAll() ?: []) : [];

    $usersStmt = $pdo->query(
        'SELECT id_usuario, nome, correo_electronico, telefono, rol_usuario, creado_en
         FROM usuarios
         ORDER BY creado_en DESC, id_usuario DESC
         LIMIT 200'
    );
    $users = $usersStmt ? ($usersStmt->fetchAll() ?: []) : [];
} catch (Throwable $exception) {
    $flashError = 'No se pudo cargar el panel de administracion en este momento.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo safe(csrfToken()); ?>">
    <title>Panel admin | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="page-wrap">
        <div class="desktop-shell">
            <header class="top-nav">
                <a class="brand" href="/home.php">DoDaqui</a>
                <nav class="nav-links desktop-only">
                    <a href="/home.php">Inicio</a>
                    <a href="/products.php">Catalogo</a>
                    <a href="/orders.php">Pedidos</a>
                    <a href="/admin.php" class="is-active">Admin</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <span><?php echo safe((string) ($user['nome'] ?? 'Admin')); ?></span>
                    <a href="/profile.php">Perfil</a>
                    <a href="/logout.php">Salir</a>
                </div>
            </header>

            <main class="store-main">
                <section class="box">
                    <h1 class="catalog-title">Panel de administracion</h1>
                    <p class="section-sub">Gestion integral de productos, categorias, productores, pedidos y usuarios.</p>
                    <div class="order-list" style="margin-top: 10px;">
                        <article class="box" style="margin: 0;">
                            <p><strong>Productos</strong></p>
                            <p class="muted-xs"><?php echo (int) count($products); ?> registros</p>
                        </article>
                        <article class="box" style="margin: 0;">
                            <p><strong>Categorias</strong></p>
                            <p class="muted-xs"><?php echo (int) count($categories); ?> registros</p>
                        </article>
                        <article class="box" style="margin: 0;">
                            <p><strong>Productores</strong></p>
                            <p class="muted-xs"><?php echo (int) count($producers); ?> registros</p>
                        </article>
                        <article class="box" style="margin: 0;">
                            <p><strong>Pedidos</strong></p>
                            <p class="muted-xs"><?php echo (int) count($orders); ?> registros</p>
                        </article>
                        <article class="box" style="margin: 0;">
                            <p><strong>Usuarios</strong></p>
                            <p class="muted-xs"><?php echo (int) count($users); ?> registros</p>
                        </article>
                    </div>
                </section>

                <?php if ($flashError !== ''): ?>
                    <section class="box" style="border-color: #d8b2b2; color: #8b3a3a;"><?php echo safe($flashError); ?></section>
                <?php endif; ?>

                <?php if ($flashOk !== ''): ?>
                    <section class="box" style="border-color: #b8c8d8; color: #32485e;"><?php echo safe($flashOk); ?></section>
                <?php endif; ?>

                <section class="box">
                    <h2 style="margin-top: 0;">Gestion de categorias</h2>
                    <p class="section-sub">Alta, baja y mantenimiento de categorias del catalogo.</p>
                </section>

                <section class="box">
                    <h2 style="margin-top: 0;">Gestion de productores</h2>
                    <p class="section-sub">Control de productores y su informacion publica.</p>
                </section>

                <section class="box">
                    <h2 style="margin-top: 0;">Gestion de productos</h2>
                    <p class="section-sub">Alta, modificacion y baja de productos de tienda.</p>
                </section>

                <section class="box">
                    <h2 style="margin-top: 0;">Gestion de pedidos</h2>
                    <p class="section-sub">Consulta y actualizacion de estados de pedidos de clientes.</p>
                </section>

                <section class="box">
                    <h2 style="margin-top: 0;">Gestion de usuarios</h2>
                    <p class="section-sub">Control de cuentas registradas y permisos de administrador.</p>
                </section>
            </main>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>
