<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    // Instancia única de conexión PDO por request.
    private PDO $pdo;

    public function __construct()
    {
        // Variables de entorno con fallback para entorno Docker por defecto.
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '3306';
        $db = getenv('DB_DATABASE') ?: 'tenda_dodaqui';
        $user = getenv('DB_USERNAME') ?: 'tenda_user';
        $pass = getenv('DB_PASSWORD') ?: 'tenda_pass';

        // DSN completo para conexión MySQL en UTF-8.
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);

        try {
            // Activamos excepciones y fetch asociativo para simplificar el acceso en el router API.
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            // Si falla la conexión, devolvemos JSON para mantener consistencia con la API.
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Erro de conexion coa base de datos',
                'details' => $exception->getMessage(),
            ]);
            exit;
        }
    }

    public function pdo(): PDO
    {
        // Exponemos PDO para ejecutar queries desde el router principal.
        return $this->pdo;
    }
}
