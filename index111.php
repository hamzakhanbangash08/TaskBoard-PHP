<?php

declare(strict_types=1);
require __DIR__ . '/config.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/functions.php';

// Filter handling
$filter = $_GET['show'] ?? 'all'; // all|active|completed
$valid = ['all', 'active', 'completed'];
if (!in_array($filter, $valid, true)) $filter = 'all';

$sql = "SELECT * FROM tasks";
if ($filter === 'active') {
  $sql .= " WHERE is_completed = 0";
} elseif ($filter === 'completed') {
  $sql .= " WHERE is_completed = 1";
}
$sql .= " ORDER BY COALESCE(due_date, '9999-12-31') ASC, created_at DESC";
$stmt = $pdo->query($sql);
$tasks = $stmt->fetchAll();

$flash = $_GET['ok'] ?? '';
$err = $_GET['err'] ?? '';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>To‑Do List (PHP)</title>
  <link rel="stylesheet" href="styles.css">

  <style>
    /* ==== Reset & Base ==== */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #f4f6f9;
      color: #333;
      font-size: 16px;
      line-height: 1.5;
    }

    a {
      text-decoration: none;
      color: #0077cc;
    }

    a:hover {
      text-decoration: underline;
    }

    /* ==== Layout ==== */
    .container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    h1 {
      font-size: 28px;
      margin-bottom: 6px;
      color: #222;
    }

    h3 {
      margin: 20px 0 10px;
      font-size: 20px;
    }

    .muted {
      font-size: 14px;
      color: #777;
    }

    /* ==== Flash & Error ==== */
    .flash {
      background: #e6ffed;
      border: 1px solid #8be79b;
      color: #217a36;
      padding: 10px;
      border-radius: 8px;
      margin: 15px 0;
    }

    .error {
      background: #ffeaea;
      border: 1px solid #ff8a8a;
      color: #c62828;
      padding: 10px;
      border-radius: 8px;
      margin: 15px 0;
    }

    /* ==== Buttons ==== */
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      margin: 2px;
      transition: all 0.2s ease;
    }

    .btn.primary {
      background: #0077cc;
      color: #fff;
    }

    .btn.primary:hover {
      background: #005fa3;
    }

    .btn.danger {
      background: #e74c3c;
      color: #fff;
    }

    .btn.danger:hover {
      background: #c0392b;
    }

    .btn:hover {
      opacity: 0.9;
    }

    /* ==== Filters ==== */
    .filters {
      margin: 15px 0;
    }

    .filters a {
      margin: 0 8px;
      padding: 5px 10px;
      border-radius: 6px;
      font-weight: 500;
    }

    .filters a:hover {
      background: #f0f0f0;
    }

    .filters .active {
      background: #0077cc;
      color: #fff;
      font-weight: bold;
    }

    /* ==== Task List ==== */
    .list {
      margin-top: 20px;
    }

    .task {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fafafa;
      border: 1px solid #eee;
      padding: 12px;
      margin-bottom: 12px;
      border-radius: 10px;
      transition: background 0.2s ease;
    }

    .task:hover {
      background: #f0f8ff;
    }

    .task.done .title strong {
      text-decoration: line-through;
      color: #777;
    }

    .task .title {
      flex: 1;
      margin: 0 12px;
    }

    /* ==== Badge ==== */
    .badge {
      display: inline-block;
      padding: 4px 8px;
      font-size: 12px;
      border-radius: 6px;
      background: #e0e0e0;
      margin-right: 5px;
      color: #555;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>To‑Do List</h1>
    <div class="muted">Add tasks, set a deadline, mark as done ✅</div>

    <?php if ($flash): ?>
      <div class="flash"><?= h($flash) ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="error"><?= h($err) ?></div>
    <?php endif; ?>

    <h3>Add Task</h3>
    <form method="post" action="create.php" class="row">
      <?php csrf_field(); ?>
      <input type="text" name="title" placeholder="What do you need to do?" required style="min-width: 260px;">
      <input type="date" name="due_date">
      <button class="btn primary" type="submit">Add</button>
    </form>

    <div class="row" style="justify-content: space-between;">
      <div class="filters">
        <span class="badge">Filter:</span>
        <a href="?show=all">All</a>
        <a href="?show=active">Active</a>
        <a href="?show=completed">Completed</a>
      </div>
      <div class="muted">
        <?= count($tasks) ?> task(s) shown
      </div>
    </div>

    <div class="list">
      <?php foreach ($tasks as $t): ?>
        <div class="task <?= $t['is_completed'] ? 'done' : '' ?>">
          <form method="post" action="toggle.php">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button class="btn" type="submit" title="Toggle done"><?= $t['is_completed'] ? '☑' : '☐' ?></button>
          </form>
          <div class="title">
            <strong><?= h($t['title']) ?></strong><br>
            <span class="muted">Created: <?= h($t['created_at']) ?></span>
          </div>
          <div>
            <?php if ($t['due_date']): ?>
              <span class="badge">Due: <?= h($t['due_date']) ?></span>
            <?php else: ?>
              <span class="badge">No deadline</span>
            <?php endif; ?>
          </div>
          <div class="row">
            <form method="get" action="edit.php">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn" type="submit">Edit</button>
            </form>
            <form method="post" action="delete.php" onsubmit="return confirm('Delete this task?');">
              <?php csrf_field(); ?>
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn danger" type="submit">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>

</html>