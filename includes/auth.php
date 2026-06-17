<?php
/**
 * Authentication & session helpers.
 *
 * Passwords are verified against the SHA2-256 hashes stored in the Users table
 * (see database/hms.sql). Roles: admin, receptionist, doctor.
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Attempt login. Returns true and populates the session on success. */
function login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM Users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && hash('sha256', $password) === $user['password']) {
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['doctor_id'] = $user['doctor_id'];
        return true;
    }
    return false;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/** The logged-in doctor's id (null for admin/receptionist). */
function currentDoctorId(): ?int
{
    return isset($_SESSION['doctor_id']) ? (int) $_SESSION['doctor_id'] : null;
}

/** Default landing page for each role. */
function dashboardFor(string $role): string
{
    return match ($role) {
        'admin'        => 'admin/dashboard.php',
        'receptionist' => 'receptionist/dashboard.php',
        'doctor'       => 'doctor/dashboard.php',
        default        => 'index.php',
    };
}

/**
 * Guard a page. Call at the top of every protected page with the role(s)
 * allowed to view it. Redirects to login (or the user's own dashboard) on
 * mismatch. $base is the relative path back to the project root.
 */
function requireRole(array $roles, string $base = '../'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $base . 'index.php');
        exit;
    }
    if (!in_array(currentRole(), $roles, true)) {
        header('Location: ' . $base . dashboardFor(currentRole()));
        exit;
    }
}
