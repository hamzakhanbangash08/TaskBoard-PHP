<?php
require "config.php";
require "functions.php";
require_admin(); // sirf admin access kar sakta hai

// Role update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'], $_POST['role'])) {
        $uid = (int)$_POST['user_id'];
        $role = $_POST['role'];

        if ($uid !== $_SESSION['user_id'] && in_array($role, ['user', 'manager', 'admin'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $uid]);
            flash("User role updated successfully!", "success");
        } else {
            flash("Invalid role update!", "danger");
        }
        header("Location: admin_dashboard.php");
        exit;
    }

    // Delete user request
    if (isset($_POST['delete_user_id'])) {
        $uid = (int)$_POST['delete_user_id'];

        if ($uid !== $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            flash("User deleted successfully!", "warning");
        } else {
            flash("You cannot delete your own account!", "danger");
        }
        header("Location: admin_dashboard.php");
        exit;
    }
}

// All users
$users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary">Admin Dashboard</h1>
            <a href="index.php" class="btn btn-secondary">← Back to Tasks</a>
        </div>

        <!-- Flash message -->
        <?php if ($f = get_flash()): ?>
            <div class="alert alert-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Users Management</div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th width="280">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= h($u['id']) ?></td>
                                <td><?= h($u['name']) ?></td>
                                <td><?= h($u['email']) ?></td>
                                <td><span class="badge bg-info"><?= h($u['role']) ?></span></td>
                                <td><?= h($u['created_at']) ?></td>
                                <td class="d-flex">
                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <!-- Role update -->
                                        <form method="post" class="d-flex me-2">
                                            <input type="hidden" name="user_id" value="<?= h($u['id']) ?>">
                                            <select name="role" class="form-select form-select-sm me-2" required>
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                <option value="manager" <?= $u['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                                        </form>

                                        <!-- Delete user -->
                                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this user? This will also remove their tasks!');">
                                            <input type="hidden" name="delete_user_id" value="<?= h($u['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <em>—</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>