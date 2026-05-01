<?php

declare(strict_types=1);

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
        http_response_code(401);
        exit('Unauthorized');
    }
}

function requireRole(array $roles): void
{
    requireAuth();

    $userRole = $_SESSION['user']['role'] ?? null;
    if (!in_array($userRole, $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}
