<?php
/**
 * Database operations layer ("connectivity").
 *
 * Every required operation in the spec is implemented here using PDO prepared
 * statements, stored-procedure calls (CALL ...), and the SQL views. UI pages
 * call these functions; they never write SQL directly.
 */

require_once __DIR__ . '/../config/database.php';

/* -------------------------------------------------------------------------
 *  PATIENT MANAGEMENT
 * ---------------------------------------------------------------------- */

function registerPatient(string $name, string $gender, ?string $dob, ?string $contact, ?string $address): int
{
    $stmt = db()->prepare(
        'INSERT INTO Patients(name, gender, date_of_birth, contact_number, address)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $gender, $dob ?: null, $contact, $address]);
    return (int) db()->lastInsertId();
}

function updatePatient(int $id, string $name, string $gender, ?string $dob, ?string $contact, ?string $address): void
{
    $stmt = db()->prepare(
        'UPDATE Patients
         SET name = ?, gender = ?, date_of_birth = ?, contact_number = ?, address = ?
         WHERE patient_id = ?'
    );
    $stmt->execute([$name, $gender, $dob ?: null, $contact, $address, $id]);
}

function deletePatient(int $id): void
{
    $stmt = db()->prepare('DELETE FROM Patients WHERE patient_id = ?');
    $stmt->execute([$id]);
}

function getPatient(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM Patients WHERE patient_id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** Search by name or contact number; empty term returns all patients. */
function searchPatients(string $term = ''): array
{
    if ($term === '') {
        return db()->query('SELECT * FROM Patients ORDER BY patient_id DESC')->fetchAll();
    }
    $stmt = db()->prepare(
        'SELECT * FROM Patients
         WHERE name LIKE ? OR contact_number LIKE ?
         ORDER BY patient_id DESC'
    );
    $like = '%' . $term . '%';
    $stmt->execute([$like, $like]);
    return $stmt->fetchAll();
}

/* -------------------------------------------------------------------------
 *  DEPARTMENT & DOCTOR MANAGEMENT
 * ---------------------------------------------------------------------- */

function getDepartments(): array
{
    return db()->query('SELECT * FROM Departments ORDER BY name')->fetchAll();
}

function addDepartment(string $name, ?string $description): void
{
    $stmt = db()->prepare('INSERT INTO Departments(name, description) VALUES (?, ?)');
    $stmt->execute([$name, $description]);
}

function getDepartment(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM Departments WHERE department_id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function updateDepartment(int $id, string $name, ?string $description): void
{
    $stmt = db()->prepare('UPDATE Departments SET name = ?, description = ? WHERE department_id = ?');
    $stmt->execute([$name, $description, $id]);
}

function getDoctors(): array
{
    return db()->query(
        'SELECT d.*, dept.name AS department_name
         FROM Doctors d
         JOIN Departments dept ON d.department_id = dept.department_id
         ORDER BY d.name'
    )->fetchAll();
}

function getDoctor(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM Doctors WHERE doctor_id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function addDoctor(string $name, string $specialty, ?string $phone, int $departmentId, float $fee): int
{
    $stmt = db()->prepare(
        'INSERT INTO Doctors(name, specialty, phone, department_id, consultation_fee)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $specialty, $phone, $departmentId, $fee]);
    return (int) db()->lastInsertId();
}

function updateDoctor(int $id, string $name, string $specialty, ?string $phone, int $departmentId, float $fee): void
{
    $stmt = db()->prepare(
        'UPDATE Doctors
         SET name = ?, specialty = ?, phone = ?, department_id = ?, consultation_fee = ?
         WHERE doctor_id = ?'
    );
    $stmt->execute([$name, $specialty, $phone, $departmentId, $fee, $id]);
}

/* -------------------------------------------------------------------------
 *  APPOINTMENT MANAGEMENT
 * ---------------------------------------------------------------------- */

/**
 * Book an appointment via the BookAppointment() stored procedure.
 * The DB triggers reject time conflicts and auto-create the bill.
 * Returns the new appointment id. Throws PDOException on conflict.
 */
function bookAppointment(int $patientId, int $doctorId, string $datetime): int
{
    $stmt = db()->prepare('CALL BookAppointment(?, ?, ?)');
    $stmt->execute([$patientId, $doctorId, $datetime]);
    $row = $stmt->fetch();
    $stmt->closeCursor();
    return (int) $row['appointment_id'];
}

function cancelAppointment(int $appointmentId): void
{
    $stmt = db()->prepare("UPDATE Appointments SET status = 'Cancelled' WHERE appointment_id = ?");
    $stmt->execute([$appointmentId]);
}

function completeAppointment(int $appointmentId): void
{
    $stmt = db()->prepare("UPDATE Appointments SET status = 'Completed' WHERE appointment_id = ?");
    $stmt->execute([$appointmentId]);
}

/** Full schedule across all doctors (DoctorSchedule view). */
function getDoctorSchedule(): array
{
    return db()->query(
        'SELECT * FROM DoctorSchedule
         WHERE appointment_id IS NOT NULL
         ORDER BY appointment_date'
    )->fetchAll();
}

/** Appointments for one doctor via the GetDoctorAppointments() procedure. */
function getDoctorAppointments(int $doctorId): array
{
    $stmt = db()->prepare('CALL GetDoctorAppointments(?)');
    $stmt->execute([$doctorId]);
    $rows = $stmt->fetchAll();
    $stmt->closeCursor();
    return $rows;
}

/* -------------------------------------------------------------------------
 *  PRESCRIPTION MANAGEMENT
 * ---------------------------------------------------------------------- */

function getMedicines(): array
{
    return db()->query('SELECT * FROM Medicines ORDER BY name')->fetchAll();
}

function addPrescription(int $appointmentId, int $medicineId, ?string $dosage, ?string $instructions): void
{
    $stmt = db()->prepare(
        'INSERT INTO Prescriptions(appointment_id, medicine_id, dosage, instructions)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$appointmentId, $medicineId, $dosage, $instructions]);
}

/** Medical history for one patient (PatientHistory view). */
function getPatientHistory(int $patientId): array
{
    $stmt = db()->prepare(
        'SELECT * FROM PatientHistory WHERE patient_id = ? ORDER BY appointment_date DESC'
    );
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

/* -------------------------------------------------------------------------
 *  BILLING & PAYMENTS
 * ---------------------------------------------------------------------- */

/** Recompute a bill total (fee + medicines) via the GenerateBill() procedure. */
function generateBill(int $appointmentId): void
{
    $stmt = db()->prepare('CALL GenerateBill(?)');
    $stmt->execute([$appointmentId]);
    $stmt->closeCursor();
}

function getBills(): array
{
    return db()->query(
        'SELECT b.*, p.name AS patient_name,
                IFNULL((SELECT SUM(amount_paid) FROM Payments WHERE bill_id = b.bill_id), 0) AS paid
         FROM Bills b
         JOIN Patients p ON b.patient_id = p.patient_id
         ORDER BY b.bill_id DESC'
    )->fetchAll();
}

function getBill(int $billId): ?array
{
    $stmt = db()->prepare('SELECT * FROM Bills WHERE bill_id = ?');
    $stmt->execute([$billId]);
    return $stmt->fetch() ?: null;
}

/**
 * Record a payment against a bill, then update the bill's status based on the
 * running total of payments (Pending / Partial / Paid).
 */
function recordPayment(int $billId, float $amount, string $method): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO Payments(bill_id, amount_paid, payment_date, payment_method)
             VALUES (?, ?, CURDATE(), ?)'
        );
        $stmt->execute([$billId, $amount, $method]);

        $stmt = $pdo->prepare('SELECT amount FROM Bills WHERE bill_id = ?');
        $stmt->execute([$billId]);
        $billAmount = (float) $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT IFNULL(SUM(amount_paid),0) FROM Payments WHERE bill_id = ?');
        $stmt->execute([$billId]);
        $paid = (float) $stmt->fetchColumn();

        $status = $paid <= 0 ? 'Pending' : ($paid >= $billAmount ? 'Paid' : 'Partial');
        $stmt = $pdo->prepare('UPDATE Bills SET payment_status = ? WHERE bill_id = ?');
        $stmt->execute([$status, $billId]);

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/* -------------------------------------------------------------------------
 *  REPORTS (Admin)
 * ---------------------------------------------------------------------- */

function getMonthlyRevenue(): array
{
    return db()->query('SELECT * FROM MonthlyRevenue ORDER BY revenue_month DESC')->fetchAll();
}

/** Simple counts for the admin dashboard. */
function getStats(): array
{
    return [
        'patients'     => (int) db()->query('SELECT COUNT(*) FROM Patients')->fetchColumn(),
        'doctors'      => (int) db()->query('SELECT COUNT(*) FROM Doctors')->fetchColumn(),
        'departments'  => (int) db()->query('SELECT COUNT(*) FROM Departments')->fetchColumn(),
        'appointments' => (int) db()->query('SELECT COUNT(*) FROM Appointments')->fetchColumn(),
        'revenue'      => (float) db()->query('SELECT IFNULL(SUM(amount_paid),0) FROM Payments')->fetchColumn(),
    ];
}

/** HTML-escape shortcut for templates. */
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
