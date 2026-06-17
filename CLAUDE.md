# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Hospital Management System (academic "MySQL + Database Connectivity" project) built
with **PHP 8 + PDO + MySQL/MariaDB** and **Bootstrap 5**. The database is the centerpiece:
9 tables, 3 views, 3 stored procedures, and 2 triggers. Designed to run under XAMPP's
Apache from `htdocs/`. No framework, no build step, no automated tests.

## Running it

1. Start Apache + MySQL in XAMPP.
2. Import `database/hms.sql` (phpMyAdmin Import, or `mysql -u root -p < database/hms.sql`).
   It **drops and recreates** the `hms` database with all schema objects + seed data.
3. Serve the folder from `htdocs/` and open `http://localhost/<folder>/`.
4. DB credentials live in **`config/database.php`** (default `root` / no password / db `hms`).

There is no test runner — verify changes manually in the browser. Demo logins are in
`README.md` (e.g. `admin/admin123`, `reception/reception123`, `ashok/doctor123`).

## Architecture

Three layers, cleanly separated:

1. **`database/hms.sql`** — the single source of truth for the schema. Object creation
   order matters and is deliberate: tables → triggers → procedures → views → seed data
   (seed `INSERT`s into `Appointments` fire the triggers; seed `CALL`s use the procedures).
   If you add a procedure that seed data calls, define it *before* the seed section.

2. **Connectivity layer** (`config/` + `includes/`):
   - `config/database.php` — `db()` returns a singleton PDO (exceptions on, real prepares).
   - `includes/auth.php` — login/session/role logic. Passwords are **SHA2-256** hashes
     (verified with `hash('sha256', ...)` against the `Users.password` column). Every
     protected page calls `requireRole([...])` at the top.
   - `includes/functions.php` — **all** database operations live here as functions using
     prepared statements and `CALL` for stored procedures. UI pages never write SQL directly.
     `e()` (HTML-escape) is also defined here.
   - `includes/header.php` / `footer.php` — shared chrome; the navbar/sidebar is driven by
     `currentRole()`.

3. **Role pages** — one folder per role: `admin/`, `receptionist/`, `doctor/`. Each page
   is a thin controller: handle `$_POST`, call functions, render with the shared header/footer.
   Pages in these subfolders set `$base = '../'` and include files via `__DIR__` relative paths.

### Page convention (follow this for new pages)
```php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['receptionist']);          // role guard FIRST
// ... handle POST, fetch data ...
$pageTitle = '...'; $base = '../';
require __DIR__ . '/../includes/header.php';
// ... HTML ...
require __DIR__ . '/../includes/footer.php';
```
`functions.php` must be included before `header.php` because the header uses `e()`.

### Database objects worth knowing
- **Triggers do real work.** Booking an appointment auto-creates a `Bills` row
  (`auto_generate_bill`) and a same-doctor/same-time clash is rejected at the DB level
  (`prevent_appointment_conflict`, raises `SQLSTATE '45000'`). Code that books appointments
  must catch `PDOException` to surface the conflict (see `receptionist/appointments.php`).
- **Bill lifecycle:** the trigger seeds a bill with the consultation fee; `GenerateBill()`
  recomputes `amount = consultation_fee + SUM(prescribed medicine prices)`. So adding a
  prescription should be followed by `generateBill($appointmentId)` to keep totals correct
  (the doctor prescriptions page does this).
- **Payment status** (`Pending`/`Partial`/`Paid`) is computed in PHP in `recordPayment()`
  by comparing total payments to the bill amount — it is not a trigger.
- **Views** back the read-heavy screens: `DoctorSchedule` (admin reports / reception
  schedule), `PatientHistory` (doctor history), `MonthlyRevenue` (admin reports).

## Conventions and cautions

- **Always use prepared statements / the existing `functions.php` helpers.** Do not build
  SQL by string interpolation. This is a clean rewrite specifically to avoid the SQL-injection
  patterns of the original project.
- Calling a stored procedure that returns a result set via PDO requires
  `$stmt->closeCursor()` after fetching (see `bookAppointment` / `getDoctorAppointments`).
- Money is `DECIMAL(10,2)`; format for display with `number_format(...)`.
- A doctor user is linked to a `Doctors` row via `Users.doctor_id`; in code use
  `currentDoctorId()`. Admin/receptionist users have `doctor_id = NULL`.
- The `hms` database is dropped and recreated by the SQL file — never put data you want to
  keep only in the running DB; reflect schema changes in `database/hms.sql`.
