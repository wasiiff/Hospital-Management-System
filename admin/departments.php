<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $desc   = trim($_POST['description']);
    $editId = (int) ($_POST['department_id'] ?? 0);
    if ($editId > 0) {
        updateDepartment($editId, $name, $desc);
        $msg = 'Department updated.';
    } else {
        addDepartment($name, $desc);
        $msg = 'Department added.';
    }
}
$departments = getDepartments();
$edit        = isset($_GET['edit']) ? getDepartment((int) $_GET['edit']) : null;

$pageTitle = 'Departments';
$pageIcon  = 'diagram-3';
$pageSub   = 'Organize the hospital into specialties';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3 p-md-4">
            <h6><i class="bi bi-<?= $edit ? 'pencil-square' : 'plus-circle' ?> text-primary"></i> <?= $edit ? 'Edit Department' : 'Add Department' ?></h6>
            <form method="post">
                <input type="hidden" name="department_id" value="<?= e($edit['department_id'] ?? '') ?>">
                <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" placeholder="e.g. Cardiology" value="<?= e($edit['name'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= e($edit['description'] ?? '') ?></textarea>
                </div>
                <button class="btn btn-primary btn-sm"><i class="bi bi-<?= $edit ? 'check-lg' : 'plus-lg' ?>"></i> <?= $edit ? 'Update' : 'Add Department' ?></button>
                <?php if ($edit): ?><a class="btn btn-link btn-sm" href="departments.php">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3 p-md-4">
            <h6><i class="bi bi-diagram-3-fill text-primary"></i> All Departments <span class="badge bg-light text-muted ms-1"><?= count($departments) ?></span></h6>
            <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Description</th><th class="text-end"></th></tr></thead>
                <tbody>
                <?php foreach ($departments as $d): ?>
                    <tr>
                        <td class="fw-semibold">#<?= e($d['department_id']) ?></td>
                        <td><i class="bi bi-hospital text-primary"></i> <?= e($d['name']) ?></td>
                        <td class="text-muted"><?= e($d['description']) ?></td>
                        <td class="text-end"><a class="btn btn-outline-secondary btn-sm" href="?edit=<?= e($d['department_id']) ?>"><i class="bi bi-pencil"></i> Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$departments): ?><tr><td colspan="4" class="empty-row">No departments yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
