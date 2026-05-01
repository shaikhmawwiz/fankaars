<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../audit/log.php';

function adminAllUsers(): array
{
    return db()->query('SELECT id,name,email,role,base_salary,commission_percentage,is_active,created_at FROM users ORDER BY id DESC')->fetchAll();
}

function adminCreateUser(array $data, int $actorId): void
{
    $stmt = db()->prepare('INSERT INTO users (name,email,password_hash,role,base_salary,commission_percentage,is_active) VALUES (:name,:email,:password_hash,:role,:base_salary,:commission_percentage,1)');
    $stmt->execute([
        'name' => $data['name'],
        'email' => $data['email'],
        'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        'role' => $data['role'],
        'base_salary' => $data['base_salary'],
        'commission_percentage' => $data['commission_percentage'],
    ]);

    auditLog($actorId, 'user_created', 'users', (int)db()->lastInsertId(), ['email' => $data['email'], 'role' => $data['role']]);
}

function adminBlockUser(int $userId, int $actorId): void
{
    $stmt = db()->prepare('UPDATE users SET is_active = 0 WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    auditLog($actorId, 'user_blocked', 'users', $userId, null);
}
