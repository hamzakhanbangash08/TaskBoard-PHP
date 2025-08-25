<?php
// config.php — database connection + session
declare(strict_types=1);
session_start();

$DB_HOST = "localhost";
$DB_NAME = "todolist";
$DB_USER = "root";
$DB_PASS = "";
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed. Please check config.php.\n";
    if (ini_get('display_errors')) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
    exit;
}

//
// 🚀 Auto-create Tables if not exists
//
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','manager','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    due_date DATE DEFAULT NULL,
    issue_date DATE DEFAULT CURRENT_DATE,
    last_date DATE DEFAULT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    completed_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS task_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    action ENUM('created','completed','reopened','updated') NOT NULL,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
");

//
// 🚀 Auto-upgrade helper
//
function addColumnIfNotExists(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :col");
    $stmt->execute([':col' => $column]);
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

//
// 🚀 Ensure all required columns exist
//

// Users table
addColumnIfNotExists($pdo, "users", "role", "ENUM('user','manager','admin') DEFAULT 'user'");
addColumnIfNotExists($pdo, "users", "created_at", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

// Tasks table
addColumnIfNotExists($pdo, "tasks", "due_date", "DATE DEFAULT NULL");
addColumnIfNotExists($pdo, "tasks", "issue_date", "DATE NOT NULL DEFAULT CURRENT_DATE");
addColumnIfNotExists($pdo, "tasks", "last_date", "DATE DEFAULT NULL");
addColumnIfNotExists($pdo, "tasks", "is_completed", "TINYINT(1) DEFAULT 0");
addColumnIfNotExists($pdo, "tasks", "completed_by", "INT UNSIGNED DEFAULT NULL");
addColumnIfNotExists($pdo, "tasks", "created_at", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

// Task Log table
addColumnIfNotExists($pdo, "task_log", "action_date", "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

//
// Helper function for redirect
//
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}
