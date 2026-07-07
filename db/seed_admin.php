<?php
require __DIR__ . '/../config/db.php';

$stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ?');
$stmt->execute(['admin']);
if ($stmt->fetch()) {
    echo "Admin already exists\n";
    exit;
}

$roleStmt = $pdo->prepare('SELECT role_id FROM roles WHERE name = ?');
$roleStmt->execute(['admin']);
$roleId = $roleStmt->fetchColumn();

$pdo->prepare('INSERT INTO users (uuid, role_id, username, password_hash, first_name, last_name, email, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)')->execute([
    bin2hex(random_bytes(8)),
    $roleId,
    'admin',
    password_hash('admin123', PASSWORD_DEFAULT),
    'System',
    'Admin',
    'admin@madrasa.local',
    '0000000000'
]);

echo "Default admin created\n";
