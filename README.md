# Hospital Management System

A Hospital Management System built with **MySQL + PHP (PDO)**, focused on database
design and connectivity. It manages patients, doctors, departments, appointments,
prescriptions, billing, and payments across three user roles.

## Features by role

| Role | Capabilities |
|------|--------------|
| **Administrator** | Add/update doctors, assign departments, manage departments, view reports (revenue + doctor schedule) |
| **Receptionist** | Register / update / search / delete patients, schedule & cancel appointments, generate bills, record payments |
| **Doctor** | View own appointments, add prescriptions, update treatment status, view patient medical history |

## Localization

This version is **localized for Pakistan** with:
- Pakistani doctor and patient names
- Pakistani addresses (Lahore, Karachi, Islamabad)
- Pakistani phone numbers (03XX format)
- **Currency: Pakistani Rupee (₨)** — consultation fees and medicine prices in PKR

## Database

The entire schema lives in [`database/hms.sql`](database/hms.sql).

**Tables (9):** `Departments`, `Doctors`, `Patients`, `Medicines`, `Appointments`,
`Prescriptions`, `Bills`, `Payments`, `Users` (authentication).

**Relationships:**
- One Department → Many Doctors
- One Patient → Many Appointments
- One Doctor → Many Appointments
- One Appointment → Many Prescriptions
- One Patient → Many Bills
- One Bill → Many Payments

**Views:** `DoctorSchedule`, `PatientHistory`, `MonthlyRevenue`

**Stored Procedures:** `BookAppointment()`, `GenerateBill()`, `GetDoctorAppointments()`

**Triggers:**
- `auto_generate_bill` — automatically creates a bill (with the doctor's fee) after every appointment is inserted.
- `prevent_appointment_conflict` — rejects a booking if the doctor already has an active appointment at that exact date & time.

## Tech stack

- **MySQL / MariaDB** (XAMPP)
- **PHP 8** with **PDO** and prepared statements
- **Bootstrap 5** (CDN) for the UI

## Security

- **No raw SQL from string interpolation** — every database operation goes through
  prepared statements (or `CALL` for stored procedures) in `includes/functions.php`.
- **Role-based access control** — each protected page calls `requireRole([...])` at the
  top, driven by the logged-in user's role.
- **Hashed credentials** — user passwords are stored as SHA2-256 hashes, never plaintext.
- **Output escaping** — values rendered into HTML are escaped via the `e()` helper.

## Setup

1. Install **XAMPP** and start **Apache** + **MySQL**.
2. Copy this project into XAMPP's `htdocs/` (e.g. `htdocs/hms`).
3. Import the database — the SQL file automatically drops and recreates the `hms` database:
   - **phpMyAdmin:** 
     - Go to `http://localhost/phpmyadmin`
     - Click "Import" tab
     - Choose `database/hms.sql` from this project
     - Click "Go"
   - **Command line:** 
     ```bash
     mysql -u root -p < database/hms.sql
     # (press Enter for blank password, or enter your MySQL password)
     ```

   The script includes all views, procedures, triggers, and sample Pakistani seed data.

4. If your MySQL credentials differ from the XAMPP default (`root` / blank password),
   edit [`config/database.php`](config/database.php).
5. Open `http://localhost/hms/` in a browser and log in with a demo account (see below).

## Demo accounts

| Role | Username | Password | Notes |
|------|----------|----------|-------|
| Administrator | `admin` | `admin123` | Full system access |
| Receptionist | `reception` | `reception123` | Patient & appointment management |
| Doctor | `hassan` | `doctor123` | Dr. Muhammad Hassan (Cardiologist) |
| Doctor | `hasan_raza` | `doctor123` | Dr. Hassan Raza (Pediatrician) |

Passwords are stored as SHA2-256 hashes in the `Users` table.

## Project structure

```
config/database.php      PDO connection (singleton)
includes/auth.php        Login, sessions, role guards
includes/functions.php   All DB operations (the "connectivity" layer)
includes/header.php      Shared navbar + role sidebar
includes/footer.php      Shared footer
index.php                Login page
logout.php               Session teardown
admin/                   Admin pages (dashboard, doctors, departments, reports)
receptionist/            Reception pages (dashboard, patients, appointments, billing)
doctor/                  Doctor pages (dashboard, prescriptions, history)
database/hms.sql         Complete schema: tables, views, procedures, triggers, seed data
assets/css/style.css     Custom styling
```

## How the required operations map to code

| Operation | Where |
|-----------|-------|
| Register / update / search / delete patient | `searchPatients`, `registerPatient`, `updatePatient`, `deletePatient` in `includes/functions.php` |
| Add / update doctor, assign department | `addDoctor`, `updateDoctor` |
| Schedule appointment | `bookAppointment` → `CALL BookAppointment()` |
| Add prescription | `addPrescription` |
| Generate bill | `generateBill` → `CALL GenerateBill()` (plus the `auto_generate_bill` trigger) |
| Record payment | `recordPayment` |
| Search patient history | `getPatientHistory` → `PatientHistory` view |
| Doctor's appointments | `getDoctorAppointments` → `CALL GetDoctorAppointments()` |
| Reports | `getMonthlyRevenue` → `MonthlyRevenue` view, `getDoctorSchedule` → `DoctorSchedule` view |
