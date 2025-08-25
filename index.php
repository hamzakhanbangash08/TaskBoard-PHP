<?php
require "config.php";
require "functions.php";
require_login();

$user = current_user();

// ✅ Handle task completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_task_id'])) {
    $taskId = (int)$_POST['complete_task_id'];

    if (is_admin_or_manager()) {
        // Admin/Manager kisi ka bhi complete kar sakte hain
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET is_completed = 1, completed_by = ? 
            WHERE id = ?
        ");
        $stmt->execute([$user['id'], $taskId]);
    } else {
        // Normal user sirf apna hi complete kar sakta hai
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET is_completed = 1, completed_by = ? 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$user['id'], $taskId, $user['id']]);
    }

    header("Location: index.php?msg=Task+completed+successfully");
    exit;
}

// ✅ Get tasks (Admin/Manager sab dekhte hain, user apne)
if (is_admin_or_manager()) {
    $stmt = $pdo->query("
        SELECT t.*, u.name AS assigned_to, cu.name AS completed_user
        FROM tasks t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN users cu ON t.completed_by = cu.id
        ORDER BY t.created_at DESC
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT t.*, u.name AS assigned_to, cu.name AS completed_user
        FROM tasks t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN users cu ON t.completed_by = cu.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user['id']]);
}
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Task List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Task List</h1>
            <div>
                <?php if (is_admin_or_manager()): ?>
                    <a href="create.php" class="btn btn-success">+ New Task</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= h($_GET['msg']) ?></div>
        <?php endif; ?>

        <table class="table table-bordered bg-white shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Assigned To</th>
                    <th>Issue Date</th>
                    <th>Last Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= h($t['title']) ?></td>
                        <td><?= h($t['assigned_to']) ?></td>
                        <td><?= h($t['issue_date']) ?></td>
                        <td><?= h($t['last_date']) ?></td>
                        <td>
                            <?php if ($t['is_completed']): ?>
                                <span class="badge bg-success">✅ Completed</span>
                                <br><small>by <?= h($t['completed_user'] ?? 'Unknown') ?></small>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$t['is_completed']): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="complete_task_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Mark Complete</button>
                                </form>
                            <?php else: ?>
                                <em>Done</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>