<?php
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    echo '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf()
{
    if ($_POST['csrf'] ?? '' !== ($_SESSION['csrf'] ?? '')) {
        die("CSRF token mismatch");
    }
}
