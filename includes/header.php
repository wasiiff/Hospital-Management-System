<?php
/**
 * Shared page chrome. Protected pages must call requireRole() BEFORE including
 * this file. Expects two optional variables to be set by the page:
 *   $pageTitle  - string shown in the navbar / <title>
 *   $base       - relative path to project root (e.g. '../'), default ''
 */
require_once __DIR__ . '/auth.php';

$base      = $base ?? '';
$pageTitle = $pageTitle ?? 'Hospital Management System';
$role      = currentRole();

/** Role-specific sidebar links: [label => path-relative-to-root]. */
$navByRole = [
    'admin' => [
        'Dashboard'   => 'admin/dashboard.php',
        'Doctors'     => 'admin/doctors.php',
        'Departments' => 'admin/departments.php',
        'Reports'     => 'admin/reports.php',
    ],
    'receptionist' => [
        'Dashboard'    => 'receptionist/dashboard.php',
        'Patients'     => 'receptionist/patients.php',
        'Appointments' => 'receptionist/appointments.php',
        'Billing'      => 'receptionist/billing.php',
    ],
    'doctor' => [
        'Dashboard'     => 'doctor/dashboard.php',
        'Appointments'  => 'doctor/dashboard.php',
        'Prescriptions' => 'doctor/prescriptions.php',
        'History'       => 'doctor/history.php',
    ],
];
$nav = $navByRole[$role] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark hms-navbar px-3">
    <span class="navbar-brand mb-0">&#9877; Global Hospital &mdash; HMS</span>
    <div class="d-flex align-items-center text-white gap-3">
        <span class="small text-capitalize"><?= e($role) ?>: <?= e($_SESSION['username'] ?? '') ?></span>
        <a class="btn btn-sm btn-outline-light" href="<?= $base ?>logout.php">Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <aside class="col-md-2 hms-sidebar py-3">
            <div class="text-uppercase text-muted small mb-2 px-2">Menu</div>
            <ul class="nav flex-column">
                <?php foreach ($nav as $label => $path): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base . e($path) ?>"><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <main class="col-md-10 py-4 px-4">
            <h3 class="mb-4"><?= e($pageTitle) ?></h3>
