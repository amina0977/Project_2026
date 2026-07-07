<?php
// Fragment: Manage Students
// Handle deletion via ?delete=ID
if (isset($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    $stmt = $pdo->prepare('UPDATE students SET deleted_at = CURRENT_TIMESTAMP WHERE student_id = ?');
    $stmt->execute([$delId]);
    header('Location: dashboard.php?page=manage_students&deleted=success');
    exit;
}

// Handle restore
if (isset($_GET['restore'])) {
    $restoreId = (int) $_GET['restore'];
    $stmt = $pdo->prepare('UPDATE students SET deleted_at = NULL WHERE student_id = ?');
    $stmt->execute([$restoreId]);
    header('Location: dashboard.php?page=manage_students&restored=success');
    exit;
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classFilter = isset($_GET['class']) ? (int)$_GET['class'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT s.student_id, s.registration_number, s.enrolled_class_id, s.status, s.gender, s.address,
          CONCAT(u.first_name, ' ', u.last_name) AS fullname, u.email, u.phone, u.created_at,
          c.name AS class_name
          FROM students s
          JOIN users u ON s.user_id = u.user_id
          LEFT JOIN classes c ON s.enrolled_class_id = c.class_id
          WHERE s.deleted_at IS NULL";

$params = [];

if ($search) {
    $query .= " AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR s.registration_number LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($classFilter) {
    $query .= " AND s.enrolled_class_id = ?";
    $params[] = $classFilter;
}

if ($statusFilter) {
    $query .= " AND s.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY s.student_id DESC";

$students = $pdo->prepare($query);
$students->execute($params);
$rows = $students->fetchAll();

// Get classes for filter
$classes = $pdo->query('SELECT class_id, name FROM classes ORDER BY name')->fetchAll();
$statuses = $pdo->query('SELECT value, label FROM student_statuses ORDER BY sort_order, label')->fetchAll();

// Get statistics
$statsQuery = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN status = 'graduated' THEN 1 ELSE 0 END) as graduated
    FROM students WHERE deleted_at IS NULL");
$stats = $statsQuery->fetch();

// Get success message
$successMessage = '';
if (isset($_GET['deleted']) && $_GET['deleted'] === 'success') {
    $successMessage = 'Student deleted successfully!';
} elseif (isset($_GET['restored']) && $_GET['restored'] === 'success') {
    $successMessage = 'Student restored successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Learners - Madrasa Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .table-row {
            transition: all 0.2s ease;
        }

        .table-row:hover {
            background: linear-gradient(90deg, #f8fafc, #f1f5f9);
            transform: scale(1.002);
        }

        .delete-btn {
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            transform: scale(1.1);
            background: #fee2e2;
        }

        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        .status-badge {
            transition: all 0.3s ease;
        }

        .status-badge:hover {
            transform: scale(1.05);
        }

        .filter-btn {
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: #f1f5f9;
        }

        .filter-btn.active {
            background: #3b82f6;
            color: white;
        }

        .search-input {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .success-message {
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pagination-btn {
            transition: all 0.3s ease;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #3b82f6;
            color: white;
            transform: translateY(-2px);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .avatar-placeholder {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }

        .empty-state {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar styling */
        .table-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900">
                    <span class="gradient-text">Manage Learners</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    <i class="fa-regular fa-users mr-1"></i>
                    Manage and organize all madrasa learner records
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="dashboard.php?page=add_student" class="px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-2xl font-semibold flex items-center gap-2 hover:shadow-lg hover:shadow-blue-500/30 transition-all hover:scale-105">
                    <i class="fa-solid fa-user-plus"></i>
                    Register Learner
                </a>
                <a href="#" onclick="window.location.reload()" class="p-3 bg-white border border-slate-200 rounded-2xl hover:bg-slate-50 transition">
                    <i class="fa-solid fa-rotate text-slate-600"></i>
                </a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if ($successMessage): ?>
            <div class="success-message mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
                <i class="fa-solid fa-check-circle text-emerald-500 text-xl"></i>
                <span class="text-emerald-700 font-medium"><?php echo $successMessage; ?></span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-400 hover:text-emerald-600 transition">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1"><?php echo $stats['total'] ?? 0; ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Active</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1"><?php echo $stats['active'] ?? 0; ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fa-regular fa-circle-check"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Inactive</p>
                        <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo $stats['inactive'] ?? 0; ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fa-regular fa-circle-pause"></i>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Graduated</p>
                        <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo $stats['graduated'] ?? 0; ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
            <form method="GET" action="" class="flex flex-col md:flex-row gap-3">
                <input type="hidden" name="page" value="manage_students">

                <div class="flex-1 relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" placeholder="Search by name, registration, or email..."
                        class="search-input w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <select name="class" class="px-4 py-2.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition">
                    <option value="">All Class Groups</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['class_id']; ?>" <?php echo ($classFilter == $class['class_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" class="px-4 py-2.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition">
                    <option value="">All Status</option>
                    <?php foreach ($statuses as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption['value']); ?>" <?php echo ($statusFilter === $statusOption['value']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption['label']); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>

                <?php if ($search || $classFilter || $statusFilter): ?>
                    <a href="dashboard.php?page=manage_students" class="px-6 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-medium hover:bg-slate-200 transition flex items-center gap-2">
                        <i class="fa-solid fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="table-container overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Learner</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Registration</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Class Group</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($rows) === 0): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center empty-state">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                                            <i class="fa-solid fa-user-slash text-3xl"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-slate-700 mb-2">No Learners Found</h3>
                                        <p class="text-sm text-slate-400 mb-4">Try adjusting your search or filter criteria</p>
                                        <a href="dashboard.php?page=add_student" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                                            <i class="fa-solid fa-user-plus mr-2"></i>
                                            Add First Learner
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $index => $r): ?>
                                <tr class="table-row">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-slate-400">#<?php echo $r['student_id']; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full avatar-placeholder flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                                                <?php
                                                $nameParts = explode(' ', htmlspecialchars($r['fullname']));
                                                $initials = isset($nameParts[0]) ? $nameParts[0][0] : '';
                                                $initials .= isset($nameParts[1]) ? $nameParts[1][0] : '';
                                                echo strtoupper($initials ?: '?');
                                                ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($r['fullname']); ?></p>
                                                <p class="text-xs text-slate-400">
                                                    <i class="fa-regular fa-<?php echo $r['gender'] === 'male' ? 'mars' : ($r['gender'] === 'female' ? 'venus' : 'circle'); ?> mr-1"></i>
                                                    <?php echo ucfirst($r['gender'] ?? 'Not set'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($r['registration_number']); ?></p>
                                            <p class="text-xs text-slate-400">
                                                <i class="fa-regular fa-calendar mr-1"></i>
                                                <?php echo date('d M Y', strtotime($r['created_at'])); ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <i class="fa-solid fa-school text-[10px]"></i>
                                            <?php echo htmlspecialchars($r['class_name'] ?? 'Not Assigned'); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <?php if ($r['email']): ?>
                                                <p class="text-sm text-slate-600">
                                                    <i class="fa-regular fa-envelope text-xs text-slate-400 mr-1"></i>
                                                    <?php echo htmlspecialchars($r['email']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($r['phone']): ?>
                                                <p class="text-xs text-slate-400 mt-0.5">
                                                    <i class="fa-solid fa-phone text-[10px] mr-1"></i>
                                                    <?php echo htmlspecialchars($r['phone']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="status-badge inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium
                                        <?php
                                        $status = $r['status'] ?? 'active';
                                        if ($status === 'active') {
                                            echo 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                        } elseif ($status === 'inactive') {
                                            echo 'bg-amber-50 text-amber-700 border border-amber-200';
                                        } elseif ($status === 'graduated') {
                                            echo 'bg-purple-50 text-purple-700 border border-purple-200';
                                        } else {
                                            echo 'bg-slate-50 text-slate-700 border border-slate-200';
                                        }
                                        ?>
                                    ">
                                            <span class="w-1.5 h-1.5 rounded-full
                                            <?php
                                            if ($status === 'active') echo 'bg-emerald-500';
                                            elseif ($status === 'inactive') echo 'bg-amber-500';
                                            elseif ($status === 'graduated') echo 'bg-purple-500';
                                            else echo 'bg-slate-400';
                                            ?>
                                        "></span>
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="dashboard.php?page=view_student&id=<?php echo $r['student_id']; ?>"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-xl transition"
                                                title="View Learner">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <a href="dashboard.php?page=edit_student&id=<?php echo $r['student_id']; ?>"
                                                class="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition"
                                                title="Edit Learner">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="dashboard.php?page=manage_students&delete=<?php echo $r['student_id']; ?>"
                                                onclick="return confirmDelete('<?php echo htmlspecialchars($r['fullname']); ?>')"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-xl transition delete-btn"
                                                title="Delete Learner">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer with Results Count -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-500">
                    <i class="fa-regular fa-file-lines mr-1"></i>
                    Showing <span class="font-semibold text-slate-700"><?php echo count($rows); ?></span> learners
                </p>
                <div class="flex items-center gap-2">
                    <button class="pagination-btn px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600" disabled>
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <span class="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">1</span>
                    <button class="pagination-btn px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600" disabled>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-file-export"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Export Data</p>
                    <p class="text-xs text-slate-400">Export learner list to CSV</p>
                </div>
                <a href="#" class="ml-auto text-blue-600 hover:text-blue-700 text-sm font-medium">Export</a>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                    <i class="fa-solid fa-print"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Print Report</p>
                    <p class="text-xs text-slate-400">Print learner directory</p>
                </div>
                <a href="#" class="ml-auto text-blue-600 hover:text-blue-700 text-sm font-medium">Print</a>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-download"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700">Bulk Import</p>
                    <p class="text-xs text-slate-400">Import learners from CSV</p>
                </div>
                <a href="#" class="ml-auto text-blue-600 hover:text-blue-700 text-sm font-medium">Import</a>
            </div>
        </div>
    </div>

    <script>
        // Confirm delete with learner name
        function confirmDelete(studentName) {
            return confirm(`Are you sure you want to delete learner: ${studentName}?\n\nThis action cannot be undone.`);
        }

        // Auto-hide success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    successMessage.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => successMessage.remove(), 500);
                }, 5000);
            }
        });

        // Keyboard shortcut for search (Ctrl+/)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                document.querySelector('input[name="search"]')?.focus();
            }
        });
    </script>

</body>

</html>