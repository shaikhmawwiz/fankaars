<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/database.php';

function taskCounts(): array {
  $rows=db()->query('SELECT status, COUNT(*) c FROM tasks GROUP BY status')->fetchAll();
  $out=['pending'=>0,'in_progress'=>0,'completed'=>0,'review'=>0,'deliverable'=>0];
  foreach($rows as $r){$out[$r['status']] = (int)$r['c'];}
  return $out;
}

function payoutTotalsForUser(int $userId): array {
  $s=db()->prepare('SELECT payment_status, COALESCE(SUM(amount),0) total FROM payouts WHERE user_id=:u GROUP BY payment_status');
  $s->execute(['u'=>$userId]);
  $tot=['pending'=>0.0,'released'=>0.0];
  foreach($s->fetchAll() as $r){$tot[$r['payment_status']] = (float)$r['total'];}
  return $tot;
}

function payoutsForUser(int $userId): array {
  $s=db()->prepare('SELECT * FROM payouts WHERE user_id=:u ORDER BY id DESC');
  $s->execute(['u'=>$userId]);
  return $s->fetchAll();
}
