<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['receptionist']);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'generate') {
        generateBill((int) $_POST['appointment_id']);
        $msg = 'Bill regenerated (consultation fee + medicines).';
    } elseif ($action === 'pay') {
        recordPayment((int) $_POST['bill_id'], (float) $_POST['amount_paid'], $_POST['payment_method']);
        $msg = 'Payment recorded.';
    }
}

$bills = getBills();

$pageTitle = 'Billing & Payments';
$base = '../';
require __DIR__ . '/../includes/header.php';
?>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= e($msg) ?></div><?php endif; ?>

<div class="card p-3">
    <h6>Bills</h6>
    <table class="table table-sm table-hover align-middle">
        <thead>
            <tr><th>Bill</th><th>Patient</th><th>Appt</th><th>Amount</th><th>Paid</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($bills as $b): ?>
            <tr>
                <td><?= e($b['bill_id']) ?></td>
                <td><?= e($b['patient_name']) ?></td>
                <td><?= e($b['appointment_id']) ?></td>
                <td>₨<?= e(number_format($b['amount'], 2)) ?></td>
                <td>₨<?= e(number_format($b['paid'], 2)) ?></td>
                <td>
                    <?php
                    $cls = ['Paid' => 'success', 'Partial' => 'warning', 'Pending' => 'secondary'][$b['payment_status']];
                    ?>
                    <span class="badge bg-<?= $cls ?>"><?= e($b['payment_status']) ?></span>
                </td>
                <td class="text-nowrap">
                    <?php if ($b['appointment_id']): ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="generate">
                            <input type="hidden" name="appointment_id" value="<?= e($b['appointment_id']) ?>">
                            <button class="btn btn-outline-secondary btn-sm" title="Recalculate via GenerateBill()">Regenerate</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($b['payment_status'] !== 'Paid'): ?>
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#pay<?= e($b['bill_id']) ?>">Pay</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($b['payment_status'] !== 'Paid'): ?>
            <tr class="collapse" id="pay<?= e($b['bill_id']) ?>">
                <td colspan="7">
                    <form method="post" class="row g-2 align-items-end">
                        <input type="hidden" name="action" value="pay">
                        <input type="hidden" name="bill_id" value="<?= e($b['bill_id']) ?>">
                        <div class="col-auto">
                            <label class="form-label small">Amount</label>
                            <input type="number" step="0.01" name="amount_paid" class="form-control form-control-sm"
                                   value="<?= e(number_format(max(0, $b['amount'] - $b['paid']), 2, '.', '')) ?>" required>
                        </div>
                        <div class="col-auto">
                            <label class="form-label small">Method</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option>Cash</option><option>Card</option><option>UPI</option><option>Insurance</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm">Record Payment</button>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (!$bills): ?><tr><td colspan="7" class="text-muted">No bills yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
