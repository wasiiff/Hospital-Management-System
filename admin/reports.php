<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$revenue  = getMonthlyRevenue();   // MonthlyRevenue view
$schedule = getDoctorSchedule();   // DoctorSchedule view

$pageTitle = 'Reports';
$pageIcon  = 'bar-chart-line';
$pageSub   = 'Revenue and schedule insights from SQL views';
$base = '../';
require __DIR__ . '/../includes/header.php';

$statusBadge = ['Scheduled' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger'];
?>
<div class="card p-3 p-md-4 mb-4">
    <h6><i class="bi bi-cash-coin text-success"></i> Monthly Revenue <span class="text-muted small fw-normal">view: MonthlyRevenue</span></h6>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Month</th><th>Payments</th><th class="text-end">Total Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($revenue as $r): ?>
            <tr>
                <td class="fw-semibold"><i class="bi bi-calendar3 text-muted"></i> <?= e($r['revenue_month']) ?></td>
                <td><?= e($r['total_payments']) ?></td>
                <td class="text-end fw-semibold text-success">₨<?= e(number_format($r['total_revenue'], 2)) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$revenue): ?><tr><td colspan="3" class="empty-row">No payments recorded yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card p-3 p-md-4">
    <h6><i class="bi bi-calendar3-week text-primary"></i> Doctor Schedule <span class="text-muted small fw-normal">view: DoctorSchedule</span></h6>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Doctor</th><th>Department</th><th>Date/Time</th><th>Patient</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($schedule as $s): ?>
            <tr>
                <td><?= e($s['doctor_name']) ?></td>
                <td><span class="badge bg-primary-subtle text-primary-emphasis"><?= e($s['department']) ?></span></td>
                <td><?= e($s['appointment_date']) ?></td>
                <td><?= e($s['patient_name']) ?></td>
                <td><span class="badge rounded-pill bg-<?= $statusBadge[$s['status']] ?? 'secondary' ?>"><?= e($s['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$schedule): ?><tr><td colspan="5" class="empty-row">No appointments scheduled.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
