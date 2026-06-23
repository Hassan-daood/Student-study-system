<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.html?error=login_required');
        exit;
    }
}

function current_username(): string
{
    return (string)($_SESSION['username'] ?? '');
}

