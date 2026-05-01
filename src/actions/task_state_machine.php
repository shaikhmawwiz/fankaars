<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

const TASK_PENDING = 'Pending';
const TASK_IN_PROGRESS = 'In Progress';
const TASK_QA_REVIEW = 'QA Review';
const TASK_DELIVERED = 'Delivered';
const TASK_NEEDS_REVISION = 'Needs Revision';

function submitTaskForQa(int $taskId, int $employeeId, string $deliveryPath): void
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $task = $pdo->prepare('SELECT id, status FROM tasks WHERE id = :id FOR UPDATE');
        $task->execute(['id' => $taskId]);
        $taskRow = $task->fetch();

        if (!$taskRow || $taskRow['status'] !== TASK_IN_PROGRESS) {
            throw new RuntimeException('Task is not in a submittable state.');
        }

        $emp = $pdo->prepare('SELECT salary, commission_pct FROM users WHERE id = :id AND role IN ("Employee", "Core Employee")');
        $emp->execute(['id' => $employeeId]);
        $empRow = $emp->fetch();

        if (!$empRow) {
            throw new RuntimeException('Employee profile not found.');
        }

        $update = $pdo->prepare(
            'UPDATE tasks
             SET status = :status,
                 delivery_path = :delivery,
                 snapshot_salary = :snapshot_salary,
                 snapshot_commission_pct = :snapshot_commission_pct,
                 completed_at = NOW()
             WHERE id = :id'
        );

        $update->execute([
            'status' => TASK_QA_REVIEW,
            'delivery' => $deliveryPath,
            'snapshot_salary' => $empRow['salary'],
            'snapshot_commission_pct' => $empRow['commission_pct'],
            'id' => $taskId,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function qaDecision(int $taskId, bool $approved, ?string $notes = null): void
{
    $pdo = db();
    $status = $approved ? TASK_DELIVERED : TASK_NEEDS_REVISION;

    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET status = :status,
             qa_notes = :notes,
             delivered_at = CASE WHEN :status = "Delivered" THEN NOW() ELSE delivered_at END,
             is_frozen = CASE WHEN :status = "Delivered" THEN 1 ELSE 0 END
         WHERE id = :id AND status = "QA Review"'
    );

    $stmt->execute([
        'status' => $status,
        'notes' => $notes,
        'id' => $taskId,
    ]);
}
