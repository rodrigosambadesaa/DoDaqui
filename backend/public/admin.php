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

        if ($action === 'update_category') {
            $categoryId = (int) ($_POST['id_categoria'] ?? 0);
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 120);
            $slug = mb_substr(trim((string) ($_POST['slug'] ?? '')), 0, 80);
            $slug = strtolower((string) preg_replace('/[^a-z0-9-]+/', '-', $slug));
            $slug = trim($slug, '-');

            if ($categoryId <= 0 || $name === '' || $slug === '') {
                $flashError = 'Datos de categoria no validos para actualizar.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE categorias
                     SET nome = :nome, slug = :slug
                     WHERE id_categoria = :id_categoria'
                );
                $stmt->execute([
                    'id_categoria' => $categoryId,
                    'nome' => $name,
                    'slug' => $slug,
                ]);
                $flashOk = 'Categoria actualizada correctamente.';
            }
        }

        if ($action === 'delete_category') {
            $categoryId = (int) ($_POST['id_categoria'] ?? 0);
            if ($categoryId <= 0) {
                $flashError = 'Categoria no valida para eliminar.';
            } else {
                $cleanProducts = $pdoAction->prepare('UPDATE produtos SET id_categoria = NULL WHERE id_categoria = :id_categoria');
                $cleanProducts->execute(['id_categoria' => $categoryId]);

                $stmt = $pdoAction->prepare('DELETE FROM categorias WHERE id_categoria = :id_categoria');
                $stmt->execute(['id_categoria' => $categoryId]);
                $flashOk = 'Categoria eliminada correctamente.';
            }
        }

        if ($action === 'create_producer') {
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 150);
            $slugRaw = mb_substr(trim((string) ($_POST['slug'] ?? '')), 0, 80);
            $description = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 255);
            $slug = strtolower((string) preg_replace('/[^a-z0-9-]+/', '-', $slugRaw !== '' ? $slugRaw : $name));
            $slug = trim($slug, '-');

            if (mb_strlen($name) < 2 || $slug === '') {
                $flashError = 'Indica nombre y slug validos para el productor.';
            } else {
                $stmt = $pdoAction->prepare(
                    'INSERT INTO produtores (slug, nome, descripcion, activo)
                     VALUES (:slug, :nome, :descripcion, 1)
                     ON DUPLICATE KEY UPDATE nome = VALUES(nome), descripcion = VALUES(descripcion)'
                );
                $stmt->execute([
                    'slug' => $slug,
                    'nome' => $name,
                    'descripcion' => $description,
                ]);
                $flashOk = 'Productor guardado correctamente.';
            }
        }

        if ($action === 'toggle_producer') {
            $producerId = (int) ($_POST['id_produtor'] ?? 0);
            if ($producerId <= 0) {
                $flashError = 'Productor no valido.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE produtores
                     SET activo = CASE WHEN activo = 1 THEN 0 ELSE 1 END
                     WHERE id_produtor = :id_produtor'
                );
                $stmt->execute(['id_produtor' => $producerId]);
                $flashOk = 'Estado de productor actualizado.';
            }
        }

        if ($action === 'update_producer') {
            $producerId = (int) ($_POST['id_produtor'] ?? 0);
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 150);
            $slug = mb_substr(trim((string) ($_POST['slug'] ?? '')), 0, 80);
            $description = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 255);
            $slug = strtolower((string) preg_replace('/[^a-z0-9-]+/', '-', $slug));
            $slug = trim($slug, '-');

            if ($producerId <= 0 || $name === '' || $slug === '') {
                $flashError = 'Datos de productor no validos para actualizar.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE produtores
                     SET nome = :nome, slug = :slug, descripcion = :descripcion
                     WHERE id_produtor = :id_produtor'
                );
                $stmt->execute([
                    'id_produtor' => $producerId,
                    'nome' => $name,
                    'slug' => $slug,
                    'descripcion' => $description,
                ]);
                $flashOk = 'Productor actualizado correctamente.';
            }
        }

        if ($action === 'delete_producer') {
            $producerId = (int) ($_POST['id_produtor'] ?? 0);
            if ($producerId <= 0) {
                $flashError = 'Productor no valido para eliminar.';
            } else {
                $cleanProducts = $pdoAction->prepare('UPDATE produtos SET id_produtor = NULL WHERE id_produtor = :id_produtor');
                $cleanProducts->execute(['id_produtor' => $producerId]);

                $stmt = $pdoAction->prepare('DELETE FROM produtores WHERE id_produtor = :id_produtor');
                $stmt->execute(['id_produtor' => $producerId]);
                $flashOk = 'Productor eliminado correctamente.';
            }
        }

        if ($action === 'create_product') {
            $productId = mb_substr(trim((string) ($_POST['id_produto'] ?? '')), 0, 80);
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 150);
            $summary = mb_substr(trim((string) ($_POST['summary'] ?? '')), 0, 255);
            $description = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 800);
            $price = (float) ($_POST['price'] ?? 0);
            $categoryId = (int) ($_POST['id_categoria'] ?? 0);
            $producerId = (int) ($_POST['id_produtor'] ?? 0);

            if (!preg_match('/^[a-zA-Z0-9-]{1,80}$/', $productId) || $name === '' || $summary === '' || $price < 0) {
                $flashError = 'Datos de producto no validos.';
            } else {
                $stmt = $pdoAction->prepare(
                    'INSERT INTO produtos (id_produto, nome, resumo, descripcion, prezo, id_categoria, id_produtor, activo)
                     VALUES (:id_produto, :nome, :resumo, :descripcion, :prezo, :id_categoria, :id_produtor, 1)
                     ON DUPLICATE KEY UPDATE
                        nome = VALUES(nome),
                        resumo = VALUES(resumo),
                        descripcion = VALUES(descripcion),
                        prezo = VALUES(prezo),
                        id_categoria = VALUES(id_categoria),
                        id_produtor = VALUES(id_produtor)'
                );
                $stmt->execute([
                    'id_produto' => $productId,
                    'nome' => $name,
                    'resumo' => $summary,
                    'descripcion' => $description,
                    'prezo' => $price,
                    'id_categoria' => $categoryId > 0 ? $categoryId : null,
                    'id_produtor' => $producerId > 0 ? $producerId : null,
                ]);
                $flashOk = 'Producto guardado correctamente.';
            }
        }

        if ($action === 'update_product') {
            $productId = mb_substr(trim((string) ($_POST['id_produto'] ?? '')), 0, 80);
            $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 150);
            $summary = mb_substr(trim((string) ($_POST['summary'] ?? '')), 0, 255);
            $description = mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 800);
            $price = (float) ($_POST['price'] ?? 0);
            $active = ((int) ($_POST['activo'] ?? 0) === 1) ? 1 : 0;
            $categoryId = (int) ($_POST['id_categoria'] ?? 0);
            $producerId = (int) ($_POST['id_produtor'] ?? 0);

            if (!preg_match('/^[a-zA-Z0-9-]{1,80}$/', $productId) || $name === '' || $summary === '' || $price < 0) {
                $flashError = 'Datos de producto no validos para actualizar.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE produtos
                     SET nome = :nome,
                         resumo = :resumo,
                         descripcion = :descripcion,
                         prezo = :prezo,
                         activo = :activo,
                         id_categoria = :id_categoria,
                         id_produtor = :id_produtor
                     WHERE id_produto = :id_produto'
                );
                $stmt->execute([
                    'id_produto' => $productId,
                    'nome' => $name,
                    'resumo' => $summary,
                    'descripcion' => $description,
                    'prezo' => $price,
                    'activo' => $active,
                    'id_categoria' => $categoryId > 0 ? $categoryId : null,
                    'id_produtor' => $producerId > 0 ? $producerId : null,
                ]);
                $flashOk = 'Producto actualizado correctamente.';
            }
        }

        if ($action === 'delete_product') {
            $productId = mb_substr(trim((string) ($_POST['id_produto'] ?? '')), 0, 80);
            if (!preg_match('/^[a-zA-Z0-9-]{1,80}$/', $productId)) {
                $flashError = 'Producto no valido para eliminar.';
            } else {
                $deleteLines = $pdoAction->prepare('DELETE FROM pedido_linas WHERE id_produto = :id_produto');
                $deleteLines->execute(['id_produto' => $productId]);

                $deleteCart = $pdoAction->prepare('DELETE FROM carrito_items WHERE id_produto = :id_produto');
                $deleteCart->execute(['id_produto' => $productId]);

                $deleteOpinions = $pdoAction->prepare('DELETE FROM opinions_clientes WHERE id_produto = :id_produto');
                $deleteOpinions->execute(['id_produto' => $productId]);

                $deleteProduct = $pdoAction->prepare('DELETE FROM produtos WHERE id_produto = :id_produto');
                $deleteProduct->execute(['id_produto' => $productId]);

                $flashOk = 'Producto eliminado correctamente.';
            }
        }

        if ($action === 'update_order_status') {
            $orderId = (int) ($_POST['id_pedido'] ?? 0);
            $status = mb_substr(trim((string) ($_POST['estado_pedido'] ?? '')), 0, 32);
            $allowed = ['confirmado', 'preparando', 'enviado', 'entregado', 'cancelado'];

            if ($orderId <= 0 || !in_array($status, $allowed, true)) {
                $flashError = 'Pedido o estado no valido.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE pedidos
                     SET estado_pedido = :estado_pedido
                     WHERE id_pedido = :id_pedido'
                );
                $stmt->execute([
                    'id_pedido' => $orderId,
                    'estado_pedido' => $status,
                ]);
                $flashOk = 'Estado de pedido actualizado.';
            }
        }

        if ($action === 'update_user_role') {
            $targetUserId = (int) ($_POST['id_usuario'] ?? 0);
            $newRole = (string) ($_POST['rol_usuario'] ?? 'cliente');

            if ($targetUserId <= 0 || !in_array($newRole, ['cliente', 'admin'], true)) {
                $flashError = 'Usuario o rol no valido.';
            } else {
                $stmt = $pdoAction->prepare(
                    'UPDATE usuarios
                     SET rol_usuario = :rol_usuario
                     WHERE id_usuario = :id_usuario'
                );
                $stmt->execute([
                    'id_usuario' => $targetUserId,
                    'rol_usuario' => $newRole,
                ]);
                $flashOk = 'Rol de usuario actualizado.';
            }
        }

        if ($action === 'delete_user') {
            $targetUserId = (int) ($_POST['id_usuario'] ?? 0);
            if ($targetUserId <= 0) {
                $flashError = 'Usuario no valido para eliminar.';
            } elseif ($targetUserId === (int) ($user['id_usuario'] ?? 0)) {
                $flashError = 'No puedes eliminar tu propio usuario administrador.';
            } else {
                $stmt = $pdoAction->prepare('DELETE FROM usuarios WHERE id_usuario = :id_usuario');
                $stmt->execute(['id_usuario' => $targetUserId]);
                $flashOk = 'Usuario eliminado correctamente.';
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
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                        <a class="btn btn-light" href="#admin-categories">Categorias</a>
                        <a class="btn btn-light" href="#admin-producers">Productores</a>
                        <a class="btn btn-light" href="#admin-products">Productos</a>
                        <a class="btn btn-light" href="#admin-orders">Pedidos</a>
                        <a class="btn btn-light" href="#admin-users">Usuarios</a>
                    </div>
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

                <section class="box" id="admin-categories">
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
                                <form method="post">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="update_category">
                                    <input type="hidden" name="id_categoria" value="<?php echo (int) ($category['id_categoria'] ?? 0); ?>">
                                    <div class="form-grid-2">
                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input name="name" maxlength="120" value="<?php echo safe((string) ($category['nome'] ?? '')); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Slug</label>
                                            <input name="slug" maxlength="80" value="<?php echo safe((string) ($category['slug'] ?? '')); ?>" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Guardar cambios</button>
                                </form>

                                <p class="muted-xs" style="margin: 8px 0 0;">Estado: <?php echo ((int) ($category['activa'] ?? 0) === 1) ? 'Activa' : 'Inactiva'; ?></p>
                                <form method="post" style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="toggle_category">
                                    <input type="hidden" name="id_categoria" value="<?php echo (int) ($category['id_categoria'] ?? 0); ?>">
                                    <button class="btn btn-light" type="submit">Activar / desactivar</button>
                                </form>

                                <form method="post" style="margin-top: 8px;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="id_categoria" value="<?php echo (int) ($category['id_categoria'] ?? 0); ?>">
                                    <button class="btn btn-light" type="submit">Eliminar categoria</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="box" id="admin-producers">
                    <h2 style="margin-top: 0;">Gestion de productores</h2>
                    <p class="section-sub">Control de productores y su informacion publica.</p>
                    <form method="post" class="review-form" style="margin-top: 10px;">
                        <?php echo csrfInput(); ?>
                        <input type="hidden" name="action" value="create_producer">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="producer-name">Nombre</label>
                                <input id="producer-name" name="name" maxlength="150" required>
                            </div>
                            <div class="form-group">
                                <label for="producer-slug">Slug</label>
                                <input id="producer-slug" name="slug" maxlength="80" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 8px;">
                            <label for="producer-description">Descripcion</label>
                            <textarea id="producer-description" name="description" rows="2" maxlength="255"></textarea>
                        </div>
                        <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Guardar productor</button>
                    </form>

                    <div class="order-list" style="margin-top: 12px;">
                        <?php foreach ($producers as $producer): ?>
                            <article class="box" style="margin: 0;">
                                <form method="post">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="update_producer">
                                    <input type="hidden" name="id_produtor" value="<?php echo (int) ($producer['id_produtor'] ?? 0); ?>">
                                    <div class="form-grid-2">
                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input name="name" maxlength="150" value="<?php echo safe((string) ($producer['nome'] ?? '')); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Slug</label>
                                            <input name="slug" maxlength="80" value="<?php echo safe((string) ($producer['slug'] ?? '')); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-top: 8px;">
                                        <label>Descripcion</label>
                                        <textarea name="description" rows="2" maxlength="255"><?php echo safe((string) ($producer['descripcion'] ?? '')); ?></textarea>
                                    </div>
                                    <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Guardar cambios</button>
                                </form>

                                <p class="muted-xs" style="margin: 8px 0 0;">Estado: <?php echo ((int) ($producer['activo'] ?? 0) === 1) ? 'Activo' : 'Inactivo'; ?></p>
                                <form method="post" style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="toggle_producer">
                                    <input type="hidden" name="id_produtor" value="<?php echo (int) ($producer['id_produtor'] ?? 0); ?>">
                                    <button class="btn btn-light" type="submit">Activar / desactivar</button>
                                </form>

                                <form method="post" style="margin-top: 8px;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="delete_producer">
                                    <input type="hidden" name="id_produtor" value="<?php echo (int) ($producer['id_produtor'] ?? 0); ?>">
                                    <button class="btn btn-light" type="submit">Eliminar productor</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="box" id="admin-products">
                    <h2 style="margin-top: 0;">Gestion de productos</h2>
                    <p class="section-sub">Alta, modificacion y baja de productos de tienda.</p>
                    <form method="post" class="review-form" style="margin-top: 10px;">
                        <?php echo csrfInput(); ?>
                        <input type="hidden" name="action" value="create_product">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="product-id">ID producto</label>
                                <input id="product-id" name="id_produto" maxlength="80" placeholder="product-10" required>
                            </div>
                            <div class="form-group">
                                <label for="product-name">Nombre</label>
                                <input id="product-name" name="name" maxlength="150" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 8px;">
                            <label for="product-summary">Resumen</label>
                            <input id="product-summary" name="summary" maxlength="255" required>
                        </div>
                        <div class="form-group" style="margin-top: 8px;">
                            <label for="product-description">Descripcion</label>
                            <textarea id="product-description" name="description" rows="2" maxlength="800"></textarea>
                        </div>
                        <div class="form-grid-2" style="margin-top: 8px;">
                            <div class="form-group">
                                <label for="product-price">Precio</label>
                                <input id="product-price" name="price" type="number" min="0" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="product-category">Categoria</label>
                                <select id="product-category" name="id_categoria">
                                    <option value="0">Sin categoria</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo (int) ($category['id_categoria'] ?? 0); ?>"><?php echo safe((string) ($category['nome'] ?? '')); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 8px;">
                            <label for="product-producer">Productor</label>
                            <select id="product-producer" name="id_produtor">
                                <option value="0">Sin productor</option>
                                <?php foreach ($producers as $producer): ?>
                                    <option value="<?php echo (int) ($producer['id_produtor'] ?? 0); ?>"><?php echo safe((string) ($producer['nome'] ?? '')); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Guardar producto</button>
                    </form>

                    <div class="order-list" style="margin-top: 12px;">
                        <?php foreach ($products as $product): ?>
                            <article class="box" style="margin: 0;">
                                <form method="post">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="update_product">
                                    <input type="hidden" name="id_produto" value="<?php echo safe((string) ($product['id_produto'] ?? '')); ?>">

                                    <p style="margin: 0 0 6px;"><strong>ID: <?php echo safe((string) ($product['id_produto'] ?? '')); ?></strong></p>
                                    <div class="form-grid-2">
                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input name="name" value="<?php echo safe((string) ($product['nome'] ?? '')); ?>" maxlength="150" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Precio</label>
                                            <input name="price" type="number" min="0" step="0.01" value="<?php echo safe((string) ($product['prezo'] ?? '0')); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-top: 8px;">
                                        <label>Resumen</label>
                                        <input name="summary" maxlength="255" value="<?php echo safe((string) ($product['resumo'] ?? '')); ?>" required>
                                    </div>

                                    <div class="form-group" style="margin-top: 8px;">
                                        <label>Descripcion</label>
                                        <textarea name="description" rows="2" maxlength="800"><?php echo safe((string) ($product['descripcion'] ?? '')); ?></textarea>
                                    </div>

                                    <div class="form-grid-2" style="margin-top: 8px;">
                                        <div class="form-group">
                                            <label>Categoria</label>
                                            <select name="id_categoria">
                                                <option value="0">Sin categoria</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo (int) ($category['id_categoria'] ?? 0); ?>" <?php echo ((int) ($category['id_categoria'] ?? 0) === (int) ($product['id_categoria'] ?? 0)) ? 'selected' : ''; ?>><?php echo safe((string) ($category['nome'] ?? '')); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Productor</label>
                                            <select name="id_produtor">
                                                <option value="0">Sin productor</option>
                                                <?php foreach ($producers as $producer): ?>
                                                    <option value="<?php echo (int) ($producer['id_produtor'] ?? 0); ?>" <?php echo ((int) ($producer['id_produtor'] ?? 0) === (int) ($product['id_produtor'] ?? 0)) ? 'selected' : ''; ?>><?php echo safe((string) ($producer['nome'] ?? '')); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group" style="margin-top: 8px;">
                                        <label>Activo</label>
                                        <select name="activo">
                                            <option value="1" <?php echo ((int) ($product['activo'] ?? 0) === 1) ? 'selected' : ''; ?>>Si</option>
                                            <option value="0" <?php echo ((int) ($product['activo'] ?? 0) === 0) ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </div>

                                    <button class="btn btn-dark" style="margin-top: 10px;" type="submit">Guardar cambios</button>
                                </form>

                                <form method="post" style="margin-top: 8px;">
                                    <?php echo csrfInput(); ?>
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id_produto" value="<?php echo safe((string) ($product['id_produto'] ?? '')); ?>">
                                    <button class="btn btn-light" type="submit">Eliminar producto</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="box" id="admin-orders">
                    <h2 style="margin-top: 0;">Gestion de pedidos</h2>
                    <p class="section-sub">Consulta y actualizacion de estados de pedidos de clientes.</p>
                    <?php if (count($orders) === 0): ?>
                        <p class="section-sub">No hay pedidos registrados todavia.</p>
                    <?php else: ?>
                        <div class="order-list" style="margin-top: 10px;">
                            <?php foreach ($orders as $order): ?>
                                <article class="box" style="margin: 0;">
                                    <p style="margin: 0;"><strong>Pedido #<?php echo (int) ($order['id_pedido'] ?? 0); ?></strong></p>
                                    <p class="muted-xs" style="margin: 4px 0;">Cliente: <?php echo safe((string) ($order['cliente_nome'] ?? '')); ?></p>
                                    <p class="muted-xs" style="margin: 4px 0;">Fecha: <?php echo safe((string) ($order['creado_en'] ?? '')); ?></p>
                                    <p class="muted-xs" style="margin: 4px 0;">Total: <?php echo formatoEuro((float) ($order['importe_total'] ?? 0)); ?></p>
                                    <form method="post" style="margin-top: 8px; display: grid; grid-template-columns: minmax(140px, 220px) auto; gap: 8px; align-items: center;">
                                        <?php echo csrfInput(); ?>
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="id_pedido" value="<?php echo (int) ($order['id_pedido'] ?? 0); ?>">
                                        <select name="estado_pedido">
                                            <?php
                                            $orderStatus = (string) ($order['estado_pedido'] ?? 'confirmado');
                                            $options = ['confirmado', 'preparando', 'enviado', 'entregado', 'cancelado'];
                                            foreach ($options as $option):
                                            ?>
                                                <option value="<?php echo safe($option); ?>" <?php echo $orderStatus === $option ? 'selected' : ''; ?>><?php echo safe(ucfirst($option)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-light" type="submit">Actualizar estado</button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="box" id="admin-users">
                    <h2 style="margin-top: 0;">Gestion de usuarios</h2>
                    <p class="section-sub">Control de cuentas registradas y permisos de administrador.</p>
                    <?php if (count($users) === 0): ?>
                        <p class="section-sub">No hay usuarios disponibles.</p>
                    <?php else: ?>
                        <div class="order-list" style="margin-top: 10px;">
                            <?php foreach ($users as $entry): ?>
                                <article class="box" style="margin: 0;">
                                    <p style="margin: 0;"><strong><?php echo safe((string) ($entry['nome'] ?? 'Usuario')); ?></strong></p>
                                    <p class="muted-xs" style="margin: 4px 0;">Correo: <?php echo safe((string) ($entry['correo_electronico'] ?? '')); ?></p>
                                    <p class="muted-xs" style="margin: 4px 0;">Telefono: <?php echo safe((string) ($entry['telefono'] ?? '')); ?></p>
                                    <p class="muted-xs" style="margin: 4px 0;">Alta: <?php echo safe((string) ($entry['creado_en'] ?? '')); ?></p>

                                    <form method="post" style="margin-top: 8px; display: grid; grid-template-columns: minmax(140px, 220px) auto; gap: 8px; align-items: center;">
                                        <?php echo csrfInput(); ?>
                                        <input type="hidden" name="action" value="update_user_role">
                                        <input type="hidden" name="id_usuario" value="<?php echo (int) ($entry['id_usuario'] ?? 0); ?>">
                                        <select name="rol_usuario">
                                            <option value="cliente" <?php echo ((string) ($entry['rol_usuario'] ?? '') === 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                                            <option value="admin" <?php echo ((string) ($entry['rol_usuario'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <button class="btn btn-light" type="submit">Guardar rol</button>
                                    </form>

                                    <?php if ((int) ($entry['id_usuario'] ?? 0) !== (int) ($user['id_usuario'] ?? 0)): ?>
                                        <form method="post" style="margin-top: 8px;">
                                            <?php echo csrfInput(); ?>
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id_usuario" value="<?php echo (int) ($entry['id_usuario'] ?? 0); ?>">
                                            <button class="btn btn-light" type="submit">Eliminar usuario</button>
                                        </form>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>

    <script src="assets/app.js" defer></script>
</body>

</html>