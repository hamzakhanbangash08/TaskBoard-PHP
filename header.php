<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?? "Todo App" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">TodoApp</a>
            <div>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <span class="text-white me-3">Hello, <?= h($_SESSION['name'] ?? '') ?></span>
                    <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mt-4">