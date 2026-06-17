<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['doctor']);

$doctorId = currentDoctorId();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'complete') {
    completeAppointment((int) $_POST['appointment_id']);
    $msg = 'Appointment marked completed.';
}

$appointments = getDoctorAppointments($doctorId);  // CALL GetDoctorAppointments

$pageTitle = 'My Appointments';
$pageIcon  = 'calendar2-week';
$pageSub   = 'Your scheduled patient appointments';
$base = '../';
require __DIR__ . '/../includes/header.php';

$statusBadge = ['Scheduled' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger'];
?>
<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($msg) ?></div><?php endif; ?>

<div class="card p-3 p-md-4">
    <h6><i class="bi bi-calendar2-check text-primary"></i> Appointments</h6>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>#</th><th>Patient</th><th>Gender</th><th>Contact</th><th>Date/Time</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td class="fw-semibold">#<?= e($a['appointment_id']) ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td><?= e($a['gender']) ?></td>
                <td><?= e($a['contact_number']) ?></td>
                <td><?= e($a['appointment_date']) ?></td>
                <td><span class="badge rounded-pill bg-<?= $statusBadge[$a['status']] ?? 'secondary' ?>"><?= e($a['status']) ?></span></td>
                <td class="text-nowrap text-end">
                    <a class="btn btn-outline-primary btn-sm"
                       href="prescriptions.php?appointment_id=<?= e($a['appointment_id']) ?>"><i class="bi bi-capsule"></i> Prescribe</a>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="history.php?patient_id=<?= e($a['patient_id']) ?>"><i class="bi bi-clock-history"></i> History</a>
                    <?php if ($a['status'] === 'Scheduled'): ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="appointment_id" value="<?= e($a['appointment_id']) ?>">
                            <button class="btn btn-outline-success btn-sm"><i class="bi bi-check-lg"></i> Complete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$appointments): ?><tr><td colspan="7" class="empty-row"><i class="bi bi-calendar-x"></i> No appointments.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
