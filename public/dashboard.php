<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth/session.php';
require_once __DIR__ . '/../src/actions/task_queries.php';
require_once __DIR__ . '/../src/actions/task_state_machine.php';
require_once __DIR__ . '/../src/finance/payouts.php';
require_once __DIR__ . '/../src/actions/admin_users.php';
require_once __DIR__ . '/../config/app.php';

requireAuth();
$user = $_SESSION['user'];
$role = strtolower((string)$user['role']);
$message = null;
$error = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($role === ROLE_ADMIN && $action === 'create_user') {
            adminCreateUser([
                'name' => trim((string)$_POST['name']),
                'email' => trim((string)$_POST['email']),
                'password' => (string)$_POST['password'],
                'role' => (string)$_POST['role'],
                'base_salary' => (float)$_POST['base_salary'],
                'commission_percentage' => (float)$_POST['commission_percentage'],
            ], (int)$user['id']);
            $message = 'User created.';
        }
        if ($role === ROLE_ADMIN && $action === 'block_user') {
            adminBlockUser((int)$_POST['user_id'], (int)$user['id']);
            $message = 'User blocked and session will be invalidated.';
        }
        if ($role === ROLE_ADMIN && $action === 'create_task') {
            createTask(trim((string)$_POST['title']), trim((string)$_POST['description']), (int)$_POST['assignee_id'], (float)$_POST['task_value']);
            $message = 'Task created.';
        }
        if (($role === ROLE_EMPLOYEE || $role === ROLE_CORE) && $action === 'start_task') {
            setTaskInProgress((int)$_POST['task_id'], (int)$user['id']);
            $message = 'Task moved to in_progress.';
        }
        if (($role === ROLE_EMPLOYEE || $role === ROLE_CORE) && $action === 'submit_task') {
            submitTaskForQa((int)$_POST['task_id'], (int)$user['id'], trim((string)$_POST['delivery_path']));
            $message = 'Task submitted to qa.';
        }
        if ($role === ROLE_QA && $action === 'qa_decision') {
            qaDecision((int)$_POST['task_id'], ($_POST['approved'] ?? '') === '1', trim((string)($_POST['qa_notes'] ?? '')));
            $message = 'qa decision saved.';
        }
        if ($role === ROLE_ACCOUNTS && $action === 'mark_paid') {
            markCommissionPaid((int)$_POST['task_id'], (int)$user['id']);
            $message = 'Payout marked paid.';
        }
    }
} catch (Throwable $t) {
    $error = $t->getMessage();
}
?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light"><div class="container-fluid"><div class="row min-vh-100">
<aside class="col-12 col-md-3 col-lg-2 bg-black p-3"><h4>Portal</h4><p><?=htmlspecialchars($user['name'])?><br><span class="badge bg-info"><?=htmlspecialchars($role)?></span></p><a class="btn btn-outline-light w-100" href="/logout.php">Logout</a></aside>
<main class="col-12 col-md-9 col-lg-10 p-4">
<?php if($message):?><div class="alert alert-success"><?=htmlspecialchars($message)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?>

<?php if($role===ROLE_ADMIN): $employees=allEmployees(); $users=adminAllUsers(); ?>
<div class="card bg-secondary text-light mb-3"><div class="card-body"><h5>Create User</h5>
<form method="post" class="row g-2"><input type="hidden" name="action" value="create_user"><div class="col-md-3"><input class="form-control" name="name" required placeholder="Name"></div><div class="col-md-3"><input class="form-control" type="email" name="email" required placeholder="Email"></div><div class="col-md-2"><input class="form-control" name="password" required placeholder="Password"></div><div class="col-md-2"><select class="form-select" name="role"><option value="employee">employee</option><option value="core">core</option><option value="qa">qa</option><option value="accounts">accounts</option><option value="admin">admin</option></select></div><div class="col-md-1"><input class="form-control" name="base_salary" type="number" step="0.01" value="0"></div><div class="col-md-1"><input class="form-control" name="commission_percentage" type="number" step="0.01" value="0"></div><div class="col-12"><button class="btn btn-primary">Save User</button></div></form></div></div>
<div class="card bg-secondary text-light mb-3"><div class="card-body"><h5>Users</h5><table class="table table-dark table-striped"><tr><th>ID</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr><?php foreach($users as $u):?><tr><td><?=$u['id']?></td><td><?=htmlspecialchars($u['email'])?></td><td><?=htmlspecialchars($u['role'])?></td><td><span class="badge <?=$u['is_active']?'bg-success':'bg-danger'?>"><?=$u['is_active']?'Active':'Blocked'?></span></td><td><?php if((int)$u['is_active']===1):?><form method="post"><input type="hidden" name="action" value="block_user"><input type="hidden" name="user_id" value="<?=$u['id']?>"><button class="btn btn-sm btn-danger">Block</button></form><?php endif;?></td></tr><?php endforeach;?></table></div></div>
<div class="card bg-secondary text-light"><div class="card-body"><h5>Create Task</h5><form method="post" class="row g-2"><input type="hidden" name="action" value="create_task"><div class="col-md-3"><input class="form-control" name="title" required placeholder="Title"></div><div class="col-md-3"><input class="form-control" name="description" placeholder="Description"></div><div class="col-md-2"><input class="form-control" name="task_value" type="number" step="0.01" required placeholder="Value"></div><div class="col-md-3"><select class="form-select" name="assignee_id"><?php foreach($employees as $e):?><option value="<?=$e['id']?>"><?=htmlspecialchars($e['name'])?> (<?=$e['role']?>)</option><?php endforeach;?></select></div><div class="col-md-1"><button class="btn btn-primary">Assign</button></div></form></div></div>
<?php endif; ?>

<?php if($role===ROLE_EMPLOYEE||$role===ROLE_CORE): $tasks=tasksForEmployee((int)$user['id']); ?>
<div class="card bg-secondary text-light"><div class="card-body"><h5>My Tasks</h5><table class="table table-dark"><tr><th>ID</th><th>Title</th><th>Status</th><th>Action</th></tr><?php foreach($tasks as $t):?><tr><td><?=$t['id']?></td><td><?=htmlspecialchars($t['title'])?></td><td><span class="badge bg-info"><?=htmlspecialchars($t['status'])?></span></td><td><?php if($t['status']==='pending'||$t['status']==='review'):?><form method="post" class="d-inline"><input type="hidden" name="action" value="start_task"><input type="hidden" name="task_id" value="<?=$t['id']?>"><button class="btn btn-sm btn-warning">Start</button></form><?php endif;?><?php if($t['status']==='in_progress'):?><form method="post" class="d-inline"><input type="hidden" name="action" value="submit_task"><input class="form-control d-inline w-auto" name="delivery_path" placeholder="Submission link" required><input type="hidden" name="task_id" value="<?=$t['id']?>"><button class="btn btn-sm btn-success">Submit</button></form><?php endif;?></td></tr><?php endforeach;?></table></div></div>
<?php endif; ?>

<?php if($role===ROLE_QA): $q=tasksByStatus('completed');?>
<div class="card bg-secondary text-light"><div class="card-body"><h5>qa Review</h5><?php foreach($q as $t):?><div class="border rounded p-2 mb-2"><strong>#<?=$t['id']?> <?=htmlspecialchars($t['title'])?></strong> <a target="_blank" class="text-warning" href="<?=htmlspecialchars((string)$t['delivery_path'])?>">Open submission</a><form method="post" class="mt-2"><input type="hidden" name="action" value="qa_decision"><input type="hidden" name="task_id" value="<?=$t['id']?>"><input class="form-control mb-2" name="qa_notes" placeholder="Notes"><button class="btn btn-success" name="approved" value="1">Approve</button> <button class="btn btn-danger" name="approved" value="0">Reject</button></form></div><?php endforeach;?></div></div>
<?php endif; ?>

<?php if($role===ROLE_ACCOUNTS): $d=tasksByStatus('deliverable');?>
<div class="card bg-secondary text-light"><div class="card-body"><h5>accounts Payouts</h5><table class="table table-dark"><tr><th>ID</th><th>Task</th><th>Commission</th><th>Status</th><th></th></tr><?php foreach($d as $t):?><tr><td><?=$t['id']?></td><td><?=htmlspecialchars($t['title'])?></td><td><?=number_format(calculateCommissionAmount($t),2)?></td><td><span class="badge <?=$t['payout_status']==='paid'?'bg-success':'bg-warning'?>"><?=htmlspecialchars($t['payout_status'])?></span></td><td><?php if($t['payout_status']==='pending'):?><form method="post"><input type="hidden" name="action" value="mark_paid"><input type="hidden" name="task_id" value="<?=$t['id']?>"><button class="btn btn-sm btn-success">Mark paid</button></form><?php endif;?></td></tr><?php endforeach;?></table></div></div>
<?php endif; ?>

</main></div></div></body></html>
