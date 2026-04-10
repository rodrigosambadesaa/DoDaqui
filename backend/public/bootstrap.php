<?php

declare(strict_types=1);

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
        
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
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
    return number_format($importe, 2, ',', '.') . ' EUR';
}
