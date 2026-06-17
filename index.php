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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time() ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="login-logo mb-3"><i class="bi bi-heart-pulse-fill"></i></div>
            <h4 class="mb-1 fw-bold">Global Hospital</h4>
            <div class="text-muted small">Hospital Management System</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        <div class="demo-box mt-4">
            <div class="fw-bold mb-2"><i class="bi bi-info-circle"></i> Demo accounts</div>
            <div class="d-flex justify-content-between"><span class="text-muted">Administrator</span> <code>admin / admin123</code></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Receptionist</span> <code>reception / reception123</code></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Doctor</span> <code>hassan / doctor123</code></div>
        </div>
    </div>
</div>
</body>
</html>
