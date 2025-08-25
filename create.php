<?php
require "config.php";
require "functions.php";
require_login();

$user = current_user();

// 🚫 Sirf Admin/Manager task create kar sakte hain
if (!is_admin_or_manager()) {
    redirect("index.php");
}

// Admin/Manager sab users ke liye task assign kar sakta hai
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $issue_date = $_POST['issue_date'] ?? date('Y-m-d');
    $last_date = $_POST['last_date'] ?? null;
    $assign_to = (int)($_POST['user_id'] ?? $user['id']);

    if ($title === '') {
        $error = "Task title is required!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, user_id, issue_date, last_date) VALUES (?,?,?,?)");
        $stmt->execute([$title, $assign_to, $issue_date, $last_date]);

        // Log entry
        $task_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO task_log (task_id, user_id, action) VALUES (?,?, 'created')");
        $log->execute([$task_id, $user['id']]);

        header("Location: index.php?msg=Task+added");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Task</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Create Task</h1>
            <a href="index.php" class="btn btn-outline-secondary">← Back to list</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Task Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Issue Date</label>
                <input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Last Date</label>
                <input type="date" name="last_date" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Assign To</label>
                <select name="user_id" class="form-select" required>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= h($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Create Task</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>