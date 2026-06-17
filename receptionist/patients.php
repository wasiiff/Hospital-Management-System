<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['receptionist']);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        deletePatient((int) $_POST['patient_id']);
        $msg = 'Patient deleted.';
    } else {
        $id      = (int) ($_POST['patient_id'] ?? 0);
        $name    = trim($_POST['name']);
        $gender  = $_POST['gender'];
        $dob     = $_POST['date_of_birth'];
        $contact = trim($_POST['contact_number']);
        $addr    = trim($_POST['address']);
        if ($id > 0) {
            updatePatient($id, $name, $gender, $dob, $contact, $addr);
            $msg = 'Patient updated.';
        } else {
            registerPatient($name, $gender, $dob, $contact, $addr);
            $msg = 'Patient registered.';
        }
    }
}

$term     = trim($_GET['q'] ?? '');
$patients = searchPatients($term);
$edit     = isset($_GET['edit']) ? getPatient((int) $_GET['edit']) : null;

$pageTitle = 'Patients';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6><?= $edit ? 'Edit Patient' : 'Register Patient' ?></h6>
            <form method="post">
                <input type="hidden" name="patient_id" value="<?= e($edit['patient_id'] ?? '') ?>">
                <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="<?= e($edit['name'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                            <option <?= ($edit && $edit['gender'] === $g) ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?= e($edit['date_of_birth'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Contact Number</label>
                    <input name="contact_number" class="form-control" value="<?= e($edit['contact_number'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= e($edit['address'] ?? '') ?></textarea>
                </div>
                <button class="btn btn-primary btn-sm"><?= $edit ? 'Update' : 'Register' ?></button>
                <?php if ($edit): ?><a class="btn btn-link btn-sm" href="patients.php">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3">
            <form class="d-flex gap-2 mb-3" method="get">
                <input name="q" class="form-control form-control-sm" placeholder="Search by name or contact" value="<?= e($term) ?>">
                <button class="btn btn-outline-primary btn-sm">Search</button>
                <?php if ($term): ?><a class="btn btn-link btn-sm" href="patients.php">Clear</a><?php endif; ?>
            </form>
            <table class="table table-sm table-hover align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Gender</th><th>DOB</th><th>Contact</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><?= e($p['patient_id']) ?></td>
                        <td><?= e($p['name']) ?></td>
                        <td><?= e($p['gender']) ?></td>
                        <td><?= e($p['date_of_birth']) ?></td>
                        <td><?= e($p['contact_number']) ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-outline-secondary btn-sm" href="?edit=<?= e($p['patient_id']) ?>">Edit</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this patient?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="patient_id" value="<?= e($p['patient_id']) ?>">
                                <button class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$patients): ?><tr><td colspan="6" class="text-muted">No patients found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
