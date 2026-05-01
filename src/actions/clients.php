<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../audit/log.php';

function createClient(string $name, int $actorId): void {
  $s=db()->prepare('INSERT INTO clients (name,status) VALUES (:n,"active")');
  $s->execute(['n'=>$name]);
  auditLog($actorId,'client_created','clients',(int)db()->lastInsertId(),['name'=>$name]);
}

function allClients(): array { return db()->query('SELECT * FROM clients ORDER BY id DESC')->fetchAll(); }
