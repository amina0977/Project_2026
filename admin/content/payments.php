<?php
// Fragment: Payments list for admin
$payments = $pdo->query("SELECT p.payment_id, p.control_number, p.amount, p.status, p.payment_date, CONCAT(u.first_name,' ',u.last_name) AS student_name
    FROM payments p
    JOIN students s ON p.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    ORDER BY p.payment_id DESC");
$rows = $payments->fetchAll();
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Payments</h2>
        <a href="../paymentoffic/dashboard.php" class="px-4 py-2 bg-emerald-600 text-white rounded-xl">Open Payment Officer</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 text-slate-600 text-sm">
                    <th class="p-4">Control No.</th>
                    <th class="p-4">Student</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="5" class="p-6 text-center text-slate-400">No payments found</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr class="border-t">
                            <td class="p-4"><?php echo htmlspecialchars($r['control_number']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($r['student_name']); ?></td>
                            <td class="p-4">TZS <?php echo number_format($r['amount']); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars(date('d M Y', strtotime($r['payment_date'] ?? 'now'))); ?></td>
                            <td class="p-4"><?php echo htmlspecialchars($r['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
