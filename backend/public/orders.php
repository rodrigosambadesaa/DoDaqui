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
                    <p class="section-sub">Estamos preparando tu historial completo. En el siguiente bloque mostraremos todos tus pedidos con su estado.</p>
                    <a class="btn btn-light" href="/home.php">Volver al inicio</a>
                </section>
            </main>
        </div>
    </div>
    <script src="assets/app.js" defer></script>
</body>

</html>
