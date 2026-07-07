<?php
// Fragment: Add Student (to be included inside admin dashboard main area)
// Processing POST if form submitted
$message = '';
$messageType = '';
if (isset($_POST['register_student'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $class_id = (int) ($_POST['class_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $guardian_name = trim($_POST['guardian_name'] ?? '');
    $guardian_phone = trim($_POST['guardian_phone'] ?? '');

    if ($username === '' || $first_name === '' || $password === '') {
        $message = 'Please fill in the required fields.';
        $messageType = 'error';
    } else {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetchColumn() > 0) {
            $message = 'Username already exists. Please choose a different username.';
            $messageType = 'error';
        } else {
            $emailCheck = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND email != ""');
            $emailCheck->execute([$email]);
            if ($email && $emailCheck->fetchColumn() > 0) {
                $message = 'Email already registered.';
                $messageType = 'error';
            } else {
                try {
                    $pdo->beginTransaction();
                    $roleStmt = $pdo->prepare('SELECT role_id FROM roles WHERE name = ?');
                    $roleStmt->execute(['student']);
                    $role = $roleStmt->fetch();
                    $role_id = $role['role_id'] ?? 0;

                    $userStmt = $pdo->prepare('INSERT INTO users (role_id, username, password_hash, first_name, last_name, email, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
                    $userStmt->execute([$role_id, $username, password_hash($password, PASSWORD_DEFAULT), $first_name, $last_name, $email, $phone]);
                    $user_id = (int) $pdo->lastInsertId();

                    $regNo = 'STU-' . date('Ymd') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
                    $studentStmt = $pdo->prepare('INSERT INTO students (user_id, registration_number, enrolled_class_id, gender, address, date_of_birth, guardian_name, guardian_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    $studentStmt->execute([$user_id, $regNo, $class_id ?: null, $gender, $address, $date_of_birth, $guardian_name, $guardian_phone]);

                    $pdo->commit();
                    $message = 'Student registered successfully! Registration Number: ' . $regNo;
                    $messageType = 'success';
                    $_POST = array();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = 'Error registering student: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }
}

$classes = $pdo->query('SELECT class_id, name FROM classes ORDER BY name')->fetchAll();
$genders = $pdo->query('SELECT value, label FROM gender_options ORDER BY sort_order, label')->fetchAll();
?>

<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="dashboard.php" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-blue-600 transition group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition"></i>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-extrabold text-slate-900 mt-3"><span class="gradient-text">Register New Learner</span></h1>
            <p class="text-sm text-slate-500 mt-1">Create a new madrasa learner account and enrollment record</p>
        </div>
        <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-2xl shadow-sm border border-slate-100">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold"><?php echo strtoupper(substr($_SESSION['name'] ?? 'Admin', 0, 1)); ?></div>
            <span class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span>
        </div>
    </div>

    <?php if ($message !== ''): ?>
        <div class="mx-6 mt-6 p-4 rounded-2xl <?php echo $messageType === 'success' ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200'; ?>">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <?php if ($messageType === 'success'): ?>
                        <i class="fa-solid fa-check-circle text-emerald-500 text-xl"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-exclamation-circle text-red-500 text-xl"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="<?php echo $messageType === 'success' ? 'text-emerald-700' : 'text-red-700'; ?> font-medium"><?php echo htmlspecialchars($message); ?></p>
                    <?php if ($messageType === 'success' && strpos($message, 'Registration Number') !== false): ?>
                        <p class="text-sm text-emerald-600 mt-1"><i class="fa-regular fa-id-card mr-1"></i> Please note the registration number for future reference.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" class="p-6 bg-white rounded-3xl shadow-lg border border-slate-100">
        <div class="form-section p-6 rounded-2xl bg-slate-50/50 mb-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fa-regular fa-user text-blue-600"></i> Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-input w-full rounded-xl px-4 py-3 bg-white" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input w-full rounded-xl px-4 py-3 bg-white" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-input w-full rounded-xl px-4 py-3 bg-white" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-input w-full rounded-xl px-4 py-3 bg-white appearance-none">
                        <option value="">Select Gender</option>
                        <?php foreach ($genders as $genderOption): ?>
                            <option value="<?php echo htmlspecialchars($genderOption['value']); ?>" <?php echo (($_POST['gender'] ?? '') === $genderOption['value']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($genderOption['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section p-6 rounded-2xl bg-slate-50/50 mb-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fa-regular fa-address-card text-blue-600"></i> Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input w-full rounded-xl px-4 py-3 bg-white" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input w-full rounded-xl px-4 py-3 bg-white" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-input w-full rounded-xl px-4 py-3 bg-white"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-section p-6 rounded-2xl bg-slate-50/50 mb-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fa-regular fa-user-tie text-blue-600"></i> Guardian Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Guardian Name</label>
                    <input type="text" name="guardian_name" class="form-input w-full rounded-xl px-4 py-3 bg-white" value="<?php echo htmlspecialchars($_POST['guardian_name'] ?? ''); ?>">
                </div>
                <div>
                    <label class="form-label">Guardian Phone</label>
                    <input type="tel" name="guardian_phone" class="form-input w-full rounded-xl px-4 py-3 bg-white" value="<?php echo htmlspecialchars($_POST['guardian_phone'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-section p-6 rounded-2xl bg-slate-50/50 mb-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-graduation-cap text-blue-600"></i> Account & Academic Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Username <span class="required">*</span></label>
                    <input type="text" name="username" class="form-input w-full rounded-xl px-4 py-3 bg-white" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <p class="text-xs text-slate-400 mt-1">Must be unique and at least 3 characters</p>
                </div>
                <div>
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input type="password" name="password" class="form-input w-full rounded-xl px-4 py-3 bg-white" required>
                    <p class="text-xs text-slate-400 mt-1">Minimum 6 characters</p>
                </div>
                <div>
                    <label class="form-label">Class Group Enrollment</label>
                    <select name="class_id" class="form-input w-full rounded-xl px-4 py-3 bg-white appearance-none">
                        <option value="">Select Class Group</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" <?php echo (($_POST['class_id'] ?? '') == $class['class_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <div class="w-full bg-blue-50 rounded-xl p-4 border border-blue-100"><p class="text-xs text-blue-700"><i class="fa-solid fa-info-circle mr-1"></i> Learner will be automatically assigned a registration number upon creation.</p></div>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4 mt-8 pt-6 border-t border-slate-200">
            <button type="submit" name="register_student" class="btn-primary flex-1 px-6 py-4 rounded-2xl text-white font-semibold flex items-center justify-center gap-3"><i class="fa-solid fa-user-plus"></i> Register Learner</button>
            <a href="dashboard.php" class="flex-1 px-6 py-4 rounded-2xl text-slate-600 font-medium bg-slate-100 hover:bg-slate-200 transition text-center flex items-center justify-center gap-2"><i class="fa-solid fa-times"></i> Cancel</a>
        </div>
    </form>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="fa-solid fa-shield-halved"></i></div><div><p class="text-xs text-slate-500">Secure Registration</p><p class="text-sm font-medium text-slate-700">Password protected</p></div></div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600"><i class="fa-solid fa-clock"></i></div><div><p class="text-xs text-slate-500">Quick Process</p><p class="text-sm font-medium text-slate-700">2 minutes average</p></div></div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600"><i class="fa-solid fa-database"></i></div><div><p class="text-xs text-slate-500">Data Storage</p><p class="text-sm font-medium text-slate-700">Secure & encrypted</p></div></div>
    </div>

</div>

<script>
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && this.value.trim() === '') {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('error');
            }
        });
    });

    document.querySelector('input[name="password"]')?.addEventListener('input', function() {});

    document.querySelector('input[name="first_name"]')?.addEventListener('input', function() {
        const usernameField = document.querySelector('input[name="username"]');
        if (usernameField && !usernameField.value) {
            const firstName = this.value.trim().toLowerCase();
            const lastName = document.querySelector('input[name="last_name"]')?.value.trim().toLowerCase() || '';
            if (firstName) {
                usernameField.value = firstName + (lastName ? '.' + lastName : '');
            }
        }
    });

    document.querySelector('form')?.addEventListener('submit', function(e) {
        const button = this.querySelector('button[type="submit"]');
        if (button) {
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            button.disabled = true;
        }
    });
</script>
