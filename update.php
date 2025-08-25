<?php
declare(strict_types=1);
require __DIR__.'/config.php';
require __DIR__.'/csrf.php';
require __DIR__.'/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$due = trim($_POST['due_date'] ?? '');
$due = $due !== '' ? $due : null;

if ($id <= 0) redirect('index.php?err=' . urlencode('Invalid task id.'));
if ($title === '') redirect('edit.php?id='.(int)$id.'&err=' . urlencode('Title is required.'));
if ($due !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) {
    redirect('edit.php?id='.(int)$id.'&err=' . urlencode('Invalid date format.'));
}

$stmt = $pdo->prepare('UPDATE tasks SET title = :title, due_date = :due WHERE id = :id');
$stmt->execute([':title' => $title, ':due' => $due, ':id' => $id]);

redirect('index.php?ok=' . urlencode('Task updated.'));
