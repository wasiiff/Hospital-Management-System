<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['receptionist']);

$msg = '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'book';
    if ($action === 'cancel') {
        cancelAppointment((int) $_POST['appointment_id']);
        $msg = 'Appointment cancelled.';
    } else {
        try {
            // Combine the date + time inputs into one DATETIME.
            $datetime = $_POST['appointment_date'] . ' ' . $_POST['appointment_time'] . ':00';
            $id = bookAppointment((int) $_POST['patient_id'], (int) $_POST['doctor_id'], $datetime);
            $msg = "Appointment #$id booked (bill auto-generated).";
        } catch (PDOException $e) {
            // The prevent_appointment_conflict trigger raises SQLSTATE 45000.
            $err = 'Could not book: ' . $e->getMessage();
        }
    }
}

$patients = searchPatients('');
$doctors  = getDoctors();
$schedule = getDoctorSchedule();

$pageTitle = 'Appointments';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Book Appointment</h6>
            <form method="post">
                <div class="mb-2">
                    <label class="form-label">Patient</label>
                    <select name="patient_id" class="form-select" required>
                        <option value="" disabled selected>Select patient</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?= e($p['patient_id']) ?>"><?= e($p['name']) ?> (<?= e($p['contact_number']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Doctor</label>
                    <select name="doctor_id" class="form-select" required>
                        <option value="" disabled selected>Select doctor</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?= e($d['doctor_id']) ?>"><?= e($d['name']) ?> &mdash; <?= e($d['specialty']) ?> (₨<?= e(number_format($d['consultation_fee'], 0)) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date</label>
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Time</label>
                    <input type="time" name="appointment_time" class="form-control" required>
                </div>
                <button class="btn btn-primary btn-sm">Book (via BookAppointment)</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3">
            <h6>Schedule</h6>
            <table class="table table-sm table-hover align-middle">
                <thead><tr><th>#</th><th>Doctor</th><th>Patient</th><th>Date/Time</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($schedule as $s): ?>
                    <tr>
                        <td><?= e($s['appointment_id']) ?></td>
                        <td><?= e($s['doctor_name']) ?></td>
                        <td><?= e($s['patient_name']) ?></td>
                        <td><?= e($s['appointment_date']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($s['status']) ?></span></td>
                        <td>
                            <?php if ($s['status'] !== 'Cancelled'): ?>
                                <form method="post" class="d-inline" onsubmit="return confirm('Cancel this appointment?');">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="appointment_id" value="<?= e($s['appointment_id']) ?>">
                                    <button class="btn btn-outline-danger btn-sm">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
