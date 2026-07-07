<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

$studentsStmt = $pdo->prepare("SELECT s.student_id, CONCAT(u.first_name, ' ', u.last_name) as fullname, c.name as class_name, s.gender
                               FROM students s
                               JOIN users u ON s.user_id = u.user_id
                               LEFT JOIN classes c ON s.enrolled_class_id = c.class_id
                               WHERE s.deleted_at IS NULL
                               ORDER BY s.student_id DESC");
$studentsStmt->execute();
$students = $studentsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;} body{background:#f8fafc;padding:30px;} .container{max-width:1100px;margin:0 auto;background:#fff;padding:25px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,0.08);} .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;} .actions a{display:inline-block;padding:10px 14px;background:#2563eb;color:white;text-decoration:none;border-radius:8px;margin-left:8px;} table{width:100%;border-collapse:collapse;} th{background:#2563eb;color:white;padding:12px;} td{padding:12px;border-bottom:1px solid #e2e8f0;}
</style>
</head>
<body>
<div class="container">
    <div class="top">
        <div>
            <h2>Teacher Dashboard</h2>
            <p>Welcome <?php echo htmlspecialchars($_SESSION['name'] ?? 'Teacher'); ?> to your madrasa dashboard</p>
        </div>
        <div class="actions">
            <a href="addstudent.php">Add Learner</a>
            <a href="manage_student.php">Manage Learners</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>
    <table>
        <tr><th>ID</th><th>Full Name</th><th>Class Group</th><th>Gender</th></tr>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo $student['student_id']; ?></td>
                <td><?php echo htmlspecialchars($student['fullname']); ?></td>
                <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>