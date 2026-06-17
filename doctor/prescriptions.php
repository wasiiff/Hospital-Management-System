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
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-3">
            <h6>New Prescription</h6>
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
                <button class="btn btn-primary btn-sm">Add Prescription</button>
            </form>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card p-3">
            <h6>Available Medicines</h6>
            <table class="table table-sm">
                <thead><tr><th>Name</th><th>Description</th><th>Price</th></tr></thead>
                <tbody>
                <?php foreach ($medicines as $m): ?>
                    <tr><td><?= e($m['name']) ?></td><td><?= e($m['description']) ?></td><td>₨<?= e(number_format($m['price'], 2)) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
