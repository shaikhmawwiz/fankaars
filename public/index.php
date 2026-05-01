<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/health') {
    jsonResponse([
        'ok' => true,
        'service' => 'business-management-portal',
        'timestamp_utc' => gmdate('c'),
    ]);
    exit;
}

if ($path === '/health/db') {
    try {
        $pdo = db();
        $version = (string)$pdo->query('SELECT VERSION() AS v')->fetch()['v'];

        jsonResponse([
            'ok' => true,
            'database' => 'connected',
            'mysql_version' => $version,
        ]);
    } catch (Throwable $e) {
        jsonResponse([
            'ok' => false,
            'database' => 'unavailable',
            'error' => $e->getMessage(),
        ], 503);
    }
    exit;
}

jsonResponse([
    'ok' => true,
    'message' => 'Business Management Portal bootstrap is running.',
    'routes' => [
        '/health',
        '/health/db',
    ],
]);
