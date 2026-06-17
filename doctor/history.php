<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['doctor']);

$patientId = (int) ($_GET['patient_id'] ?? 0);
$patient   = $patientId ? getPatient($patientId) : null;
$history   = $patientId ? getPatientHistory($patientId) : [];  // PatientHistory view
$patients  = searchPatients('');

$pageTitle = 'Patient History';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<div class="card p-3 mb-3">
    <form class="d-flex gap-2 align-items-end" method="get">
        <div>
            <label class="form-label small">Patient</label>
            <select name="patient_id" class="form-select form-select-sm">
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= e($p['patient_id']) ?>" <?= $patientId === (int) $p['patient_id'] ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary btn-sm">View</button>
    </form>
</div>

<?php if ($patient): ?>
<div class="card p-3">
    <h6>Medical History &mdash; <?= e($patient['name']) ?> <span class="text-muted small">(view: PatientHistory)</span></h6>
    <table class="table table-sm table-hover">
        <thead><tr><th>Appt</th><th>Date</th><th>Doctor</th><th>Status</th><th>Medicine</th><th>Dosage</th><th>Instructions</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
            <tr>
                <td><?= e($h['appointment_id']) ?></td>
                <td><?= e($h['appointment_date']) ?></td>
                <td><?= e($h['doctor_name']) ?></td>
                <td><?= e($h['status']) ?></td>
                <td><?= e($h['medicine_name'] ?? '-') ?></td>
                <td><?= e($h['dosage'] ?? '-') ?></td>
                <td><?= e($h['instructions'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$history): ?><tr><td colspan="7" class="text-muted">No history found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
