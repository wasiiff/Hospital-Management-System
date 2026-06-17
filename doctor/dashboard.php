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
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>

<div class="card p-3">
    <h6>Appointments <span class="text-muted small">(procedure: GetDoctorAppointments)</span></h6>
    <table class="table table-sm table-hover align-middle">
        <thead><tr><th>#</th><th>Patient</th><th>Gender</th><th>Contact</th><th>Date/Time</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?= e($a['appointment_id']) ?></td>
                <td><?= e($a['patient_name']) ?></td>
                <td><?= e($a['gender']) ?></td>
                <td><?= e($a['contact_number']) ?></td>
                <td><?= e($a['appointment_date']) ?></td>
                <td><span class="badge bg-secondary"><?= e($a['status']) ?></span></td>
                <td class="text-nowrap">
                    <a class="btn btn-outline-primary btn-sm"
                       href="prescriptions.php?appointment_id=<?= e($a['appointment_id']) ?>">Prescribe</a>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="history.php?patient_id=<?= e($a['patient_id']) ?>">History</a>
                    <?php if ($a['status'] === 'Scheduled'): ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="appointment_id" value="<?= e($a['appointment_id']) ?>">
                            <button class="btn btn-outline-success btn-sm">Complete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$appointments): ?><tr><td colspan="7" class="text-muted">No appointments.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
