CREATE DATABASE IF NOT EXISTS madrasa_registration;
USE madrasa_registration;

CREATE TABLE roles (-- ...existing code...
CREATE DATABASE IF NOT EXISTS madrasa_registration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE madrasa_registration;

-- Roles / Permissions
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_permission_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE,
    UNIQUE (role_id, permission_id)
) ENGINE=InnoDB;

-- Users (with UUID for public reference, soft delete, verification & auth metadata)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    email_verified_at DATETIME,
    phone VARCHAR(30),
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

-- Classes / Subjects
CREATE TABLE IF NOT EXISTS classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(30) UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(30) UNIQUE,
    description TEXT,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Students & Teachers (link to users, soft delete, UUID)
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id INT NOT NULL UNIQUE,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    birth_date DATE,
    gender ENUM('male','female','other'),
    address VARCHAR(255),
    enrolled_class_id INT,
    status ENUM('active','inactive','graduated','withdrawn') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (enrolled_class_id) REFERENCES classes(class_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id INT NOT NULL UNIQUE,
    hire_date DATE,
    specialization VARCHAR(150),
    assigned_class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_class_id) REFERENCES classes(class_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Student <> Subject enrollment
CREATE TABLE IF NOT EXISTS student_subjects (
    student_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    enrollment_date DATE DEFAULT (CURRENT_DATE),
    grade VARCHAR(10),
    status ENUM('enrolled','completed','dropped') DEFAULT 'enrolled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    UNIQUE (student_id, subject_id)
) ENGINE=InnoDB;

-- Payments: methods, transactions, receipts
CREATE TABLE IF NOT EXISTS payment_methods (
    payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    student_id INT NOT NULL,
    control_number VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_date DATETIME,
    status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
    payment_method_id INT,
    payment_officer_id INT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (payment_officer_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(payment_method_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_control ON payments(control_number);

CREATE TABLE IF NOT EXISTS payment_transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    transaction_ref VARCHAR(150),
    provider_response JSON,
    status ENUM('initiated','success','failed') DEFAULT 'initiated',
    amount DECIMAL(10,2),
    processed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Audit / Logs / Sessions
CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    row_id VARCHAR(255),
    changes JSON,
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sessions (
    session_id CHAR(128) PRIMARY KEY,
    user_id INT,
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    data MEDIUMTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Application settings / key-value
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Useful indexes for lookups
CREATE INDEX IF NOT EXISTS idx_students_reg ON students(registration_number);
CREATE INDEX IF NOT EXISTS idx_students_uuid ON students(uuid);
CREATE INDEX IF NOT EXISTS idx_teachers_uuid ON teachers(uuid);

-- Seed: roles & sample permissions
INSERT IGNORE INTO roles (name, description) VALUES
('admin', 'System administrator'),
('teacher', 'Teacher / instructor'),
('student', 'Learner'),
('payment_officer', 'Payment officer');

INSERT IGNORE INTO permissions (name, description) VALUES
('users.create', 'Create users'),
('users.update', 'Update users'),
('users.delete', 'Delete users'),
('payments.manage', 'Manage payments'),
('students.enroll', 'Enroll students');

-- Example role -> permission mapping (idempotent)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM roles r
JOIN permissions p ON p.name IN ('users.create','users.update','users.delete','payments.manage')
WHERE r.name = 'admin';

-- Trigger: auto-generate simple control_number for payments if not provided
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_payments_control BEFORE INSERT ON payments
FOR EACH ROW
BEGIN
    IF NEW.control_number IS NULL OR NEW.control_number = '' THEN
        SET NEW.control_number = CONCAT('PAY-', DATE_FORMAT(UTC_TIMESTAMP(), '%Y%m%d%H%i%s'), '-', LPAD(FLOOR(RAND()*9999),4,'0'));
    END IF;
    IF NEW.uuid IS NULL OR NEW.uuid = '' THEN
        SET NEW.uuid = UUID();
    END IF;
END$$
DELIMITER ;

-- Trigger: set user.uuid if missing
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_users_uuid BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.uuid IS NULL OR NEW.uuid = '' THEN
        SET NEW.uuid = UUID();
    END IF;
END$$
DELIMITER ;

-- Ensure STRICT mode compatible defaults (optional)
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
);

INSERT INTO roles (name, description) VALUES
('admin', 'System administrator'),
('teacher', 'Teacher / instructor'),
('student', 'Learner'),
('payment_officer', 'Payment officer');

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    class_id INT,
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
);

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    birth_date DATE,
    gender ENUM('male','female','other'),
    address VARCHAR(255),
    enrolled_class_id INT,
    status ENUM('active','inactive','graduated','withdrawn') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (enrolled_class_id) REFERENCES classes(class_id)
);

CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    hire_date DATE,
    specialization VARCHAR(150),
    assigned_class_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (assigned_class_id) REFERENCES classes(class_id)
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    control_number VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
    payment_officer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (payment_officer_id) REFERENCES users(user_id)
);

CREATE TABLE student_subjects (
    student_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    UNIQUE (student_id, subject_id)
);