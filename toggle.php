<?php
require 'config.php';
require 'functions.php';
require_login();

$user = current_user();
$task_id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!$task_id || !in_array($action, ['complete', 'reopen'])) {
    redirect('index.php');
}

// Get task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id=?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    redirect('index.php');
}

// Permission check: manager or task owner
if (!is_manager() && $task['user_id'] != $user['id']) {
    redirect('index.php');
}

// Complete or reopen
if ($action === 'complete' && !$task['is_completed']) {
    $stmt = $pdo->prepare("UPDATE tasks SET is_completed=1, completed_by=? WHERE id=?");
    $stmt->execute([$user['id'], $task_id]);

    // Log
    $log = $pdo->prepare("INSERT INTO task_log (task_id, user_id, action) VALUES (?, ?, 'completed')");
    $log->execute([$task_id, $user['id']]);
} elseif ($action === 'reopen' && $task['is_completed']) {
    $stmt = $pdo->prepare("UPDATE tasks SET is_completed=0, completed_by=NULL WHERE id=?");
    $stmt->execute([$task_id]);

    // Log
    $log = $pdo->prepare("INSERT INTO task_log (task_id, user_id, action) VALUES (?, ?, 'reopened')");
    $log->execute([$task_id, $user['id']]);
}

redirect('index.php');
