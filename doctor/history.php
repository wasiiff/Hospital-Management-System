<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['doctor']);

$patientId = (int) ($_GET['patient_id'] ?? 0);
$patient   = $patientId ? getPatient($patientId) : null;
$history   = $patientId ? getPatientHistory($patientId) : [];  // PatientHistory view
$patients  = searchPatients('');

$pageTitle = 'Patient History';
$pageIcon  = 'clock-history';
$pageSub   = 'Review a patient\'s appointments and prescriptions';
$base = '../';
require __DIR__ . '/../includes/header.php';

$statusBadge = ['Scheduled' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger'];
?>
<div class="card p-3 p-md-4 mb-3">
    <form class="d-flex gap-2 align-items-end flex-wrap" method="get">
        <div style="min-width:240px">
            <label class="form-label">Patient</label>
            <select name="patient_id" class="form-select">
                <option value="">Select patient</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= e($p['patient_id']) ?>" <?= $patientId === (int) $p['patient_id'] ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary"><i class="bi bi-eye"></i> View History</button>
    </form>
</div>

<?php if ($patient): ?>
<div class="card p-3 p-md-4">
    <h6><i class="bi bi-file-medical text-primary"></i> Medical History &mdash; <?= e($patient['name']) ?> <span class="text-muted small fw-normal">view: PatientHistory</span></h6>
    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Appt</th><th>Date</th><th>Doctor</th><th>Status</th><th>Medicine</th><th>Dosage</th><th>Instructions</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
            <tr>
                <td class="fw-semibold">#<?= e($h['appointment_id']) ?></td>
                <td><?= e($h['appointment_date']) ?></td>
                <td><?= e($h['doctor_name']) ?></td>
                <td><span class="badge rounded-pill bg-<?= $statusBadge[$h['status']] ?? 'secondary' ?>"><?= e($h['status']) ?></span></td>
                <td><?= e($h['medicine_name'] ?? '—') ?></td>
                <td><?= e($h['dosage'] ?? '—') ?></td>
                <td class="text-muted"><?= e($h['instructions'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$history): ?><tr><td colspan="7" class="empty-row"><i class="bi bi-file-earmark-x"></i> No history found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php else: ?>
<div class="card p-4 text-center text-muted">
    <div style="font-size:2.5rem"><i class="bi bi-person-vcard"></i></div>
    Select a patient above to view their medical history.
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
