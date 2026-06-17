<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$stats = getStats();
$pageTitle = 'Admin Dashboard';
$pageIcon  = 'speedometer2';
$pageSub   = 'Overview of hospital operations';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
    <?php
    $cards = [
        ['Patients',     $stats['patients'],                       'people',          'tint-blue'],
        ['Doctors',      $stats['doctors'],                        'person-badge',    'tint-violet'],
        ['Departments',  $stats['departments'],                    'diagram-3',       'tint-amber'],
        ['Appointments', $stats['appointments'],                   'calendar2-check', 'tint-rose'],
        ['Revenue',      '₨' . number_format($stats['revenue'], 0), 'cash-stack',      'tint-green'],
    ];
    foreach ($cards as [$label, $value, $icon, $tint]): ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card stat-card h-100">
                <div class="stat-icon <?= $tint ?>"><i class="bi bi-<?= $icon ?>"></i></div>
                <div class="stat-meta">
                    <div class="label"><?= e($label) ?></div>
                    <div class="value"><?= e($value) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mt-4 p-3 p-md-4">
    <h6><i class="bi bi-lightning-charge-fill text-warning"></i> Quick Links</h6>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-primary btn-sm" href="doctors.php"><i class="bi bi-person-badge"></i> Manage Doctors</a>
        <a class="btn btn-outline-primary btn-sm" href="departments.php"><i class="bi bi-diagram-3"></i> Manage Departments</a>
        <a class="btn btn-outline-primary btn-sm" href="reports.php"><i class="bi bi-bar-chart-line"></i> View Reports</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
