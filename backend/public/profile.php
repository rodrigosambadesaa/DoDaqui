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

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'profile_update') {
    requireValidCsrfToken((string) ($_POST['csrf_token'] ?? ''));

    $name = trim((string) ($_POST['nome'] ?? ''));
    $phone = trim((string) ($_POST['telefono'] ?? ''));

    if ($name === '' || mb_strlen($name) > 120) {
        $message = 'El nombre es obligatorio y debe tener menos de 120 caracteres.';
        $messageType = 'error';
    } elseif ($phone !== '' && mb_strlen($phone) > 30) {
        $message = 'El teléfono no puede superar los 30 caracteres.';
        $messageType = 'error';
    } else {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                'UPDATE usuarios
                 SET nome = :nome, telefono = :telefono
                 WHERE id_usuario = :id_usuario'
            );

            $stmt->execute([
                'nome' => mb_substr($name, 0, 120),
                'telefono' => mb_substr($phone, 0, 30),
                'id_usuario' => (int) ($user['id_usuario'] ?? 0),
            ]);

            $passwordHashStmt = $pdo->prepare('SELECT contrasinal FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1');
            $passwordHashStmt->execute(['id_usuario' => (int) ($user['id_usuario'] ?? 0)]);
            $row = $passwordHashStmt->fetch();

            $_SESSION['user'] = [
                'id_usuario' => (int) ($user['id_usuario'] ?? 0),
                'nome' => mb_substr($name, 0, 120),
                'email' => (string) ($user['email'] ?? ''),
                'telefono' => mb_substr($phone, 0, 30),
                'rol' => (string) ($user['rol'] ?? 'cliente'),
            ];
            clearFallbackLoggedOutMarker();
            if (is_array($row)) {
                issueFallbackAuthCookie($_SESSION['user'], (string) ($row['contrasinal'] ?? ''));
            }

            $user = currentUser() ?? $_SESSION['user'];
            $message = 'Perfil actualizado correctamente.';
            $messageType = 'ok';
        } catch (Throwable $exception) {
            $message = 'No se pudo actualizar el perfil. Inténtalo de nuevo.';
            $messageType = 'error';
        }
    }
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

                    <?php if ($message !== ''): ?>
                        <p class="section-sub" style="color: <?php echo $messageType === 'ok' ? '#0f5132' : '#842029'; ?>;">
                            <?php echo safe($message); ?>
                        </p>
                    <?php endif; ?>

                    <p class="section-sub">Correo: <?php echo safe((string) ($user['email'] ?? '')); ?></p>

                    <form method="post" class="box" style="margin-top: 10px;">
                        <input type="hidden" name="csrf_token" value="<?php echo safe(csrfToken()); ?>">
                        <input type="hidden" name="action" value="profile_update">

                        <label for="nome">Nombre</label>
                        <input id="nome" name="nome" type="text" maxlength="120" required value="<?php echo safe((string) ($user['nome'] ?? '')); ?>">

                        <label for="telefono">Teléfono</label>
                        <input id="telefono" name="telefono" type="text" maxlength="30" value="<?php echo safe((string) ($user['telefono'] ?? '')); ?>">

                        <button class="btn btn-dark" type="submit" style="margin-top: 10px;">Guardar cambios</button>
                    </form>
                </section>
            </main>
        </div>
    </div>
    <script src="assets/app.js" defer></script>
</body>

</html>
