<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'payment_officer') {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_POST['confirm_payment'])) {
    $paymentId = (int) $_POST['payment_id'];
    $pdo->prepare('UPDATE payments SET status = ?, payment_officer_id = ? WHERE payment_id = ?')->execute(['paid', $_SESSION['user_id'], $paymentId]);
}

$paymentsStmt = $pdo->query("SELECT p.payment_id, p.control_number, p.amount, p.status, CONCAT(u.first_name, ' ', u.last_name) as student_name
                            FROM payments p
                            JOIN students s ON p.student_id = s.student_id
                            JOIN users u ON s.user_id = u.user_id
                            ORDER BY p.payment_id DESC");
$payments = $paymentsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Payment Officer Dashboard</title><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"><style>*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;} body{background:#f8fafc;padding:30px;} .box{max-width:1000px;margin:0 auto;background:#fff;padding:25px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,0.08);} table{width:100%;border-collapse:collapse;} th{background:#2563eb;color:white;padding:12px;} td{padding:12px;border-bottom:1px solid #e2e8f0;} button{padding:8px 12px;border:none;background:#16a34a;color:white;border-radius:8px;cursor:pointer;} a{display:inline-block;margin-bottom:15px;color:#2563eb;text-decoration:none;}</style></head>
<body>
<div class="box">
    <h2>Payment Officer Dashboard</h2>
    <p>Manage student payments and confirm completed transactions.</p>
    <table>
        <tr><th>Control No.</th><th>Student</th><th>Amount</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?php echo htmlspecialchars($payment['control_number']); ?></td>
                <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                <td>TZS <?php echo number_format($payment['amount']); ?></td>
                <td><?php echo htmlspecialchars($payment['status']); ?></td>
                <td>
                    <?php if ($payment['status'] !== 'paid'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                            <button type="submit" name="confirm_payment">Confirm</button>
                        </form>
                    <?php else: ?>
                        Confirmed
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>