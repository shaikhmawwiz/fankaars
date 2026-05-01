<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth/auth.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (loginUser(trim((string)($_POST['email'] ?? '')), (string)($_POST['password'] ?? ''))) {
        header('Location: /dashboard.php');
        exit;
    }

    $error = 'Invalid credentials.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<div class="login-card">
  <h1>Business Management Portal</h1>
  <p>Sign in to continue</p>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post" class="grid">
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Password" required>
    <button class="btn" type="submit">Login</button>
  </form>
</div>
</body>
</html>
