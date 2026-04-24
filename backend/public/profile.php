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
    <title>Mi perfil | DoDaqui</title>
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
                    <a href="/orders.php">Pedidos</a>
                    <a href="/profile.php" class="is-active">Mi perfil</a>
                </nav>
                <div class="nav-grow"></div>
                <div class="nav-actions">
                    <span><?php echo safe((string) ($user['nome'] ?? 'Usuario')); ?></span>
                    <a href="/logout.php">Salir</a>
                </div>
            </header>

            <main class="store-main">
                <section class="box">
                    <h2 class="catalog-title">Mi perfil</h2>
                    <p class="section-sub">Correo: <?php echo safe((string) ($user['email'] ?? '')); ?></p>
                    <p class="section-sub">Nombre: <?php echo safe((string) ($user['nome'] ?? '')); ?></p>
                    <p class="section-sub">Teléfono: <?php echo safe((string) ($user['telefono'] ?? '')); ?></p>
                </section>
            </main>
        </div>
    </div>
    <script src="assets/app.js" defer></script>
</body>

</html>
