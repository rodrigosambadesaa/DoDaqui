<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/bootstrap.php';

if (currentUser() !== null) {
    redirect('home.php');
}

function ensureAuthSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(120) NOT NULL,
            email VARCHAR(160) NOT NULL UNIQUE,
            contrasinal VARCHAR(255) NOT NULL,
            rol ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nome, email, contrasinal, rol)
         VALUES (:nome, :email, :contrasinal, :rol)
         ON DUPLICATE KEY UPDATE nome = VALUES(nome), contrasinal = VALUES(contrasinal), rol = VALUES(rol)'
    );

    $stmt->execute([
        'nome' => 'Usuario Demo',
        'email' => 'demo@tenda.gal',
        'contrasinal' => '$2y$10$nZ24rn1voj52FXBw4hezpOtXJAovRyHrNSVfv9zKIyKy5RrUbi2Z6',
        'rol' => 'cliente',
    ]);
}

$pdo = db();
ensureAuthSchema($pdo);

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Debes completar correo y contraseña.';
        } else {
            $stmt = $pdo->prepare('SELECT id_usuario, nome, email, rol, contrasinal FROM usuarios WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['contrasinal'])) {
                $_SESSION['user'] = [
                    'id_usuario' => (int) $user['id_usuario'],
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'rol' => $user['rol'],
                ];
                redirect('home.php');
            }

            $error = 'Credenciales incorrectas.';
        }
    }

    if ($action === 'register') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Todos los campos son obligatorios.';
        } else {
            $passwordErrors = validateStrongPassword($password);

            if (count($passwordErrors) > 0) {
                $error = implode(' ', $passwordErrors);
            } else {
                $exists = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1');
                $exists->execute(['email' => $email]);

                if ($exists->fetch()) {
                    $error = 'El correo ya está registrado.';
                } else {
                    $insert = $pdo->prepare('INSERT INTO usuarios (nome, email, contrasinal, rol) VALUES (:nome, :email, :contrasinal, :rol)');
                    $insert->execute([
                        'nome' => $name,
                        'email' => $email,
                        'contrasinal' => password_hash($password, PASSWORD_BCRYPT),
                        'rol' => 'cliente',
                    ]);
                    $ok = 'Registro correcto. Ya puedes iniciar sesión.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | DoDaqui</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <div class="checkout-wrap" style="grid-template-columns: minmax(0, 760px); justify-content: center;">
        <main class="checkout-main" style="background: #fff;">
            <h1 style="font-size: 32px; margin-bottom: 8px;">Acceso a la tienda</h1>
            <p class="section-sub" style="margin-bottom: 16px;">Inicia sesión o crea tu cuenta.</p>

            <?php if ($error !== ''): ?>
                <div class="box" style="border-color: #d8b2b2; color: #8b3a3a;"><?php echo safe($error); ?></div>
            <?php endif; ?>

            <?php if ($ok !== ''): ?>
                <div class="box" style="border-color: #b8c8d8; color: #32485e;"><?php echo safe($ok); ?></div>
            <?php endif; ?>

            <div class="checkout-grid">
                <section class="box">
                    <h3>Iniciar sesión</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="login-email">Correo</label>
                            <input id="login-email" name="email" type="email" required>
                        </div>
                        <div class="form-group" style="margin-top: 10px;">
                            <label for="login-password">Contraseña</label>
                            <input id="login-password" name="password" type="password" required>
                        </div>
                        <button class="btn btn-dark" style="width: 100%; margin-top: 14px;">Entrar</button>
                    </form>
                </section>

                <section class="box">
                    <h3>Crear cuenta</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="register">
                        <div class="form-group">
                            <label for="register-name">Nombre</label>
                            <input id="register-name" name="name" required>
                        </div>
                        <div class="form-group" style="margin-top: 10px;">
                            <label for="register-email">Correo</label>
                            <input id="register-email" name="email" type="email" required>
                        </div>
                        <div class="form-group" style="margin-top: 10px;">
                            <label for="register-password">Contraseña</label>
                            <input id="register-password" name="password" type="password" required>
                        </div>
                        <p class="muted-xs" style="margin-top: 8px;">Mínimo 10 caracteres con mayúscula, minúscula, número y símbolo.</p>
                        <button class="btn btn-dark" style="width: 100%; margin-top: 12px;">Registrarme</button>
                    </form>
                </section>
            </div>

            <div class="box" style="margin-top: 14px;">
                <strong>Usuario de prueba permanente:</strong>
                <p class="muted-xs" style="margin-top: 6px;">Correo: demo@tenda.gal</p>
                <p class="muted-xs">Contraseña: Demo1234!</p>
            </div>
        </main>
    </div>
</body>

</html>
