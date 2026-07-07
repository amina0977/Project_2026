<?php
// Fragment: Manage Classes / Madrasa Levels
$message = '';
$messageType = '';
$editClass = null;

if (isset($_POST['delete_class'])) {
    $deleteId = (int) ($_POST['class_id'] ?? 0);
    if ($deleteId > 0) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE students SET enrolled_class_id = NULL WHERE enrolled_class_id = ?')->execute([$deleteId]);
            $pdo->prepare('DELETE FROM classes WHERE class_id = ?')->execute([$deleteId]);
            $pdo->commit();
            $message = 'Class deleted successfully.';
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = 'Unable to delete class: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid class selected for deletion.';
        $messageType = 'error';
    }
}

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT class_id, name, code FROM classes WHERE class_id = ?');
    $stmt->execute([$editId]);
    $editClass = $stmt->fetch();
}

if (isset($_POST['save_class'])) {
    $className = trim($_POST['name'] ?? '');
    $classCode = trim($_POST['code'] ?? '');
    $classId = (int) ($_POST['class_id'] ?? 0);

    if ($className === '') {
        $message = 'Class name is required.';
        $messageType = 'error';
    } else {
        try {
            if ($classId > 0) {
                $stmt = $pdo->prepare('UPDATE classes SET name = ?, code = ? WHERE class_id = ?');
                $stmt->execute([$className, $classCode ?: null, $classId]);
                $message = 'Class updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO classes (name, code) VALUES (?, ?)');
                $stmt->execute([$className, $classCode ?: null]);
                $message = 'Class created successfully.';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Unable to save class: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$classes = $pdo->query('SELECT class_id, name, code FROM classes ORDER BY name')->fetchAll();
$successMessage = '';
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">
                <span class="gradient-text">Madrasa Class Management</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                <i class="fa-solid fa-layer-group mr-1"></i>
                Create, edit, and remove class groups used for madrasa enrollment.
            </p>
        </div>
        <div>
            <a href="dashboard.php?page=manage_students" class="px-5 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold hover:bg-slate-50 transition">
                <i class="fa-solid fa-users"></i>
                Manage Learners
            </a>
        </div>
    </div>

    <?php if ($message || $successMessage): ?>
        <div class="mb-6 p-4 rounded-2xl <?php echo $messageType === 'error' ? 'bg-red-50 border-red-200 text-red-700 border' : 'bg-emerald-50 border-emerald-200 text-emerald-700 border'; ?>">
            <div class="flex items-center gap-3">
                <i class="fa-solid <?php echo $messageType === 'error' ? 'fa-triangle-exclamation text-red-500' : 'fa-check-circle text-emerald-500'; ?> text-xl"></i>
                <span><?php echo htmlspecialchars($message ?: $successMessage); ?></span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-current hover:text-slate-900">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Class Groups</h2>
                    <p class="text-sm text-slate-500">All madrasa class names available for enrollment.</p>
                </div>
                <span class="text-xs uppercase tracking-[0.24em] text-slate-400"><?php echo count($classes); ?> entries</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Class Name</th>
                            <th class="px-5 py-4">Code</th>
                            <th class="px-5 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($classes)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-slate-400">No class groups have been added yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($class['name']); ?></td>
                                    <td class="px-5 py-4 text-slate-500"><?php echo htmlspecialchars($class['code'] ?? '—'); ?></td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="dashboard.php?page=manage_classes&edit=<?php echo $class['class_id']; ?>" class="inline-flex items-center gap-2 px-3 py-2 text-amber-600 bg-amber-50 rounded-2xl hover:bg-amber-100 transition">Edit</a>
                                        <form method="POST" action="" class="inline-block">
                                            <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                            <button type="submit" name="delete_class" onclick="return confirm('Delete this class group? This will unassign it from enrolled learners.')" class="inline-flex items-center gap-2 px-3 py-2 text-red-600 bg-red-50 rounded-2xl hover:bg-red-100 transition">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <div class="mb-5">
                <h2 class="text-xl font-semibold text-slate-900"><?php echo $editClass ? 'Edit Class Group' : 'Create New Class Group'; ?></h2>
                <p class="text-sm text-slate-500">Add or update a madrasa class name that learners can enroll into.</p>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($editClass['class_id'] ?? ''); ?>">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($editClass['name'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-blue-500/20 outline-none" placeholder="e.g. Hifdh, Tajweed, Fiqh" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class Code</label>
                    <input type="text" name="code" value="<?php echo htmlspecialchars($editClass['code'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-blue-500/20 outline-none" placeholder="Optional short code">
                </div>
                <button type="submit" name="save_class" class="w-full px-6 py-3 bg-blue-600 text-white rounded-2xl font-medium hover:bg-blue-700 transition">
                    <?php echo $editClass ? 'Update Class Group' : 'Create Class Group'; ?>
                </button>
                <?php if ($editClass): ?>
                    <a href="dashboard.php?page=manage_classes" class="block text-center text-sm text-slate-600 hover:text-slate-900">Cancel edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
