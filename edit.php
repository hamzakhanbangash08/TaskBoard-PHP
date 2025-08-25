<?php
require "config.php";
require "functions.php";
require_login();

$user = current_user();

// Task ID
$task_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
  header("Location: index.php?err=Task+not+found");
  exit;
}

// Check permission → normal user apna hi task edit kar sakta hai
if ($task['user_id'] !== $user['id'] && !is_manager()) {
  header("Location: index.php?err=Permission+denied");
  exit;
}

// Update form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $due_date = $_POST['due_date'] ?? null;
  $assigned_user = (int)($_POST['user_id'] ?? $task['user_id']);

  if ($title === '') {
    header("Location: edit.php?id=$task_id&err=Title+required");
    exit;
  }

  if (is_manager()) {
    // Manager can reassign
    $update = $pdo->prepare("UPDATE tasks SET title=?, due_date=?, user_id=? WHERE id=?");
    $update->execute([$title, $due_date ?: null, $assigned_user, $task_id]);
  } else {
    // Normal user can only update title + due_date
    $update = $pdo->prepare("UPDATE tasks SET title=?, due_date=? WHERE id=?");
    $update->execute([$title, $due_date ?: null, $task_id]);
  }

  // Log
  $log = $pdo->prepare("INSERT INTO task_log (task_id, user_id, action) VALUES (?,?, 'updated')");
  $log->execute([$task_id, $user['id']]);

  header("Location: index.php?msg=Task+updated");
  exit;
}

// Fetch users list if manager
$users = [];
if (is_manager()) {
  $users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Edit Task</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <div class="container">
    <h1>Edit Task</h1>
    <form method="post">
      <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">

      <label>Title</label><br>
      <input type="text" name="title" value="<?= h($task['title']) ?>" required><br><br>

      <label>Deadline</label><br>
      <input type="date" name="due_date" value="<?= h($task['due_date']) ?>"><br><br>

      <?php if (is_manager()): ?>
        <label>Assign To</label><br>
        <select name="user_id">
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['id'] == $task['user_id'] ? 'selected' : '' ?>>
              <?= h($u['name']) ?>
            </option>
          <?php endforeach; ?>
        </select><br><br>
      <?php endif; ?>

      <button class="btn primary" type="submit">Save Changes</button>
      <a href="index.php" class="btn">Cancel</a>
    </form>
  </div>
</body>

</html>