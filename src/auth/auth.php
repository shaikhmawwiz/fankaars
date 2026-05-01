<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../config/database.php';

function loginUser(string $email, string $password): bool
{
    ensureSessionStarted();

    $stmt = db()->prepare('SELECT id, name, email, role, password_hash, is_active FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1) {
        return false;
    }

    if (!password_verify($password, (string)$user['password_hash'])) {
        return false;
    }

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => (string)$user['name'],
        'email' => (string)$user['email'],
        'role' => (string)$user['role'],
    ];

    return true;
}

function logoutUser(): void
{
    ensureSessionStarted();
    $_SESSION = [];
    session_destroy();
}
