<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/database.php';

function releaseMonthlySalary(int $userId, float $amount, int $accountsId): void {
  $s=db()->prepare('INSERT INTO payouts (task_id,user_id,amount,payout_type,payment_status,released_by,released_at) VALUES (NULL,:u,:a,"salary","released",:rb,NOW())');
  $s->execute(['u'=>$userId,'a'=>$amount,'rb'=>$accountsId]);
}
