<?php
// functions.php

// Agar config.php already load nahi hua to yeh line rakho
require_once __DIR__ . "/config.php";

/**
 * Escape output for HTML
 */
function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Ensure user is logged in
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect("login.php");
    }
}

/**
 * Get current logged-in user
 */
function current_user(): ?array
{
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached === null) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cached = $stmt->fetch();
    }
    return $cached;
}

/**
 * Role helpers
 */
function is_admin(): bool
{
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_manager(): bool
{
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'manager';
}

function is_admin_or_manager(): bool
{
    return is_admin() || is_manager();
}

/**
 * Require only Admin access
 */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        redirect("index.php?error=Access+Denied");
    }
}

/**
 * Require Admin OR Manager access
 */
function require_admin_or_manager(): void
{
    require_login();
    if (!is_admin_or_manager()) {
        redirect("index.php?error=Access+Denied");
    }
}
