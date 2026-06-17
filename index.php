<?php
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Go to the role dashboard.
if (isLoggedIn()) {
    header('Location: ' . dashboardFor(currentRole()));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        header('Location: ' . dashboardFor(currentRole()));
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login &middot; Hospital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="text-center mb-4">
            <div style="font-size:2.5rem">&#9877;</div>
            <h4 class="mb-0">Global Hospital</h4>
            <div class="text-muted small">Management System</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
        </form>

        <hr class="my-4">
        <div class="small text-muted">
            <strong>Demo accounts</strong><br>
            Admin: <code>admin / admin123</code><br>
            Receptionist: <code>reception / reception123</code><br>
            Doctor: <code>ashok / doctor123</code>
        </div>
    </div>
</div>
</body>
</html>
