<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT s.student_id, s.registration_number, CONCAT(u.first_name, ' ', u.last_name) as fullname, s.gender, c.name as class_name, s.status
                       FROM students s
                       JOIN users u ON s.user_id = u.user_id
                       LEFT JOIN classes c ON s.enrolled_class_id = c.class_id
                       WHERE u.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

$paymentStmt = $pdo->prepare("SELECT control_number, amount, status FROM payments WHERE student_id = ? ORDER BY payment_id DESC LIMIT 1");
$paymentStmt->execute([$student['student_id'] ?? 0]);
$payment = $paymentStmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;} body{background:#f8fafc;padding:30px;} .card{max-width:800px;margin:0 auto;background:#fff;padding:25px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,0.08);} .row{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-top:15px;} .item{padding:12px;background:#f8fafc;border-radius:10px;} a{display:inline-block;margin-top:15px;padding:10px 14px;background:#2563eb;color:white;text-decoration:none;border-radius:8px;}
</style>
</head>
<body>
<div class="card">
    <h2>Student Profile</h2>
    <p>Welcome <?php echo htmlspecialchars($_SESSION['name'] ?? 'Student'); ?></p>
    <?php if ($student): ?>
        <div class="row">
            <div class="item"><strong>Full Name:</strong><br><?php echo htmlspecialchars($student['fullname']); ?></div>
            <div class="item"><strong>Registration No:</strong><br><?php echo htmlspecialchars($student['registration_number']); ?></div>
        </div>
        <div class="row">
            <div class="item"><strong>Class Group:</strong><br><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></div>
            <div class="item"><strong>Status:</strong><br><?php echo htmlspecialchars($student['status'] ?? 'active'); ?></div>
        </div>
        <div class="row">
            <div class="item"><strong>Payment Status:</strong><br><?php echo htmlspecialchars($payment['status'] ?? 'No payment record'); ?></div>
            <div class="item"><strong>Payment Control No:</strong><br><?php echo htmlspecialchars($payment['control_number'] ?? 'N/A'); ?></div>
        </div>
    <?php else: ?>
        <p>No student record found for your account.</p>
    <?php endif; ?>
    <a href="../auth/logout.php">Logout</a>
</div>
</body>
</html>
