<?php
/**
 * Shared page chrome. Protected pages must call requireRole() BEFORE including
 * this file. Expects optional variables set by the page:
 *   $pageTitle  - string shown in the page header / <title>
 *   $pageIcon   - Bootstrap Icons class for the page header (default 'grid')
 *   $pageSub    - optional subtitle line under the page title
 *   $base       - relative path to project root (e.g. '../'), default ''
 */
require_once __DIR__ . '/auth.php';

$base      = $base ?? '';
$pageTitle = $pageTitle ?? 'Hospital Management System';
$pageIcon  = $pageIcon ?? 'grid-1x2';
$pageSub   = $pageSub ?? '';
$role      = currentRole();
$username  = $_SESSION['username'] ?? '';

/** Role-specific sidebar links: [label => [path, icon]]. */
$navByRole = [
    'admin' => [
        'Dashboard'   => ['admin/dashboard.php',   'speedometer2'],
        'Doctors'     => ['admin/doctors.php',      'person-badge'],
        'Departments' => ['admin/departments.php',  'diagram-3'],
        'Reports'     => ['admin/reports.php',      'bar-chart-line'],
    ],
    'receptionist' => [
        'Dashboard'    => ['receptionist/dashboard.php',    'speedometer2'],
        'Patients'     => ['receptionist/patients.php',     'people'],
        'Appointments' => ['receptionist/appointments.php', 'calendar2-check'],
        'Billing'      => ['receptionist/billing.php',      'receipt'],
    ],
    'doctor' => [
        'My Appointments' => ['doctor/dashboard.php',      'calendar2-week'],
        'Prescriptions'   => ['doctor/prescriptions.php',  'capsule'],
        'Patient History' => ['doctor/history.php',        'clock-history'],
    ],
];
$nav     = $navByRole[$role] ?? [];
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');

/** First letter(s) for the avatar. */
$initials = strtoupper(substr($username, 0, 2));
$roleLabels = ['admin' => 'Administrator', 'receptionist' => 'Receptionist', 'doctor' => 'Doctor'];

/** Cache-busting version so CSS changes are picked up without a hard refresh. */
$cssPath = __DIR__ . '/../assets/css/style.css';
$cssVer  = is_file($cssPath) ? filemtime($cssPath) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= $base ?>assets/css/style.css?v=<?= $cssVer ?>" rel="stylesheet">
</head>
<body>
<aside class="hms-sidebar">
    <div class="hms-brand">
        <span class="hms-brand-logo"><i class="bi bi-heart-pulse-fill"></i></span>
        <div>
            <div class="hms-brand-name">Global Hospital</div>
            <div class="hms-brand-sub">Management System</div>
        </div>
    </div>

    <div class="menu-label">Main Menu</div>
    <ul class="nav flex-column">
        <?php foreach ($nav as $label => [$path, $icon]):
            $active = (basename($path) === $current) ? ' active' : ''; ?>
            <li class="nav-item">
                <a class="nav-link<?= $active ?>" href="<?= $base . e($path) ?>">
                    <i class="bi bi-<?= e($icon) ?>"></i> <?= e($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="hms-sidebar-footer">
        <div class="hms-user-chip">
            <span class="hms-avatar"><?= e($initials) ?></span>
            <div class="meta">
                <div class="name"><?= e($username) ?></div>
                <div class="role"><?= e($roleLabels[$role] ?? ucfirst($role)) ?></div>
            </div>
        </div>
        <a class="btn btn-sm btn-outline-danger w-100" href="<?= $base ?>logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</aside>

<main class="hms-main">
    <div class="page-head">
        <div class="icon"><i class="bi bi-<?= e($pageIcon) ?>"></i></div>
        <div>
            <h3><?= e($pageTitle) ?></h3>
            <?php if ($pageSub): ?><div class="sub"><?= e($pageSub) ?></div><?php endif; ?>
        </div>
    </div>
