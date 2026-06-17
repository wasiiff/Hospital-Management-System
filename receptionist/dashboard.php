<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['receptionist']);

$stats = getStats();
$pageTitle = 'Receptionist Dashboard';
$pageIcon  = 'speedometer2';
$pageSub   = 'Patients, appointments & billing at a glance';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
    <?php
    $cards = [
        ['Patients',     $stats['patients'],                       'people',          'tint-blue'],
        ['Doctors',      $stats['doctors'],                        'person-badge',    'tint-violet'],
        ['Appointments', $stats['appointments'],                   'calendar2-check', 'tint-rose'],
        ['Revenue',      '₨' . number_format($stats['revenue'], 0), 'cash-stack',      'tint-green'],
    ];
    foreach ($cards as [$label, $value, $icon, $tint]): ?>
        <div class="col-6 col-xl-3">
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
    <h6><i class="bi bi-lightning-charge-fill text-warning"></i> Quick Actions</h6>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-primary btn-sm" href="patients.php"><i class="bi bi-people"></i> Register / Search Patients</a>
        <a class="btn btn-outline-primary btn-sm" href="appointments.php"><i class="bi bi-calendar2-check"></i> Schedule Appointments</a>
        <a class="btn btn-outline-primary btn-sm" href="billing.php"><i class="bi bi-receipt"></i> Billing &amp; Payments</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
