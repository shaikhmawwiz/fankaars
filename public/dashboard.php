<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth/session.php';
require_once __DIR__ . '/../src/actions/task_queries.php';
require_once __DIR__ . '/../src/actions/task_state_machine.php';
require_once __DIR__ . '/../src/finance/payouts.php';
require_once __DIR__ . '/../config/app.php';

requireAuth();
$user = $_SESSION['user'];
$role = $user['role'];
$message = null;
$error = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($role === ROLE_ADMIN && $action === 'create_task') {
            createTask(trim((string)$_POST['title']), trim((string)$_POST['description']), (int)$_POST['assignee_id'], (float)$_POST['task_value']);
            $message = 'Task created.';
        }

        if (($role === ROLE_EMPLOYEE || $role === ROLE_CORE_EMPLOYEE) && $action === 'start_task') {
            setTaskInProgress((int)$_POST['task_id'], (int)$user['id']);
            $message = 'Task moved to In Progress.';
        }

        if (($role === ROLE_EMPLOYEE || $role === ROLE_CORE_EMPLOYEE) && $action === 'submit_task') {
            submitTaskForQa((int)$_POST['task_id'], (int)$user['id'], trim((string)$_POST['delivery_path']));
            $message = 'Task submitted to QA with snapshot.';
        }

        if ($role === ROLE_QA && $action === 'qa_decision') {
            qaDecision((int)$_POST['task_id'], ($_POST['approved'] ?? '') === '1', trim((string)($_POST['qa_notes'] ?? '')));
            $message = 'QA decision saved.';
        }

        if ($role === ROLE_ACCOUNTS && $action === 'mark_paid') {
            markCommissionPaid((int)$_POST['task_id'], (int)$user['id']);
            $message = 'Payout marked paid and ledger entry written.';
        }
    }
} catch (Throwable $t) {
    $error = $t->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard</title>
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<div class="container">
  <header class="header">
    <h1>Business Management Portal</h1>
    <div>
      <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($role) ?>)
      <a class="btn btn-light" href="/logout.php">Logout</a>
    </div>
  </header>

  <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($role === ROLE_ADMIN): ?>
    <?php $employees = allEmployees(); $pending = tasksByStatus('Pending'); ?>
    <section class="card">
      <h2>Create Task</h2>
      <form method="post" class="grid">
        <input type="hidden" name="action" value="create_task">
        <input name="title" placeholder="Task title" required>
        <input name="description" placeholder="Task description">
        <input type="number" step="0.01" name="task_value" placeholder="Task value" required>
        <select name="assignee_id" required>
          <?php foreach ($employees as $employee): ?>
            <option value="<?= (int)$employee['id'] ?>"><?= htmlspecialchars($employee['name']) ?> (<?= htmlspecialchars($employee['role']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Create Task</button>
      </form>
    </section>
    <section class="card">
      <h2>Pending Tasks</h2>
      <ul>
        <?php foreach ($pending as $task): ?>
          <li>#<?= (int)$task['id'] ?> — <?= htmlspecialchars($task['title']) ?> → <?= htmlspecialchars($task['assignee_name']) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <?php if ($role === ROLE_EMPLOYEE || $role === ROLE_CORE_EMPLOYEE): ?>
    <?php $tasks = tasksForEmployee((int)$user['id']); ?>
    <section class="card">
      <h2>My Tasks</h2>
      <table>
        <tr><th>ID</th><th>Title</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($tasks as $task): ?>
          <tr>
            <td><?= (int)$task['id'] ?></td>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= htmlspecialchars($task['status']) ?></td>
            <td>
              <?php if ($task['status'] === 'Pending' || $task['status'] === 'Needs Revision'): ?>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="start_task">
                  <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                  <button class="btn" type="submit">Start</button>
                </form>
              <?php endif; ?>
              <?php if ($task['status'] === 'In Progress'): ?>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="submit_task">
                  <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                  <input name="delivery_path" placeholder="Delivery URL / path" required>
                  <button class="btn" type="submit">Submit to QA</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </section>
  <?php endif; ?>

  <?php if ($role === ROLE_QA): ?>
    <?php $queue = tasksByStatus('QA Review'); ?>
    <section class="card">
      <h2>QA Queue</h2>
      <?php foreach ($queue as $task): ?>
        <article class="qa-item">
          <h3>#<?= (int)$task['id'] ?> — <?= htmlspecialchars($task['title']) ?></h3>
          <p>Assignee: <?= htmlspecialchars($task['assignee_name']) ?></p>
          <p>Delivery: <code><?= htmlspecialchars((string)$task['delivery_path']) ?></code></p>
          <form method="post" class="inline-form">
            <input type="hidden" name="action" value="qa_decision">
            <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
            <input name="qa_notes" placeholder="QA notes">
            <button class="btn" name="approved" value="1">Approve</button>
            <button class="btn btn-warn" name="approved" value="0">Send Back</button>
          </form>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <?php if ($role === ROLE_ACCOUNTS): ?>
    <?php $delivered = tasksByStatus('Delivered'); ?>
    <section class="card">
      <h2>Delivered Tasks (Payout)</h2>
      <table>
        <tr><th>ID</th><th>Title</th><th>Commission</th><th>Payout Status</th><th>Action</th></tr>
        <?php foreach ($delivered as $task): ?>
          <tr>
            <td><?= (int)$task['id'] ?></td>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= number_format(calculateCommissionAmount($task), 2) ?></td>
            <td><?= htmlspecialchars($task['payout_status']) ?></td>
            <td>
              <?php if ($task['payout_status'] === 'Pending'): ?>
                <form method="post" class="inline-form">
                  <input type="hidden" name="action" value="mark_paid">
                  <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                  <button class="btn" type="submit">Mark Paid</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </section>
  <?php endif; ?>
</div>
</body>
</html>
