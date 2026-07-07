<?php
session_start();
include __DIR__ . '/../config/db.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $checkUser = $pdo->prepare("SELECT users.*, roles.name AS role_name
                                FROM users
                                LEFT JOIN roles ON users.role_id = roles.role_id
                                WHERE username = ?");
    $checkUser->execute([$username]);
    $user = $checkUser->fetch();

    if (!$user) {
        $error = 'User Not Found';
    } else {
        if ($user['deleted_at'] !== null) {
            $error = 'Account has been deleted';
        } elseif ((int) $user['is_active'] !== 1) {
            $error = 'Account is inactive. Please contact admin.';
        } elseif (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['uuid'] = $user['uuid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

            $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?")->execute([$user['user_id']]);

            if ($user['role_name'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } elseif ($user['role_name'] === 'teacher') {
                header('Location: ../teacher/dashboard.php');
            } elseif ($user['role_name'] === 'student') {
                header('Location: ../student/dashboard.php');
            } elseif ($user['role_name'] === 'payment_officer') {
                header('Location: ../paymentoffic/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $error = 'Incorrect Password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Madrasa Login System</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{height:100vh;display:flex;justify-content:center;align-items:center;background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 100%);overflow:hidden;}
.overlay{position:absolute;width:100%;height:100%;background:rgba(0,0,0,0.55);} 
.login-container{position:relative;width:400px;padding:40px;border-radius:20px;background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.2);box-shadow:0 8px 32px rgba(0,0,0,0.3);z-index:1;}
.logo{text-align:center;margin-bottom:20px;}.logo h1{color:#fff;font-size:32px;font-weight:700;}.logo p{color:#ddd;font-size:14px;}
.input-box{margin-top:20px;}.input-box label{color:#fff;display:block;margin-bottom:8px;font-size:14px;}.input-box input{width:100%;padding:14px;border:none;outline:none;border-radius:10px;background:rgba(255,255,255,0.2);color:#fff;font-size:15px;}.input-box input::placeholder{color:#eee;}
.btn{width:100%;margin-top:25px;padding:14px;border:none;border-radius:10px;background:#16a34a;color:#fff;font-size:16px;font-weight:600;cursor:pointer;transition:0.3s;}.btn:hover{background:#15803d;}
.error{background:#dc2626;color:#fff;padding:10px;border-radius:8px;margin-top:15px;text-align:center;}
.footer{text-align:center;color:#ddd;margin-top:20px;font-size:13px;}
</style>
</head>
<body>
<div class="overlay"></div>
<div class="login-container">
    <div class="logo"><h1>MADRASA</h1><p>Registration Management System</p></div>
    <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="input-box"><label>Username</label><input type="text" name="username" placeholder="Enter Username" required></div>
        <div class="input-box"><label>Password</label><input type="password" name="password" placeholder="Enter Password" required></div>
        <button type="submit" name="login" class="btn">LOGIN</button>
    </form>
    <div class="footer">© 2026 Madrasa Registration System</div>
</div>
</body>
</html>