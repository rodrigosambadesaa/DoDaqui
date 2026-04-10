<?php

declare(strict_types=1);

/**
 * Bootstrap configuration for DoDaquí
 * Initializes database connection and utilities
 */

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'dodaqui');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);

/**
 * Get database connection
 */
function db(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
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
