<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch dashboard statistics
$studentsStmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE deleted_at IS NULL");
$total_students = $studentsStmt->fetch()['total'] ?? 0;

$teachersStmt = $pdo->query("SELECT COUNT(*) as total FROM teachers");
$total_teachers = $teachersStmt->fetch()['total'] ?? 0;

$paymentsStmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments");
$total_payments = $paymentsStmt->fetch()['total'] ?? 0;

$classesStmt = $pdo->query("SELECT COUNT(*) as total FROM classes");
$total_classes = $classesStmt->fetch()['total'] ?? 0;

// Recent students with better query
$recentStmt = $pdo->prepare("
    SELECT s.student_id, 
           CONCAT(u.first_name, ' ', u.last_name) as fullname, 
           c.name as class_name,
           s.created_at,
           u.email,
           u.phone
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN classes c ON s.enrolled_class_id = c.class_id
    WHERE s.deleted_at IS NULL
    ORDER BY s.student_id DESC LIMIT 5
");
$recentStmt->execute();
$recentStudents = $recentStmt->fetchAll();

// Fetch upcoming payments or recent activities
$activityStmt = $pdo->prepare("
    SELECT p.payment_id, 
           CONCAT(u.first_name, ' ', u.last_name) as student_name,
           p.amount,
           p.payment_date,
           p.status
    FROM payments p
    JOIN students s ON p.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    ORDER BY p.payment_date DESC LIMIT 5
");
$activityStmt->execute();
$recentActivities = $activityStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Madrasa</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        .sidebar-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-link:hover {
            transform: translateX(4px);
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }
        .stat-icon {
            transition: all 0.3s ease;
        }
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .table-row {
            transition: all 0.2s ease;
        }
        .table-row:hover {
            background: linear-gradient(90deg, #f8fafc, #f1f5f9);
        }
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-slate-50 min-h-screen">

    <!-- Sidebar -->
    <aside class="w-72 bg-gradient-to-b from-slate-900 to-slate-800 text-slate-200 fixed h-full p-6 flex flex-col justify-between shadow-2xl z-20">
        <div>
            <!-- Logo -->
            <div class="flex items-center gap-4 px-2 py-4 mb-8 border-b border-slate-700/50">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <i class="fa-solid fa-mosque text-2xl text-white"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold tracking-tight text-white">Madrasa</span>
                    <p class="text-xs text-slate-400 font-medium tracking-wider">ADMIN PANEL</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="space-y-1.5 mt-6">
                <a href="dashboard.php" class="sidebar-link active flex items-center gap-4 px-4 py-3.5 text-sm font-medium rounded-2xl transition duration-200">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-lg"></i> 
                    <span>Dashboard</span>
                    <span class="ml-auto bg-white/20 text-xs px-2 py-0.5 rounded-full">Live</span>
                </a>
                <a href="dashboard.php?page=add_student" class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-slate-400 hover:text-white rounded-2xl transition duration-200">
                    <i class="fa-solid fa-user-plus w-5 text-center text-lg"></i> 
                    <span>Register Learner</span>
                </a>
                <a href="dashboard.php?page=manage_students" class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-slate-400 hover:text-white rounded-2xl transition duration-200">
                    <i class="fa-solid fa-graduation-cap w-5 text-center text-lg"></i> 
                    <span>Manage Learners</span>
                </a>
                <a href="dashboard.php?page=manage_classes" class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-slate-400 hover:text-white rounded-2xl transition duration-200">
                    <i class="fa-solid fa-layer-group w-5 text-center text-lg"></i> 
                    <span>Manage Class Groups</span>
                </a>
                <a href="dashboard.php?page=payments" class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-slate-400 hover:text-white rounded-2xl transition duration-200">
                    <i class="fa-solid fa-wallet w-5 text-center text-lg"></i> 
                    <span>Payments</span>
                    <span class="ml-auto bg-emerald-500/20 text-emerald-400 text-xs px-2 py-0.5 rounded-full">New</span>
                </a>
                <a href="#" class="sidebar-link flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-slate-400 hover:text-white rounded-2xl transition duration-200">
                    <i class="fa-solid fa-cog w-5 text-center text-lg"></i> 
                    <span>Settings</span>
                </a>
            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="border-t border-slate-700/50 pt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-slate-800/50 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                    <?php echo strtoupper(substr($_SESSION['name'] ?? 'Admin', 0, 1)); ?>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></p>
                    <p class="text-xs text-slate-400">Administrator</p>
                </div>
            </div>
            <a href="../auth/logout.php" class="flex items-center gap-4 px-4 py-3.5 text-sm font-medium text-red-400 hover:bg-red-500/10 rounded-2xl transition duration-200 border border-transparent hover:border-red-500/20">
                <i class="fa-solid fa-right-from-bracket w-5 text-center text-lg"></i> 
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-72 w-full p-8">
        <?php
        // Load page fragment into the main content area while keeping header/sidebar fixed
        $page = $_GET['page'] ?? 'home';
        $allowed = ['home','add_student','manage_students','manage_classes','payments'];
        if (!in_array($page, $allowed)) { $page = 'home'; }
        if ($page !== 'home') {
            $map = [
                'add_student' => 'add_student',
                'manage_students' => 'manage_students',
                'manage_classes' => 'manage_classes',
                'payments' => 'payments'
            ];
            $fragName = $map[$page] ?? 'home';
            $frag = __DIR__ . '/content/' . $fragName . '.php';
            if (file_exists($frag)) {
                include $frag;
                echo "\n</main>\n</body>\n</html>";
                exit;
            }
        }
        ?>
        
        <!-- Top Bar -->
        <header class="flex justify-between items-center bg-white/80 backdrop-blur-md p-5 rounded-3xl shadow-sm border border-white/50 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Welcome back, <span class="gradient-text"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></span> 👋
                </h1>
                <p class="text-sm text-slate-500 mt-1">Here's what's happening with your madrasa today</p>
            </div>
            <div class="flex items-center gap-4">
                <!-- Date Display -->
                <div class="hidden md:flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-2xl">
                    <i class="fa-regular fa-calendar text-slate-600"></i>
                    <span class="text-sm font-medium text-slate-700">
                        <?php echo date('l, F d, Y'); ?>
                    </span>
                </div>
                <!-- Notifications -->
                <div class="relative">
                    <button class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 transition flex items-center justify-center text-slate-600">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold notification-badge">3</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- KPI Cards -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Students -->
            <div class="stat-card bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Students</p>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-2"><?php echo $total_students; ?></h3>
                        <p class="text-xs text-emerald-600 mt-1">
                            <i class="fa-solid fa-arrow-up"></i> 12% this month
                        </p>
                    </div>
                    <div class="stat-icon w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-500/30">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>

            <!-- Teachers -->
            <div class="stat-card bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Teachers</p>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-2"><?php echo $total_teachers; ?></h3>
                        <p class="text-xs text-emerald-600 mt-1">
                            <i class="fa-solid fa-arrow-up"></i> 4% this month
                        </p>
                    </div>
                    <div class="stat-icon w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-emerald-500/30">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            <div class="stat-card bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Revenue</p>
                        <h3 class="text-2xl font-extrabold text-slate-900 mt-2">TZS <?php echo number_format($total_payments); ?></h3>
                        <p class="text-xs text-emerald-600 mt-1">
                            <i class="fa-solid fa-arrow-up"></i> 18.5% this month
                        </p>
                    </div>
                    <div class="stat-icon w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-amber-500/30">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>

            <!-- Classes -->
            <div class="stat-card bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Classes</p>
                        <h3 class="text-3xl font-extrabold text-slate-900 mt-2"><?php echo $total_classes; ?></h3>
                        <p class="text-xs text-slate-400 mt-1">Active classes</p>
                    </div>
                    <div class="stat-icon w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-purple-500/30">
                        <i class="fa-solid fa-school"></i>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts & Quick Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Revenue Overview</h3>
                        <p class="text-xs text-slate-500">Monthly payment statistics</p>
                    </div>
                    <select class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>This Year</option>
                        <option>This Month</option>
                        <option>Last Month</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium">Pending Payments</p>
                                <p class="text-lg font-bold text-slate-900">24</p>
                            </div>
                        </div>
                        <span class="text-xs bg-blue-200 text-blue-700 px-2 py-1 rounded-full font-semibold">+2</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium">Complete Payments</p>
                                <p class="text-lg font-bold text-slate-900">142</p>
                            </div>
                        </div>
                        <span class="text-xs bg-emerald-200 text-emerald-700 px-2 py-1 rounded-full font-semibold">+18</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-amber-50 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium">Active Students</p>
                                <p class="text-lg font-bold text-slate-900"><?php echo $total_students; ?></p>
                            </div>
                        </div>
                        <span class="text-xs bg-amber-200 text-amber-700 px-2 py-1 rounded-full font-semibold">+5</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Students & Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Students Table -->
            <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            <i class="fa-regular fa-user text-blue-600 mr-2"></i>Recent Students
                        </h2>
                        <p class="text-xs text-slate-500">Last 5 enrolled students</p>
                    </div>
                    <a href="../teacher/manage_student.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl transition duration-200">
                        View All <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-500 uppercase text-xs font-bold tracking-wider">
                                <th class="py-3 px-6">ID</th>
                                <th class="py-3 px-6">Student</th>
                                <th class="py-3 px-6">Class</th>
                                <th class="py-3 px-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm">
                            <?php if (empty($recentStudents)): ?>
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">
                                        <i class="fa-regular fa-face-frown text-2xl block mb-2"></i>
                                        No recent students found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentStudents as $row): ?>
                                    <tr class="table-row">
                                        <td class="py-4 px-6 font-semibold text-slate-600">#<?php echo $row['student_id']; ?></td>
                                        <td class="py-4 px-6">
                                            <div>
                                                <p class="font-medium text-slate-900"><?php echo htmlspecialchars($row['fullname']); ?></p>
                                                <p class="text-xs text-slate-400"><?php echo htmlspecialchars($row['email'] ?? 'No email'); ?></p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                <?php echo htmlspecialchars($row['class_name'] ?? 'Not Assigned'); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Active
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent Activities -->
            <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900">
                        <i class="fa-regular fa-clock text-purple-600 mr-2"></i>Recent Activities
                    </h2>
                    <p class="text-xs text-slate-500">Latest transactions and events</p>
                </div>
                
                <div class="p-6 space-y-4 max-h-80 overflow-y-auto scrollbar-hide">
                    <?php if (empty($recentActivities)): ?>
                        <div class="text-center text-slate-400 py-8">
                            <i class="fa-regular fa-clock text-2xl block mb-2"></i>
                            No recent activities
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 transition">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">
                                        <?php echo htmlspecialchars($activity['student_name']); ?>
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Payment of TZS <?php echo number_format($activity['amount']); ?> 
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium 
                                            <?php echo $activity['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'; ?>">
                                            <?php echo ucfirst($activity['status'] ?? 'pending'); ?>
                                        </span>
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400">
                                    <?php echo date('d M', strtotime($activity['payment_date'])); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="mt-8 text-center text-sm text-slate-400 border-t border-slate-200 pt-6">
            <p>&copy; <?php echo date('Y'); ?> Madrasa Management System. All rights reserved.</p>
        </footer>

    </main>

    <script>
        // Revenue Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue (TZS)',
                        data: [120000, 145000, 180000, 165000, 210000, 195000, 230000, 250000, 220000, 280000, 310000, 345000],
                        borderColor: '#3b82f6',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'TZS ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        });
    </script>

</body>
</html>