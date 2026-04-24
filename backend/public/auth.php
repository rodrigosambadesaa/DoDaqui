<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

function ensureAuthSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(120) NOT NULL,
            correo_electronico VARCHAR(160) NOT NULL UNIQUE,
            telefono VARCHAR(30) NULL,
            contrasinal VARCHAR(255) NOT NULL,
            rol_usuario ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    $columnCheck = $pdo->query('SHOW COLUMNS FROM usuarios');
    $columns = array_column($columnCheck->fetchAll(), 'Field');

    if (in_array('email', $columns, true) && !in_array('correo_electronico', $columns, true)) {
        $pdo->exec('ALTER TABLE usuarios CHANGE COLUMN email correo_electronico VARCHAR(160) NOT NULL');
    }

    if (in_array('rol', $columns, true) && !in_array('rol_usuario', $columns, true)) {
        $pdo->exec("ALTER TABLE usuarios CHANGE COLUMN rol rol_usuario ENUM('cliente','admin') NOT NULL DEFAULT 'cliente'");
    }

    if (!in_array('telefono', $columns, true)) {
        $pdo->exec('ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(30) NULL AFTER correo_electronico');
    }

    $indexCheck = $pdo->query('SHOW INDEX FROM usuarios');
    $indexNames = array_column($indexCheck->fetchAll(), 'Key_name');
    if (!in_array('unique_correo_electronico', $indexNames, true)) {
        $pdo->exec('ALTER TABLE usuarios ADD UNIQUE KEY unique_correo_electronico (correo_electronico)');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nome, correo_electronico, telefono, contrasinal, rol_usuario)
            VALUES (:nome, :correo_electronico, :telefono, :contrasinal, :rol_usuario)
            ON DUPLICATE KEY UPDATE nome = VALUES(nome), telefono = VALUES(telefono), contrasinal = VALUES(contrasinal), rol_usuario = VALUES(rol_usuario)'
    );

    $stmt->execute([
        'nome' => 'Usuario Demo',
        'correo_electronico' => 'demo@tenda.gal',
        'telefono' => '+34600000000',
        'contrasinal' => '$2y$10$nZ24rn1voj52FXBw4hezpOtXJAovRyHrNSVfv9zKIyKy5RrUbi2Z6',
        'rol_usuario' => 'cliente',
    ]);
}

$pdo = null;
$dbAvailable = true;

try {
    $pdo = db();
    ensureAuthSchema($pdo);
} catch (Throwable $exception) {
    $dbAvailable = false;
}

if (currentUser() !== null) {
    redirect('home.php');
}

$error = '';
$ok = '';
$info = '';

if (!$dbAvailable) {
    $info = 'Modo demo activo temporalmente en produccion. Usa la cuenta demo para acceder.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken((string) ($_POST['csrf_token'] ?? ''));
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (!$dbAvailable) {
            if ($email === 'demo@tenda.gal' && $password === 'Demo1234!') {
                $_SESSION['user'] = [
                    'id_usuario' => 1,
                    'nome' => 'Usuario Demo',
                    'email' => 'demo@tenda.gal',
                    'telefono' => '+34600000000',
                    'rol' => 'cliente',
                ];
                issueDemoAuthCookie();
                redirect('home.php');
            }

            $error = 'Credenciales incorrectas. En este momento solo esta disponible la cuenta demo.';
        }

        if ($error === '' && $pdo instanceof PDO) {
            $loginRateLimitOk = authRateLimitAllow($pdo, 'login_ip', clientIp(), 20, 900)
                && authRateLimitAllow($pdo, 'login_email', strtolower($email), 10, 900);

            if (!$loginRateLimitOk) {
                $error = 'Demasiados intentos de inicio de sesión. Inténtalo de nuevo en unos minutos.';
            }
        }

        if ($error === '' && ($email === '' || $password === '')) {
            $error = 'Debes completar correo y contraseña.';
        } elseif ($error === '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160)) {
            $error = 'El correo no tiene un formato válido.';
        } elseif ($error === '' && $pdo instanceof PDO) {
            $stmt = $pdo->prepare('SELECT id_usuario, nome, correo_electronico, telefono, rol_usuario, contrasinal FROM usuarios WHERE correo_electronico = :correo LIMIT 1');
            $stmt->execute(['correo' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['contrasinal'])) {
                $_SESSION['user'] = [
                    'id_usuario' => (int) $user['id_usuario'],
                    'nome' => $user['nome'],
                    'email' => $user['correo_electronico'],
                    'telefono' => (string) ($user['telefono'] ?? ''),
                    'rol' => $user['rol_usuario'],
                ];
                redirect('home.php');
            }

            $error = 'Credenciales incorrectas.';
        } elseif ($error === '') {
            $error = 'La base de datos no está disponible temporalmente.';
        }
    }

    if ($action === 'register') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (!$dbAvailable) {
            $error = 'El registro está desactivado temporalmente hasta configurar la base de datos en producción.';
        }

        if ($error === '' && $pdo instanceof PDO) {
            $registerRateLimitOk = authRateLimitAllow($pdo, 'register_ip', clientIp(), 8, 3600)
                && authRateLimitAllow($pdo, 'register_email', strtolower($email), 3, 3600);

            if (!$registerRateLimitOk) {
                $error = 'Demasiados intentos de registro. Vuelve a intentarlo más tarde.';
            }
        }

        if ($error === '' && ($name === '' || $email === '' || $password === '')) {
            $error = 'Todos los campos son obligatorios.';
        } elseif ($error === '' && (mb_strlen($name) < 2 || mb_strlen($name) > 120 || !preg_match('/^[\p{L}\s\'-]+$/u', $name))) {
            $error = 'El nombre solo puede contener letras, espacios, apostrofes y guiones.';
        } elseif ($error === '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160)) {
            $error = 'El correo no tiene un formato válido.';
        } elseif ($error === '' && $pdo instanceof PDO) {
            $passwordErrors = validateStrongPassword($password);

            if (count($passwordErrors) > 0) {
                $error = implode(' ', $passwordErrors);
            } else {
                $exists = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE correo_electronico = :correo LIMIT 1');
                $exists->execute(['correo' => $email]);

                if ($exists->fetch()) {
                    $error = 'El correo ya está registrado.';
                } else {
                    $insert = $pdo->prepare('INSERT INTO usuarios (nome, correo_electronico, contrasinal, rol_usuario) VALUES (:nome, :correo_electronico, :contrasinal, :rol_usuario)');
                    $insert->execute([
                        'nome' => $name,
                        'correo_electronico' => $email,
                        'contrasinal' => password_hash($password, PASSWORD_BCRYPT),
                        'rol_usuario' => 'cliente',
                    ]);
                    $ok = 'Registro correcto. Ya puedes iniciar sesión.';
                }
            }
        } elseif ($error === '') {
            $error = 'La base de datos no está disponible temporalmente.';
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

            <?php if ($info !== ''): ?>
                <div class="box" style="border-color: #d4c8a8; color: #6b5526;"><?php echo safe($info); ?></div>
            <?php endif; ?>

            <div class="checkout-grid">
                <section class="box">
                    <h3>Iniciar sesión</h3>
                    <form method="post">
                        <?php echo csrfInput(); ?>
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
                        <?php echo csrfInput(); ?>
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
                            <input
                                id="register-password"
                                name="password"
                                type="password"
                                minlength="10"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}"
                                title="Mínimo 10 caracteres con mayúscula, minúscula, número y símbolo"
                                required>
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