<?php
declare(strict_types=1); require_once __DIR__.'/../../config/database.php';
function auditLog(int $actorId,string $action,?string $entityType=null,?int $entityId=null,?array $meta=null): void {$s=db()->prepare('INSERT INTO audit_logs (actor_id,action,entity_type,entity_id,metadata_json,created_at) VALUES (:a,:ac,:et,:ei,:m,NOW())');$s->execute(['a'=>$actorId,'ac'=>$action,'et'=>$entityType,'ei'=>$entityId,'m'=>$meta?json_encode($meta,JSON_THROW_ON_ERROR):null]);}
