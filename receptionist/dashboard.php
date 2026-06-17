<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['receptionist']);

$stats = getStats();
$pageTitle = 'Receptionist Dashboard';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Patients</div><div class="value"><?= e($stats['patients']) ?></div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Doctors</div><div class="value"><?= e($stats['doctors']) ?></div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Appointments</div><div class="value"><?= e($stats['appointments']) ?></div></div></div>
    <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Revenue</div><div class="value">₹<?= e(number_format($stats['revenue'], 0)) ?></div></div></div>
</div>

<div class="card mt-4 p-3">
    <h6>Quick actions</h6>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-primary btn-sm" href="patients.php">Register / Search Patients</a>
        <a class="btn btn-outline-primary btn-sm" href="appointments.php">Schedule Appointments</a>
        <a class="btn btn-outline-primary btn-sm" href="billing.php">Billing & Payments</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
