-- ============================================================================
--  Hospital Management System  -  Complete MySQL Database
--  Project 2: MySQL + Database Connectivity
--
--  Contents:
--    * 9 tables (8 core + Users for authentication)
--    * Foreign-key relationships
--    * 3 Views      : DoctorSchedule, PatientHistory, MonthlyRevenue
--    * 3 Procedures : BookAppointment, GenerateBill, GetDoctorAppointments
--    * 2 Triggers   : auto_generate_bill, prevent_appointment_conflict
--    * Sample seed data
--
--  Object creation order matters: tables -> triggers -> procedures -> views,
--  then seed data (seed appointments fire the triggers, seed CALLs use the
--  procedures).
--
--  Import:  mysql -u root -p < database/hms.sql
--      or:  create the schema in phpMyAdmin and import this file.
-- ============================================================================

DROP DATABASE IF EXISTS hms;
CREATE DATABASE hms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hms;

-- ============================================================================
--  TABLES
-- ============================================================================

-- One Department -> Many Doctors
CREATE TABLE Departments (
    department_id   INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    description     VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE Doctors (
    doctor_id        INT AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(100) NOT NULL,
    specialty        VARCHAR(100) NOT NULL,
    phone            VARCHAR(15),
    department_id    INT NOT NULL,
    consultation_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (department_id) REFERENCES Departments(department_id)
) ENGINE=InnoDB;

CREATE TABLE Patients (
    patient_id      INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    gender          ENUM('Male','Female','Other') NOT NULL,
    date_of_birth   DATE,
    contact_number  VARCHAR(15),
    address         VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE Medicines (
    medicine_id     INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    description     VARCHAR(255),
    price           DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- One Patient -> Many Appointments,  One Doctor -> Many Appointments
CREATE TABLE Appointments (
    appointment_id   INT AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT NOT NULL,
    doctor_id        INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    status           ENUM('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
    FOREIGN KEY (patient_id) REFERENCES Patients(patient_id),
    FOREIGN KEY (doctor_id)  REFERENCES Doctors(doctor_id)
) ENGINE=InnoDB;

-- One Appointment -> Many Prescriptions
CREATE TABLE Prescriptions (
    prescription_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id  INT NOT NULL,
    medicine_id     INT NOT NULL,
    dosage          VARCHAR(100),
    instructions    VARCHAR(255),
    FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id),
    FOREIGN KEY (medicine_id)    REFERENCES Medicines(medicine_id)
) ENGINE=InnoDB;

-- One Patient -> Many Bills.  One bill per appointment (auto-created by trigger).
CREATE TABLE Bills (
    bill_id         INT AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT NOT NULL,
    appointment_id  INT UNIQUE,
    amount          DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_status  ENUM('Pending','Partial','Paid') NOT NULL DEFAULT 'Pending',
    bill_date       DATE NOT NULL,
    FOREIGN KEY (patient_id)     REFERENCES Patients(patient_id),
    FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id)
) ENGINE=InnoDB;

-- One Bill -> Many Payments (supports partial payments)
CREATE TABLE Payments (
    payment_id      INT AUTO_INCREMENT PRIMARY KEY,
    bill_id         INT NOT NULL,
    amount_paid     DECIMAL(10,2) NOT NULL,
    payment_date    DATE NOT NULL,
    payment_method  ENUM('Cash','Card','UPI','Insurance') NOT NULL DEFAULT 'Cash',
    FOREIGN KEY (bill_id) REFERENCES Bills(bill_id)
) ENGINE=InnoDB;

-- Authentication / role management for the three user roles.
-- Passwords are stored as SHA2-256 hashes (see seed data + includes/auth.php).
CREATE TABLE Users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    CHAR(64) NOT NULL,
    role        ENUM('admin','receptionist','doctor') NOT NULL,
    doctor_id   INT NULL,
    FOREIGN KEY (doctor_id) REFERENCES Doctors(doctor_id)
) ENGINE=InnoDB;

-- ============================================================================
--  TRIGGERS
-- ============================================================================
DELIMITER $$

-- Prevent appointment conflicts: a doctor cannot have two active appointments
-- at the exact same date & time.
CREATE TRIGGER prevent_appointment_conflict
BEFORE INSERT ON Appointments
FOR EACH ROW
BEGIN
    DECLARE v_conflicts INT;
    SELECT COUNT(*) INTO v_conflicts
    FROM Appointments
    WHERE doctor_id = NEW.doctor_id
      AND appointment_date = NEW.appointment_date
      AND status <> 'Cancelled';

    IF v_conflicts > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Appointment conflict: the doctor is already booked at this time.';
    END IF;
END$$

-- Auto-generate a bill after every appointment, seeded with the doctor's fee.
CREATE TRIGGER auto_generate_bill
AFTER INSERT ON Appointments
FOR EACH ROW
BEGIN
    DECLARE v_fee DECIMAL(10,2);
    SELECT consultation_fee INTO v_fee FROM Doctors WHERE doctor_id = NEW.doctor_id;

    INSERT INTO Bills(patient_id, appointment_id, amount, payment_status, bill_date)
    VALUES (NEW.patient_id, NEW.appointment_id, v_fee, 'Pending', CURDATE());
END$$

DELIMITER ;

-- ============================================================================
--  STORED PROCEDURES
-- ============================================================================
DELIMITER $$

-- Book an appointment. Triggers handle conflict-checking and bill creation.
-- Returns the new appointment_id.
CREATE PROCEDURE BookAppointment(
    IN  p_patient_id       INT,
    IN  p_doctor_id        INT,
    IN  p_appointment_date DATETIME
)
BEGIN
    INSERT INTO Appointments(patient_id, doctor_id, appointment_date, status)
    VALUES (p_patient_id, p_doctor_id, p_appointment_date, 'Scheduled');

    SELECT LAST_INSERT_ID() AS appointment_id;
END$$

-- (Re)generate a bill total for an appointment:
--   amount = doctor consultation fee + sum(prescribed medicine prices)
CREATE PROCEDURE GenerateBill(
    IN p_appointment_id INT
)
BEGIN
    DECLARE v_fee       DECIMAL(10,2) DEFAULT 0;
    DECLARE v_medicines DECIMAL(10,2) DEFAULT 0;

    SELECT d.consultation_fee INTO v_fee
    FROM Appointments a
    JOIN Doctors d ON a.doctor_id = d.doctor_id
    WHERE a.appointment_id = p_appointment_id;

    SELECT IFNULL(SUM(m.price), 0) INTO v_medicines
    FROM Prescriptions pr
    JOIN Medicines m ON pr.medicine_id = m.medicine_id
    WHERE pr.appointment_id = p_appointment_id;

    UPDATE Bills
    SET amount = v_fee + v_medicines
    WHERE appointment_id = p_appointment_id;
END$$

-- Return all appointments for a given doctor.
CREATE PROCEDURE GetDoctorAppointments(
    IN p_doctor_id INT
)
BEGIN
    SELECT  a.appointment_id,
            a.appointment_date,
            a.status,
            p.patient_id,
            p.name           AS patient_name,
            p.gender,
            p.contact_number
    FROM Appointments a
    JOIN Patients p ON a.patient_id = p.patient_id
    WHERE a.doctor_id = p_doctor_id
    ORDER BY a.appointment_date;
END$$

DELIMITER ;

-- ============================================================================
--  VIEWS
-- ============================================================================

-- DoctorSchedule: every doctor's appointments with patient + department context.
CREATE OR REPLACE VIEW DoctorSchedule AS
SELECT  d.doctor_id,
        d.name              AS doctor_name,
        dept.name           AS department,
        a.appointment_id,
        a.appointment_date,
        a.status,
        p.patient_id,
        p.name              AS patient_name,
        p.contact_number
FROM Doctors d
JOIN Departments dept   ON d.department_id = dept.department_id
LEFT JOIN Appointments a ON a.doctor_id = d.doctor_id
LEFT JOIN Patients p     ON a.patient_id = p.patient_id;

-- PatientHistory: appointments joined with their prescriptions per patient.
CREATE OR REPLACE VIEW PatientHistory AS
SELECT  p.patient_id,
        p.name              AS patient_name,
        a.appointment_id,
        a.appointment_date,
        a.status,
        doc.name            AS doctor_name,
        m.name              AS medicine_name,
        pr.dosage,
        pr.instructions
FROM Patients p
JOIN Appointments a       ON a.patient_id = p.patient_id
JOIN Doctors doc          ON a.doctor_id = doc.doctor_id
LEFT JOIN Prescriptions pr ON pr.appointment_id = a.appointment_id
LEFT JOIN Medicines m      ON pr.medicine_id = m.medicine_id;

-- MonthlyRevenue: total collected revenue grouped by month.
CREATE OR REPLACE VIEW MonthlyRevenue AS
SELECT  DATE_FORMAT(pay.payment_date, '%Y-%m') AS revenue_month,
        COUNT(pay.payment_id)                  AS total_payments,
        SUM(pay.amount_paid)                   AS total_revenue
FROM Payments pay
GROUP BY DATE_FORMAT(pay.payment_date, '%Y-%m');

-- ============================================================================
--  SEED DATA
-- ============================================================================

INSERT INTO Departments(name, description) VALUES
    ('Cardiology',  'Heart and cardiovascular system'),
    ('Neurology',   'Brain and nervous system'),
    ('Orthopedics', 'Bones, joints and muscles'),
    ('Pediatrics',  'Medical care of infants and children');

INSERT INTO Doctors(name, specialty, phone, department_id, consultation_fee) VALUES
    ('Dr. Muhammad Hassan',  'Cardiologist',  '03001234501', 1, 3500.00),
    ('Dr. Fatima Khan',      'Cardiologist',  '03002234502', 1, 4000.00),
    ('Dr. Ahmed Ali',        'Neurologist',   '03003234503', 2, 4500.00),
    ('Dr. Ayesha Malik',     'Orthopedic',    '03004234504', 3, 3000.00),
    ('Dr. Hassan Raza',      'Pediatrician',  '03005234505', 4, 2500.00);

INSERT INTO Patients(name, gender, date_of_birth, contact_number, address) VALUES
    ('Muhammad Ali Khan',   'Male',   '1995-06-12', '03001234567', '12 Defence, Lahore'),
    ('Zainab Ahmed',        'Female', '1993-03-15', '03012234567', '44 Clifton, Karachi'),
    ('Imran Hassan',        'Male',   '1988-11-02', '03003234567', '7 F-7, Islamabad'),
    ('Saira Malik',         'Female', '1997-09-21', '03014234567', '90 Gulberg, Lahore');

INSERT INTO Medicines(name, description, price) VALUES
    ('Paracetamol 500mg', 'Fever and pain relief',         150.00),
    ('Amoxicillin 250mg', 'Antibiotic',                    450.00),
    ('Benadryl Syrup',    'Cough suppressant',             600.00),
    ('Atorvastatin 10mg', 'Cholesterol control',           800.00),
    ('Ibuprofen 400mg',   'Anti-inflammatory pain relief',  250.00);

-- Users (the plaintext password that hashes to each stored SHA2-256 value):
--   admin       / admin123
--   reception   / reception123
--   hassan      / doctor123   (-> Dr. Muhammad Hassan, doctor_id 1)
--   hasan_raza  / doctor123   (-> Dr. Hassan Raza, doctor_id 5)
INSERT INTO Users(username, password, role, doctor_id) VALUES
    ('admin',     SHA2('admin123', 256),     'admin',        NULL),
    ('reception', SHA2('reception123', 256), 'receptionist', NULL),
    ('hassan',    SHA2('doctor123', 256),    'doctor',       1),
    ('hasan_raza', SHA2('doctor123', 256),   'doctor',       5);

-- Appointments — each INSERT fires prevent_appointment_conflict (BEFORE)
-- and auto_generate_bill (AFTER), so Bills rows are created automatically.
INSERT INTO Appointments(patient_id, doctor_id, appointment_date, status) VALUES
    (1, 1, '2026-06-20 10:00:00', 'Completed'),
    (2, 5, '2026-06-21 11:00:00', 'Completed'),
    (3, 3, '2026-06-22 09:30:00', 'Scheduled'),
    (4, 4, '2026-06-23 15:00:00', 'Scheduled');

INSERT INTO Prescriptions(appointment_id, medicine_id, dosage, instructions) VALUES
    (1, 4, '1 tablet daily', 'After breakfast'),
    (1, 1, '1 tablet',       'Only if fever above 100F'),
    (2, 3, '2 tsp at night', 'For 5 days');

-- Recalculate the two completed appointments' bills (fee + medicines).
CALL GenerateBill(1);
CALL GenerateBill(2);

-- Record a full payment for appointment 1's bill and mark it Paid.
INSERT INTO Payments(bill_id, amount_paid, payment_date, payment_method)
SELECT bill_id, amount, CURDATE(), 'Card' FROM Bills WHERE appointment_id = 1;
UPDATE Bills SET payment_status = 'Paid' WHERE appointment_id = 1;

-- ============================================================================
--  END
-- ============================================================================
