<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

function auditLog(int $actorId, string $action, ?string $entityType = null, ?int $entityId = null, ?array $meta = null): void
{
    $pdo = db();

    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (actor_id, action, entity_type, entity_id, metadata_json, created_at)
         VALUES (:actor_id, :action, :entity_type, :entity_id, :metadata_json, NOW())'
    );

    $stmt->execute([
        'actor_id' => $actorId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'metadata_json' => $meta ? json_encode($meta, JSON_THROW_ON_ERROR) : null,
    ]);
}
