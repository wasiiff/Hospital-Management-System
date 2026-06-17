<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$revenue  = getMonthlyRevenue();   // MonthlyRevenue view
$schedule = getDoctorSchedule();   // DoctorSchedule view

$pageTitle = 'Reports';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="card p-3 mb-4">
    <h6>Monthly Revenue <span class="text-muted small">(view: MonthlyRevenue)</span></h6>
    <table class="table table-sm">
        <thead><tr><th>Month</th><th>Payments</th><th>Total Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($revenue as $r): ?>
            <tr>
                <td><?= e($r['revenue_month']) ?></td>
                <td><?= e($r['total_payments']) ?></td>
                <td>₹<?= e(number_format($r['total_revenue'], 2)) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$revenue): ?><tr><td colspan="3" class="text-muted">No payments recorded yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card p-3">
    <h6>Doctor Schedule <span class="text-muted small">(view: DoctorSchedule)</span></h6>
    <table class="table table-sm table-hover">
        <thead><tr><th>Doctor</th><th>Department</th><th>Date/Time</th><th>Patient</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($schedule as $s): ?>
            <tr>
                <td><?= e($s['doctor_name']) ?></td>
                <td><?= e($s['department']) ?></td>
                <td><?= e($s['appointment_date']) ?></td>
                <td><?= e($s['patient_name']) ?></td>
                <td><span class="badge bg-secondary"><?= e($s['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
