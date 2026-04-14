<?php

declare(strict_types=1);

function secureSessionStart(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

function applySecurityHeaders(bool $json = false): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Cross-Origin-Opener-Policy: same-origin');

    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");
        return;
    }

    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
}

function csrfToken(): string
{
    if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token']) || $_SESSION['_csrf_token'] === '') {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrfInput(): string
{
    return '<input type="hidden" name="csrf_token" value="' . safe(csrfToken()) . '">';
}

function verifyCsrfToken(?string $token): bool
{
    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    if (!is_string($sessionToken) || $sessionToken === '' || !is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

function requireValidCsrfToken(?string $token): void
{
    if (verifyCsrfToken($token)) {
        return;
    }

    http_response_code(419);
    echo 'Token CSRF inválido o ausente.';
    exit;
}

function requireValidCsrfTokenJson(?string $token): void
{
    if (verifyCsrfToken($token)) {
        return;
    }

    http_response_code(419);
    echo json_encode(['ok' => false, 'message' => 'Token CSRF inválido o ausente.'], JSON_UNESCAPED_UNICODE);
    exit;
}

function readStringPost(string $key, int $maxLength = 255): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    if ($maxLength > 0) {
        return mb_substr($value, 0, $maxLength);
    }

    return $value;
}

function clientIp(): string
{
    $candidate = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    if (filter_var($candidate, FILTER_VALIDATE_IP)) {
        return $candidate;
    }

    return '0.0.0.0';
}

function appEnv(string $name, string $default = ''): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

/**
 * Get database connection
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = appEnv('DB_HOST', 'db');
        $port = (int) appEnv('DB_PORT', '3306');
        $database = appEnv('DB_DATABASE', appEnv('DB_NAME', 'app'));
        $user = appEnv('DB_USERNAME', appEnv('DB_USER', 'app_user'));
        $pass = appEnv('DB_PASSWORD', appEnv('DB_PASS', 'app_pass'));

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $exception) {
            $rootUser = appEnv('DB_ROOT_USERNAME', appEnv('DB_ROOT_USER', 'root'));
            $rootPass = appEnv('DB_ROOT_PASSWORD', appEnv('DB_ROOT_PASS', 'root'));

            try {
                // Fallback para entornos locales con volumen MySQL persistido y credenciales antiguas.
                $pdo = new PDO($dsn, $rootUser, $rootPass, $options);
            } catch (PDOException $rootException) {
                $serverDsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
                $adminPdo = new PDO($serverDsn, $rootUser, $rootPass, $options);

                // Si la base no existe en el volumen actual, se crea para mantener login/registro operativos.
                $dbNameSafe = str_replace('`', '``', $database);
                $adminPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbNameSafe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                $pdo = new PDO($dsn, $rootUser, $rootPass, $options);
            }
        }
    }

    return $pdo;
}

/**
 * Get current logged-in user
 */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Sanitize output
 */
function safe(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

function validateStrongPassword(string $password): array
{
    $errors = [];

    if (strlen($password) < 10) {
        $errors[] = 'La contraseña debe tener al menos 10 caracteres.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'La contraseña debe incluir al menos una letra mayúscula.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'La contraseña debe incluir al menos una letra minúscula.';
    }

    if (!preg_match('/\d/', $password)) {
        $errors[] = 'La contraseña debe incluir al menos un número.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'La contraseña debe incluir al menos un símbolo.';
    }

    return $errors;
}

function formatoEuro(float $importe): string
{
    return number_format($importe, 2, ',', '.') . ' €';
}

function ensureOpinionsSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS opinions_clientes (
            id_opinion INT AUTO_INCREMENT PRIMARY KEY,
            id_produto VARCHAR(80) NOT NULL,
            id_cliente INT NOT NULL,
            data_opinion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            valoracion TINYINT NOT NULL,
            opinion VARCHAR(600) NOT NULL,
            CONSTRAINT fk_opinion_usuario
                FOREIGN KEY (id_cliente) REFERENCES usuarios(id_usuario)
                ON DELETE CASCADE,
            CONSTRAINT chk_valoracion
                CHECK (valoracion BETWEEN 1 AND 5)
        )"
    );
}

function ensureAuthRateLimitSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS auth_rate_limits (
            id_limit INT AUTO_INCREMENT PRIMARY KEY,
            scope_name VARCHAR(32) NOT NULL,
            identifier VARCHAR(190) NOT NULL,
            hits INT NOT NULL DEFAULT 0,
            window_start TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_scope_identifier (scope_name, identifier)
        )"
    );
}

function authRateLimitAllow(PDO $pdo, string $scope, string $identifier, int $maxHits, int $windowSeconds): bool
{
    ensureAuthRateLimitSchema($pdo);

    $select = $pdo->prepare('SELECT hits, window_start FROM auth_rate_limits WHERE scope_name = :scope_name AND identifier = :identifier LIMIT 1');
    $select->execute([
        'scope_name' => $scope,
        'identifier' => $identifier,
    ]);
    $row = $select->fetch();

    if (!$row) {
        $insert = $pdo->prepare(
            'INSERT INTO auth_rate_limits (scope_name, identifier, hits, window_start)
             VALUES (:scope_name, :identifier, 1, NOW())'
        );
        $insert->execute([
            'scope_name' => $scope,
            'identifier' => $identifier,
        ]);
        return true;
    }

    $windowStart = strtotime((string) ($row['window_start'] ?? 'now'));
    $hits = (int) ($row['hits'] ?? 0);
    $elapsed = time() - $windowStart;

    if ($elapsed >= $windowSeconds) {
        $reset = $pdo->prepare(
            'UPDATE auth_rate_limits
             SET hits = 1, window_start = NOW()
             WHERE scope_name = :scope_name AND identifier = :identifier'
        );
        $reset->execute([
            'scope_name' => $scope,
            'identifier' => $identifier,
        ]);
        return true;
    }

    if ($hits >= $maxHits) {
        return false;
    }

    $update = $pdo->prepare(
        'UPDATE auth_rate_limits
         SET hits = hits + 1
         WHERE scope_name = :scope_name AND identifier = :identifier'
    );
    $update->execute([
        'scope_name' => $scope,
        'identifier' => $identifier,
    ]);

    return true;
}
