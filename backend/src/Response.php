<?php

declare(strict_types=1);

namespace App;

final class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        // Centralizamos status + cabecera para no repetir lógica en cada endpoint.
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        // JSON_UNESCAPED_UNICODE evita escapar caracteres acentuados innecesariamente.
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
