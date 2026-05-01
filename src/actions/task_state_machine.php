<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/database.php';

function submitTaskForQa(int $taskId,int $employeeId,string $submissionLink): void {
  $pdo=db(); $pdo->beginTransaction();
  try{
    $t=$pdo->prepare('SELECT status,task_value FROM tasks WHERE id=:id AND assignee_id=:uid AND is_locked=0 FOR UPDATE');
    $t->execute(['id'=>$taskId,'uid'=>$employeeId]); $task=$t->fetch();
    if(!$task || $task['status']!=='in_progress'){ throw new RuntimeException('Invalid task state'); }
    $u=$pdo->prepare('SELECT commission_percentage FROM users WHERE id=:id');
    $u->execute(['id'=>$employeeId]); $usr=$u->fetch();
    $pct=(float)($usr['commission_percentage']??0); $value=(float)$task['task_value']; $payout=round($value*($pct/100),2);
    $q=$pdo->prepare('UPDATE tasks SET status="completed", submission_link=:link, snapshot_percentage=:pct, snapshot_payout=:payout, completed_at=NOW() WHERE id=:id');
    $q->execute(['link'=>$submissionLink,'pct'=>$pct,'payout'=>$payout,'id'=>$taskId]);
    $pdo->commit();
  }catch(Throwable $e){$pdo->rollBack(); throw $e;}
}

function qaDecision(int $taskId,bool $approved,?string $feedback=null): void {
  $status=$approved?'deliverable':'review';
  $s=db()->prepare('UPDATE tasks SET status=:s, qa_feedback=:f, is_locked=IF(:s="deliverable",1,0), delivered_at=IF(:s="deliverable",NOW(),delivered_at) WHERE id=:id AND status="completed" AND is_locked=0');
  $s->execute(['s'=>$status,'f'=>$feedback,'id'=>$taskId]);
}
