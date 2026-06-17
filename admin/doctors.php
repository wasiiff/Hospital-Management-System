<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name']);
    $specialty = trim($_POST['specialty']);
    $phone     = trim($_POST['phone']);
    $deptId    = (int) $_POST['department_id'];
    $fee       = (float) $_POST['consultation_fee'];
    $editId    = (int) ($_POST['doctor_id'] ?? 0);

    if ($editId > 0) {
        updateDoctor($editId, $name, $specialty, $phone, $deptId, $fee);
        $msg = 'Doctor updated.';
    } else {
        addDoctor($name, $specialty, $phone, $deptId, $fee);
        $msg = 'Doctor added.';
    }
}

$departments = getDepartments();
$doctors     = getDoctors();
$edit        = isset($_GET['edit']) ? getDoctor((int) $_GET['edit']) : null;

$pageTitle = 'Doctors';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6><?= $edit ? 'Edit Doctor' : 'Add Doctor' ?></h6>
            <form method="post">
                <input type="hidden" name="doctor_id" value="<?= e($edit['doctor_id'] ?? '') ?>">
                <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="<?= e($edit['name'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Specialty</label>
                    <input name="specialty" class="form-control" value="<?= e($edit['specialty'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" value="<?= e($edit['phone'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= e($d['department_id']) ?>"
                                <?= ($edit && $edit['department_id'] == $d['department_id']) ? 'selected' : '' ?>>
                                <?= e($d['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Consultation Fee (₨)</label>
                    <input type="number" step="0.01" name="consultation_fee" class="form-control"
                           value="<?= e($edit['consultation_fee'] ?? '') ?>" required>
                </div>
                <button class="btn btn-primary btn-sm"><?= $edit ? 'Update' : 'Add' ?></button>
                <?php if ($edit): ?><a class="btn btn-link btn-sm" href="doctors.php">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3">
            <h6>All Doctors</h6>
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Specialty</th><th>Department</th><th>Phone</th><th>Fee</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($doctors as $d): ?>
                    <tr>
                        <td><?= e($d['doctor_id']) ?></td>
                        <td><?= e($d['name']) ?></td>
                        <td><?= e($d['specialty']) ?></td>
                        <td><?= e($d['department_name']) ?></td>
                        <td><?= e($d['phone']) ?></td>
                        <td>₨<?= e(number_format($d['consultation_fee'], 2)) ?></td>
                        <td><a class="btn btn-outline-secondary btn-sm" href="?edit=<?= e($d['doctor_id']) ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
