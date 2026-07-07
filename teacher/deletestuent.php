<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare('UPDATE students SET deleted_at = CURRENT_TIMESTAMP WHERE student_id = ?');
    $stmt->execute([$id]);
}

header('Location: manage_student.php');
exit();
