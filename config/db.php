<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'madrasa_registration';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: 'azmina0977';
$dbFile = __DIR__ . '/../db/madrasa.sqlite';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('SET NAMES utf8mb4');
    $driver = 'mysql';
} catch (PDOException $e) {
    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $driver = 'sqlite';
}

if ($driver === 'mysql') {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS roles (
            role_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            uuid CHAR(36) NOT NULL UNIQUE,
            role_id INT NOT NULL,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100),
            email VARCHAR(150) UNIQUE,
            phone VARCHAR(30),
            is_active TINYINT(1) DEFAULT 1,
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            FOREIGN KEY (role_id) REFERENCES roles(role_id)
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS classes (
            class_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(30) UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gender_options (
            gender_id INT AUTO_INCREMENT PRIMARY KEY,
            value VARCHAR(30) NOT NULL UNIQUE,
            label VARCHAR(50) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS student_statuses (
            status_id INT AUTO_INCREMENT PRIMARY KEY,
            value VARCHAR(30) NOT NULL UNIQUE,
            label VARCHAR(50) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            student_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            registration_number VARCHAR(50) NOT NULL UNIQUE,
            gender VARCHAR(20),
            address VARCHAR(255),
            enrolled_class_id INT,
            status VARCHAR(20) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (enrolled_class_id) REFERENCES classes(class_id)
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teachers (
            teacher_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            specialization VARCHAR(150),
            assigned_class_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (assigned_class_id) REFERENCES classes(class_id)
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            control_number VARCHAR(100) NOT NULL UNIQUE,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(10) DEFAULT 'USD',
            payment_date DATETIME,
            status VARCHAR(20) DEFAULT 'pending',
            payment_officer_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(student_id),
            FOREIGN KEY (payment_officer_id) REFERENCES users(user_id)
        ) ENGINE=InnoDB
    ");
} else {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS roles (
            role_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            role_id INTEGER NOT NULL,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT,
            email TEXT,
            phone TEXT,
            is_active INTEGER DEFAULT 1,
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME,
            FOREIGN KEY (role_id) REFERENCES roles(role_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS classes (
            class_id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            code TEXT UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gender_options (
            gender_id INTEGER PRIMARY KEY AUTOINCREMENT,
            value TEXT NOT NULL UNIQUE,
            label TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS student_statuses (
            status_id INTEGER PRIMARY KEY AUTOINCREMENT,
            value TEXT NOT NULL UNIQUE,
            label TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            student_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            registration_number TEXT NOT NULL UNIQUE,
            gender TEXT,
            address TEXT,
            enrolled_class_id INTEGER,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            deleted_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (enrolled_class_id) REFERENCES classes(class_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teachers (
            teacher_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            specialization TEXT,
            assigned_class_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id),
            FOREIGN KEY (assigned_class_id) REFERENCES classes(class_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            payment_id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            control_number TEXT NOT NULL UNIQUE,
            amount REAL NOT NULL,
            currency TEXT DEFAULT 'USD',
            payment_date DATETIME,
            status TEXT DEFAULT 'pending',
            payment_officer_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(student_id),
            FOREIGN KEY (payment_officer_id) REFERENCES users(user_id)
        )
    ");
}

$insertIgnore = $driver === 'sqlite' ? 'INSERT OR IGNORE' : 'INSERT IGNORE';

$pdo->exec("$insertIgnore INTO roles (name, description) VALUES ('admin', 'System Administrator')");
$pdo->exec("$insertIgnore INTO roles (name, description) VALUES ('teacher', 'Teacher')");
$pdo->exec("$insertIgnore INTO roles (name, description) VALUES ('student', 'Student')");
$pdo->exec("$insertIgnore INTO roles (name, description) VALUES ('payment_officer', 'Payment Officer')");

$pdo->exec("$insertIgnore INTO classes (name, code) VALUES ('Hifdh', 'HIF')");
$pdo->exec("$insertIgnore INTO classes (name, code) VALUES ('Tajweed', 'TAJ')");
$pdo->exec("$insertIgnore INTO classes (name, code) VALUES ('Qur\'an Class', 'QUR')");
$pdo->exec("$insertIgnore INTO classes (name, code) VALUES ('Fiqh', 'FIQ')");
$pdo->exec("$insertIgnore INTO classes (name, code) VALUES ('Aqeedah', 'AQD')");
$pdo->exec("$insertIgnore INTO classes (name, code) VALUES ('Hadith', 'HAD')");

$pdo->exec("$insertIgnore INTO gender_options (value, label, sort_order) VALUES ('male', 'Male', 10)");
$pdo->exec("$insertIgnore INTO gender_options (value, label, sort_order) VALUES ('female', 'Female', 20)");
$pdo->exec("$insertIgnore INTO gender_options (value, label, sort_order) VALUES ('other', 'Other', 30)");

$pdo->exec("$insertIgnore INTO student_statuses (value, label, sort_order) VALUES ('active', 'Active', 10)");
$pdo->exec("$insertIgnore INTO student_statuses (value, label, sort_order) VALUES ('inactive', 'Inactive', 20)");
$pdo->exec("$insertIgnore INTO student_statuses (value, label, sort_order) VALUES ('graduated', 'Graduated', 30)");
?>