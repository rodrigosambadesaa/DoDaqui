<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$productFilter = trim((string) ($_GET['producto'] ?? ''));

$productNames = [
    'product-1' => 'Tarro de miel ecológica',
    'product-2' => 'Cesta de mimbre artesanal',
    'product-3' => 'Aceite de oliva prensado en frío',
    'product-4' => 'Pan de masa madre',
    'product-5' => 'Queso curado artesanal',
    'product-6' => 'Mermelada de frutos rojos',
];

$opinions = [];
$error = '';

try {
    $pdo = db();
    ensureOpinionsSchema($pdo);

    if ($productFilter !== '') {
        $query = $pdo->prepare(
            'SELECT o.id_produto, o.valoracion, o.opinion, o.data_opinion, u.nome
             FROM opinions_clientes o
             INNER JOIN usuarios u ON u.id_usuario = o.id_cliente
             WHERE o.id_produto = :id_produto
             ORDER BY o.data_opinion DESC, o.id_opinion DESC'
        );
        $query->execute(['id_produto' => $productFilter]);
    } else {
        $query = $pdo->query(
            'SELECT o.id_produto, o.valoracion, o.opinion, o.data_opinion, u.nome
             FROM opinions_clientes o
             INNER JOIN usuarios u ON u.id_usuario = o.id_cliente
             ORDER BY o.data_opinion DESC, o.id_opinion DESC'
        );
    }

    $opinions = $query ? ($query->fetchAll() ?: []) : [];
} catch (Throwable $exception) {
    $error = 'No se pudieron cargar las opiniones en este momento.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opiniones | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="page-wrap">
        <div class="desktop-shell">
            <header class="top-nav">
                <a class="brand" href="/home.php">DoDaqui</a>
                <nav class="nav-links desktop-only">
                    <a href="/home.php">Inicio</a>
                    <a href="/products.php">Categorías</a>
                    <a href="/cart.php">Carrito</a>
                    <a href="/opinions.php" class="is-active">Opiniones</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <?php if ($user === null): ?>
                        <a class="login-link" href="/auth.php">Iniciar sesión</a>
                    <?php else: ?>
                        <span><?php echo safe((string) ($user['nome'] ?? 'Usuario')); ?></span>
                        <a href="/logout.php">Salir</a>
                    <?php endif; ?>
                    <a href="/cart.php" aria-label="Carrito">Carrito</a>
                    <span class="badge-count" id="cart-count">0</span>
                </div>
            </header>

            <main class="store-main">
                <section class="box">
                    <div class="catalog-head" style="margin-bottom: 6px;">
                        <h1 class="catalog-title" style="margin: 0;">Todas las opiniones</h1>
                        <a class="muted-xs" href="/products.php">Ir al catálogo</a>
                    </div>

                    <?php if ($productFilter !== ''): ?>
                        <p class="section-sub">Filtro activo: <?php echo safe($productNames[$productFilter] ?? $productFilter); ?></p>
                    <?php endif; ?>

                    <?php if ($error !== ''): ?>
                        <p class="section-sub" style="color: #8b3a3a;"><?php echo safe($error); ?></p>
                    <?php elseif (count($opinions) === 0): ?>
                        <p class="section-sub">No hay opiniones registradas todavía.</p>
                    <?php else: ?>
                        <div class="review-list">
                            <?php foreach ($opinions as $entry): ?>
                                <?php
                                $rating = (int) ($entry['valoracion'] ?? 0);
                                $stars = str_repeat('★', max(0, $rating)) . str_repeat('☆', max(0, 5 - $rating));
                                $productId = (string) ($entry['id_produto'] ?? '');
                                ?>
                                <article class="review-item">
                                    <div style="display: flex; justify-content: space-between; gap: 8px; align-items: center;">
                                        <strong><?php echo safe((string) ($entry['nome'] ?? 'Cliente')); ?></strong>
                                        <span class="muted-xs"><?php echo safe((string) date('d/m/Y', strtotime((string) ($entry['data_opinion'] ?? 'now')))); ?></span>
                                    </div>
                                    <p class="muted-xs" style="margin-top: 4px;">
                                        Producto:
                                        <a href="/product.php?id=<?php echo urlencode($productId); ?>"><?php echo safe($productNames[$productId] ?? $productId); ?></a>
                                    </p>
                                    <p class="review-stars"><?php echo safe($stars); ?></p>
                                    <p><?php echo safe((string) ($entry['opinion'] ?? '')); ?></p>
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