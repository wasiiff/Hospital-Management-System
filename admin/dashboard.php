<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$stats = getStats();
$pageTitle = 'Admin Dashboard';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
    <?php
    $cards = [
        ['Patients', $stats['patients']],
        ['Doctors', $stats['doctors']],
        ['Departments', $stats['departments']],
        ['Appointments', $stats['appointments']],
        ['Revenue Collected', '₨ ' . number_format($stats['revenue'], 2)],
    ];
    foreach ($cards as [$label, $value]): ?>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="text-muted small"><?= e($label) ?></div>
                <div class="value"><?= e($value) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mt-4 p-3">
    <h6>Quick links</h6>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-primary btn-sm" href="doctors.php">Manage Doctors</a>
        <a class="btn btn-outline-primary btn-sm" href="departments.php">Manage Departments</a>
        <a class="btn btn-outline-primary btn-sm" href="reports.php">View Reports</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
