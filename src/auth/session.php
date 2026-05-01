<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

function ensureSessionStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function requireAuth(): void
{
    ensureSessionStarted();

    if (!isset($_SESSION['user'])) {
        header('Location: /login.php');
        exit;
    }

    $id = (int)($_SESSION['user']['id'] ?? 0);
    $stmt = db()->prepare('SELECT is_active FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row || (int)$row['is_active'] !== 1) {
        $_SESSION = [];
        session_destroy();
        header('Location: /login.php');
        exit;
    }
}

function requireRole(array $roles): void
{
    requireAuth();

    $role = strtolower((string)($_SESSION['user']['role'] ?? ''));
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}
