<?php

include '../config/db.php';

$message = "";

// ensure $conn is available (supporting db.php that exposes $pdo instead)
if (!isset($conn)) {
    if (isset($pdo)) {
        $conn = $pdo;
    } else {
        die('Database connection not found.');
    }
}

if(isset($_POST['register']))
{
    $role_id = $_POST['role_id'];
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // check if username exists using PDO prepared statement
    $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $check->execute([':username' => $username]);
    $count = $check->fetchColumn();

    if($count > 0)
    {
        $message = "Username already exists";
    }
    else
    {
        // check if email already exists
        $checkEmail = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $checkEmail->execute([':email' => $email]);
        $emailCount = $checkEmail->fetchColumn();

        if($emailCount > 0)
        {
            $message = "Email already registered";
        }
        else
        {
        // insert new user with prepared statement
        $sql = $conn->prepare("INSERT INTO users (role_id, username, password_hash, first_name, last_name, email, phone) VALUES (:role_id, :username, :password_hash, :first_name, :last_name, :email, :phone)");
        $success = $sql->execute([
            ':role_id' => $role_id,
            ':username' => $username,
            ':password_hash' => $password,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':phone' => $phone
        ]);

        if($success)
        {
            $message = "Registration Successful";
        }
        else
        {
            $message = "Registration Failed";
        }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Madrasa Register</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{

    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;

    background:
    linear-gradient(
    rgba(0,0,0,0.60),
    rgba(0,0,0,0.60)
    ),

    url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=2070&auto=format&fit=crop');

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
}

/* REGISTER BOX */

.register-box{

    width:100%;
    max-width:520px;

    padding:40px;

    border-radius:25px;

    background:rgba(255,255,255,0.08);

    backdrop-filter:blur(18px);

    border:1px solid rgba(255,255,255,0.15);

    box-shadow:
    0 10px 40px rgba(0,0,0,0.45);

    animation:fadeIn 1s ease;
}

/* ANIMATION */

@keyframes fadeIn{

    from{
        opacity:0;
        transform:translateY(20px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }

}

/* TITLE */

.title{
    text-align:center;
    margin-bottom:30px;
}

.title h1{
    color:#fff;
    font-size:38px;
    font-weight:700;
    letter-spacing:1px;
}

.title p{
    color:#ddd;
    margin-top:8px;
    font-size:14px;
}

/* MESSAGE */

.message{
    background:#16a34a;
    color:#fff;
    padding:12px;
    border-radius:12px;
    text-align:center;
    margin-bottom:20px;
}

/* INPUTS */

.row{
    display:flex;
    gap:15px;
}

.input-box{
    width:100%;
    margin-bottom:18px;
}

.input-box label{
    display:block;
    color:#fff;
    margin-bottom:8px;
    font-size:14px;
    font-weight:500;
}

.input-box input,
.input-box select{

    width:100%;
    padding:15px;

    border:none;
    outline:none;

    border-radius:14px;

    background:rgba(255,255,255,0.12);

    color:#fff;
    font-size:14px;

    transition:0.3s;
}

.input-box input::placeholder{
    color:#ddd;
}

.input-box select option{
    color:#000;
}

.input-box input:focus,
.input-box select:focus{

    background:rgba(255,255,255,0.18);

    border:1px solid #22c55e;

    transform:scale(1.02);
}

/* BUTTON */

.btn{

    width:100%;
    padding:15px;

    border:none;
    border-radius:14px;

    background:linear-gradient(
    135deg,
    #22c55e,
    #15803d
    );

    color:#fff;

    font-size:16px;
    font-weight:600;

    cursor:pointer;

    transition:0.3s;

    margin-top:10px;
}

.btn:hover{

    transform:translateY(-3px);

    box-shadow:
    0 10px 25px rgba(34,197,94,0.4);
}

/* FOOTER */

.footer{
    text-align:center;
    margin-top:18px;
    color:#ddd;
}

.footer a{
    color:#fff;
    text-decoration:none;
    font-weight:600;
}

.footer a:hover{
    color:#22c55e;
}

/* MOBILE */

@media(max-width:600px){

    .row{
        flex-direction:column;
    }

    .title h1{
        font-size:30px;
    }

    .register-box{
        padding:25px;
    }

}

</style>

</head>

<body>

<div class="register-box">

    <div class="title">

        <h1>MADRASA</h1>

        <p>Create New Account</p>

    </div>

    <?php if($message != "") { ?>

        <div class="message">
            <?php echo $message; ?>
        </div>

    <?php } ?>

    <form method="POST">

        <div class="row">

            <div class="input-box">

                <label>First Name</label>

                <input type="text"
                       name="first_name"
                       placeholder="Enter First Name"
                       required>

            </div>

            <div class="input-box">

                <label>Last Name</label>

                <input type="text"
                       name="last_name"
                       placeholder="Enter Last Name">

            </div>

        </div>

        <div class="input-box">

            <label>Username</label>

            <input type="text"
                   name="username"
                   placeholder="Enter Username"
                   required>

        </div>

        <div class="row">

            <div class="input-box">

                <label>Email</label>

                <input type="email"
                       name="email"
                       placeholder="Enter Email">

            </div>

            <div class="input-box">

                <label>Phone</label>

                <input type="text"
                       name="phone"
                       placeholder="Phone Number">

            </div>

        </div>

        <div class="row">

            <div class="input-box">

                <label>Select Role</label>

                <select name="role_id" required>

                    <option value="">Choose Role</option>

                    <option value="1">Admin</option>

                    <option value="2">Teacher</option>

                    <option value="3">Student</option>

                    <option value="4">Payment Officer</option>

                </select>

            </div>

            <div class="input-box">

                <label>Password</label>

                <input type="password"
                       name="password"
                       placeholder="Enter Password"
                       required>

            </div>

        </div>

        <button type="submit"
                name="register"
                class="btn">

                CREATE ACCOUNT

        </button>

    </form>

    <div class="footer">

        Already have account?

        <a href="login.php">Login</a>

    </div>

</div>

</body>
</html>