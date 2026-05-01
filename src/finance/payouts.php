<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

function calculateCommissionAmount(array $task): float
{
    $base = (float)($task['task_value'] ?? 0);
    $pct = (float)($task['snapshot_commission_pct'] ?? 0);

    return round($base * ($pct / 100), 2);
}

function markCommissionPaid(int $taskId, int $accountsUserId): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $taskStmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id AND status = "Delivered" FOR UPDATE');
        $taskStmt->execute(['id' => $taskId]);
        $task = $taskStmt->fetch();

        if (!$task) {
            throw new RuntimeException('Delivered task not found.');
        }

        $commission = calculateCommissionAmount($task);

        $updateTask = $pdo->prepare('UPDATE tasks SET payout_status = "Paid", payout_paid_at = NOW() WHERE id = :id');
        $updateTask->execute(['id' => $taskId]);

        $ledger = $pdo->prepare(
            'INSERT INTO ledger_entries (task_id, user_id, amount, entry_type, created_by)
             VALUES (:task_id, :user_id, :amount, "commission_payout", :created_by)'
        );
        $ledger->execute([
            'task_id' => $taskId,
            'user_id' => $task['assignee_id'],
            'amount' => $commission,
            'created_by' => $accountsUserId,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
