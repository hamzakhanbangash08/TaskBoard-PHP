<?php
require "config.php";
require "functions.php";
require_login();

$user = current_user();

// Task ID from URL
$task_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: index.php?err=Task+not+found");
    exit;
}

// Permission check → manager ya task owner
if ($task['user_id'] !== $user['id'] && !is_manager()) {
    header("Location: index.php?err=Permission+denied");
    exit;
}

// Delete task
$delete = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
$delete->execute([$task_id]);

// Log action
$log = $pdo->prepare("INSERT INTO task_log (task_id, user_id, action) VALUES (?, ?, 'deleted')");
$log->execute([$task_id, $user['id']]);

header("Location: index.php?msg=Task+deleted");
exit;
