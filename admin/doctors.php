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
$pageIcon  = 'person-badge';
$pageSub   = 'Add doctors and assign them to departments';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3 p-md-4">
            <h6><i class="bi bi-<?= $edit ? 'pencil-square' : 'plus-circle' ?> text-primary"></i> <?= $edit ? 'Edit Doctor' : 'Add Doctor' ?></h6>
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
                <button class="btn btn-primary btn-sm"><i class="bi bi-<?= $edit ? 'check-lg' : 'plus-lg' ?>"></i> <?= $edit ? 'Update' : 'Add Doctor' ?></button>
                <?php if ($edit): ?><a class="btn btn-link btn-sm" href="doctors.php">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3 p-md-4">
            <h6><i class="bi bi-people-fill text-primary"></i> All Doctors <span class="badge bg-light text-muted ms-1"><?= count($doctors) ?></span></h6>
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Specialty</th><th>Department</th><th>Phone</th><th>Fee</th><th class="text-end"></th></tr>
                </thead>
                <tbody>
                <?php foreach ($doctors as $d): ?>
                    <tr>
                        <td class="fw-semibold">#<?= e($d['doctor_id']) ?></td>
                        <td><?= e($d['name']) ?></td>
                        <td><span class="badge bg-primary-subtle text-primary-emphasis"><?= e($d['specialty']) ?></span></td>
                        <td><?= e($d['department_name']) ?></td>
                        <td><?= e($d['phone']) ?></td>
                        <td class="fw-semibold">₨<?= e(number_format($d['consultation_fee'], 2)) ?></td>
                        <td class="text-end"><a class="btn btn-outline-secondary btn-sm" href="?edit=<?= e($d['doctor_id']) ?>"><i class="bi bi-pencil"></i> Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$doctors): ?><tr><td colspan="7" class="empty-row"><i class="bi bi-person-x"></i> No doctors yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
