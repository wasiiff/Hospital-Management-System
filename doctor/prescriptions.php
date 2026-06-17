<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['doctor']);

$doctorId = currentDoctorId();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apptId = (int) $_POST['appointment_id'];
    addPrescription($apptId, (int) $_POST['medicine_id'], trim($_POST['dosage']), trim($_POST['instructions']));
    // Keep the bill in sync with the new medicine cost.
    generateBill($apptId);
    $msg = 'Prescription added and bill updated.';
}

// Build a quick map of this doctor's appointments for the selector.
$appointments  = getDoctorAppointments($doctorId);
$medicines     = getMedicines();
$selectedAppt  = (int) ($_GET['appointment_id'] ?? $_POST['appointment_id'] ?? 0);

$pageTitle = 'Add Prescription';
$pageIcon  = 'capsule';
$pageSub   = 'Prescribe medicines for an appointment';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3 p-md-4">
            <h6><i class="bi bi-capsule text-primary"></i> New Prescription</h6>
            <form method="post">
                <div class="mb-2">
                    <label class="form-label">Appointment</label>
                    <select name="appointment_id" class="form-select" required>
                        <option value="" disabled <?= $selectedAppt ? '' : 'selected' ?>>Select appointment</option>
                        <?php foreach ($appointments as $a): ?>
                            <option value="<?= e($a['appointment_id']) ?>" <?= $selectedAppt === (int) $a['appointment_id'] ? 'selected' : '' ?>>
                                #<?= e($a['appointment_id']) ?> &mdash; <?= e($a['patient_name']) ?> (<?= e($a['appointment_date']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Medicine</label>
                    <select name="medicine_id" class="form-select" required>
                        <?php foreach ($medicines as $m): ?>
                            <option value="<?= e($m['medicine_id']) ?>"><?= e($m['name']) ?> (₨<?= e(number_format($m['price'], 2)) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Dosage</label>
                    <input name="dosage" class="form-control" placeholder="e.g. 1 tablet twice a day">
                </div>
                <div class="mb-2">
                    <label class="form-label">Instructions</label>
                    <textarea name="instructions" class="form-control" rows="2" placeholder="e.g. After meals"></textarea>
                </div>
                <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Prescription</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card p-3 p-md-4">
            <h6><i class="bi bi-prescription2 text-primary"></i> Available Medicines</h6>
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Name</th><th>Description</th><th class="text-end">Price</th></tr></thead>
                <tbody>
                <?php foreach ($medicines as $m): ?>
                    <tr><td class="fw-semibold"><i class="bi bi-capsule-pill text-primary"></i> <?= e($m['name']) ?></td><td class="text-muted"><?= e($m['description']) ?></td><td class="text-end">₨<?= e(number_format($m['price'], 2)) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$medicines): ?><tr><td colspan="3" class="empty-row">No medicines available.</td></tr><?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
