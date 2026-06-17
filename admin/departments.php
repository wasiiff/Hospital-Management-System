<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    addDepartment(trim($_POST['name']), trim($_POST['description']));
    $msg = 'Department added.';
}
$departments = getDepartments();

$pageTitle = 'Departments';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-3">
            <h6>Add Department</h6>
            <form method="post">
                <div class="mb-2">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <button class="btn btn-primary btn-sm">Add</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-3">
            <h6>All Departments</h6>
            <table class="table table-sm table-hover">
                <thead><tr><th>ID</th><th>Name</th><th>Description</th></tr></thead>
                <tbody>
                <?php foreach ($departments as $d): ?>
                    <tr>
                        <td><?= e($d['department_id']) ?></td>
                        <td><?= e($d['name']) ?></td>
                        <td><?= e($d['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
