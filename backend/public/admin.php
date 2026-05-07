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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken((string) ($_POST['csrf_token'] ?? ''));

    try {
        $pdoAction = db();
        ensureCatalogDataAvailable($pdoAction);

        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'create_category') {
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
            $slugRaw = mb_substr(trim((string) ($_POST['slug'] ?? '')), 0, 80);
            $slug = strtolower((string) preg_replace('/[^a-z0-9-]+/', '-', $slugRaw !== '' ? $slugRaw : $name));
            $slug = trim($slug, '-');

            if (mb_strlen($name) < 2 || $slug === '') {
                $flashError = 'Indica nombre y slug validos para la categoria.';
            } else {
                $stmt = $pdoAction->prepare(
                    'INSERT INTO categorias (slug, nome, activa)
                     VALUES (:slug, :nome, 1)
                     ON DUPLICATE KEY UPDATE nome = VALUES(nome)'
                );
                $stmt->execute([
                    'slug' => $slug,
                    'nome' => $name,
                ]);
                $flashOk = 'Categoria guardada correctamente.';
            }
        }

        if ($action === 'toggle_category') {
            $categoryId = (int) ($_POST['id_categoria'] ?? 0);
            if ($categoryId <= 0) {
                $flashError = 'Categoria no valida.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE categorias
                     SET activa = CASE WHEN activa = 1 THEN 0 ELSE 1 END
                     WHERE id_categoria = :id_categoria'
                );
                $stmt->execute(['id_categoria' => $categoryId]);
                $flashOk = 'Estado de categoria actualizado.';
            }
        }
    } catch (Throwable $exception) {
        $flashError = 'No se pudo aplicar la accion de administracion.';
    }
}

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
                    <form method="post" class="review-form" style="margin-top: 10px;">
                        <?php echo csrfInput(); ?>
                        <input type="hidden" name="action" value="create_category">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="category-name">Nombre</label>
                                <input id="category-name" name="name" maxlength="120" required>
                            </div>
                            <div class="form-group">
                                <label for="category-slug">Slug</label>
                                <input id="category-slug" name="slug" maxlength="80" placeholder="alimentacion" required>
                            </div>
                        </div>
                        <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Guardar categoria</button>
                    </form>

                    <div class="order-list" style="margin-top: 12px;">
                        <?php foreach ($categories as $category): ?>
                            <article class="box" style="margin: 0;">
                                <p style="margin: 0;"><strong><?php echo safe((string) ($category['nome'] ?? 'Categoria')); ?></strong></p>
                                <p class="muted-xs" style="margin: 4px 0;">slug: <?php echo safe((string) ($category['slug'] ?? '')); ?></p>
                                <p class="muted-xs" style="margin: 4px 0;">Estado: <?php echo ((int) ($category['activa'] ?? 0) === 1) ? 'Activa' : 'Inactiva'; ?></p>
                                <form method="post" style="margin-top: 8px;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="toggle_category">
                                    <input type="hidden" name="id_categoria" value="<?php echo (int) ($category['id_categoria'] ?? 0); ?>">
                                    <button class="btn btn-light" type="submit">Activar / desactivar</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
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
