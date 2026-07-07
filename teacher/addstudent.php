<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header('Location: ../auth/login.php');
    exit();
}

$message = '';

if (isset($_POST['register_student'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $password = $_POST['password'];

    if ($username === '' || $first_name === '' || $password === '') {
        $message = 'Please fill in the required fields.';
    } else {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetchColumn() > 0) {
            $message = 'Username already exists.';
        } else {
            $roleStmt = $pdo->prepare('SELECT role_id FROM roles WHERE name = ?');
            $roleStmt->execute(['student']);
            $role = $roleStmt->fetch();
            $role_id = $role['role_id'] ?? 0;

            $userStmt = $pdo->prepare('INSERT INTO users (role_id, username, password_hash, first_name, last_name, email, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
            $userStmt->execute([$role_id, $username, password_hash($password, PASSWORD_DEFAULT), $first_name, trim($_POST['last_name']), trim($_POST['email']), trim($_POST['phone'])]);
            $user_id = (int) $pdo->lastInsertId();

            $regNo = 'STU-' . date('Ymd') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
            $studentStmt = $pdo->prepare('INSERT INTO students (user_id, registration_number, enrolled_class_id, gender, address) VALUES (?, ?, ?, ?, ?)');
            $studentStmt->execute([$user_id, $regNo, (int) ($_POST['class_id'] ?? 0) ?: null, $_POST['gender'] ?? null, $_POST['address'] ?? null]);
            $message = 'Learner added successfully.';
        }
    }
}

$classes = $pdo->query('SELECT class_id, name FROM classes ORDER BY name')->fetchAll();
$genders = $pdo->query('SELECT value, label FROM gender_options ORDER BY sort_order, label')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Add Learner</title><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"><style>*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;} body{background:#f8fafc;padding:30px;} .box{max-width:700px;margin:0 auto;background:#fff;padding:25px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,0.08);} .message{padding:12px;border-radius:10px;margin:15px 0;background:#dcfce7;color:#166534;} .row{display:grid;grid-template-columns:1fr 1fr;gap:15px;} label{display:block;margin:10px 0 6px;font-weight:600;} input, select, textarea{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:10px;} button{margin-top:15px;padding:12px 16px;border:none;background:#2563eb;color:white;border-radius:10px;cursor:pointer;} a{display:inline-block;margin-top:15px;color:#2563eb;text-decoration:none;}</style></head>
<body>
<div class="box">
    <h2>Add Learner</h2>
    <?php if ($message !== ''): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <form method="POST">
        <div class="row">
            <div><label>First Name</label><input type="text" name="first_name" required></div>
            <div><label>Last Name</label><input type="text" name="last_name"></div>
        </div>
        <div class="row">
            <div><label>Username</label><input type="text" name="username" required></div>
            <div><label>Password</label><input type="password" name="password" required></div>
        </div>
        <div class="row">
            <div><label>Email</label><input type="email" name="email"></div>
            <div><label>Phone</label><input type="text" name="phone"></div>
        </div>
        <div class="row">
            <div>
                <label>Gender</label>
                <select name="gender">
                    <option value="">Select Gender</option>
                    <?php foreach ($genders as $genderOption): ?>
                        <option value="<?php echo htmlspecialchars($genderOption['value']); ?>" <?php echo (($_POST['gender'] ?? '') === $genderOption['value']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($genderOption['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>Class Group</label><select name="class_id"><option value="">Select Class Group</option><?php foreach ($classes as $class): ?><option value="<?php echo $class['class_id']; ?>" <?php echo (($_POST['class_id'] ?? '') == $class['class_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['name']); ?></option><?php endforeach; ?></select></div>
        </div>
        <label>Address</label><textarea name="address" rows="3"></textarea>
        <button type="submit" name="register_student">Save Learner</button>
    </form>
    <a href="dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>