<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

$user = currentUser();

if ($user === null) {
    header('Location: /auth.php');
    exit;
}

$orders = [];
$selectedOrderId = (int) ($_GET['id_pedido'] ?? 0);
$orderLines = [];

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        'SELECT id_pedido, estado_pedido, importe_total, creado_en
         FROM pedidos
         WHERE id_usuario = :id_usuario
         ORDER BY creado_en DESC, id_pedido DESC'
    );
    $stmt->execute(['id_usuario' => (int) ($user['id_usuario'] ?? 0)]);
    $orders = $stmt->fetchAll() ?: [];

    if ($selectedOrderId > 0) {
        $lineStmt = $pdo->prepare(
            'SELECT pl.nome_produto, pl.prezo_unitario, pl.cantidade
             FROM pedido_linas pl
             INNER JOIN pedidos p ON p.id_pedido = pl.id_pedido
             WHERE p.id_usuario = :id_usuario AND p.id_pedido = :id_pedido
             ORDER BY pl.id_lina ASC'
        );
        $lineStmt->execute([
            'id_usuario' => (int) ($user['id_usuario'] ?? 0),
            'id_pedido' => $selectedOrderId,
        ]);
        $orderLines = $lineStmt->fetchAll() ?: [];
    }
} catch (Throwable $exception) {
    $orders = [];
    $orderLines = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo safe(csrfToken()); ?>">
    <title>Mis pedidos | DoDaqui</title>
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
                    <a href="/orders.php" class="is-active">Pedidos</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <span><?php echo safe((string) ($user['nome'] ?? 'Usuario')); ?></span>
                    <a href="/logout.php">Salir</a>
                </div>
            </header>

            <main class="store-main">
                <section class="box">
                    <h2 class="catalog-title">Historial de pedidos</h2>
                    <p class="section-sub">Consulta el estado y el importe de tus pedidos más recientes.</p>

                    <?php if (count($orders) === 0): ?>
                        <p class="section-sub">Todavía no tienes pedidos confirmados.</p>
                        <a class="btn btn-light" href="/products.php">Explorar catálogo</a>
                    <?php else: ?>
                        <div style="display: grid; gap: 10px; margin-top: 10px;">
                            <?php foreach ($orders as $order): ?>
                                <article class="box" style="margin: 0;">
                                    <p style="margin: 0;"><strong>Pedido #<?php echo safe((string) ($order['id_pedido'] ?? '')); ?></strong></p>
                                    <p class="muted-xs" style="margin: 4px 0;">
                                        Estado: <?php echo safe((string) ($order['estado_pedido'] ?? 'confirmado')); ?>
                                        · Fecha: <?php echo safe((string) ($order['creado_en'] ?? '')); ?>
                                    </p>
                                    <p style="margin: 0;">Total: <?php echo formatoEuro((float) ($order['importe_total'] ?? 0)); ?></p>
                                    <a class="muted-xs" href="/orders.php?id_pedido=<?php echo urlencode((string) ($order['id_pedido'] ?? '')); ?>" style="display: inline-block; margin-top: 6px;">Ver detalle</a>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($selectedOrderId > 0): ?>
                            <section class="box" style="margin-top: 12px;">
                                <h3 style="margin-top: 0;">Detalle del pedido #<?php echo safe((string) $selectedOrderId); ?></h3>
                                <?php if (count($orderLines) === 0): ?>
                                    <p class="section-sub">No hay líneas disponibles para este pedido.</p>
                                <?php else: ?>
                                    <ul style="margin: 8px 0 0; padding-left: 18px;">
                                        <?php foreach ($orderLines as $line): ?>
                                            <li style="margin-bottom: 6px;">
                                                <?php echo safe((string) ($line['nome_produto'] ?? 'Producto')); ?>
                                                · <?php echo (int) ($line['cantidade'] ?? 0); ?> ud.
                                                · <?php echo formatoEuro((float) ($line['prezo_unitario'] ?? 0)); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </section>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
    <script src="assets/app.js" defer></script>
</body>

</html>
