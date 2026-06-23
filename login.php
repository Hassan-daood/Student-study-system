<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$username = strtolower(trim((string)($_POST['username'] ?? '')));
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: login.html?error=missing');
    exit;
}

$stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    log_activity(null, $username, 'failed_login');
    header('Location: login.html?error=invalid');
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];

$update = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
$update->execute([':id' => (int)$user['id']]);
log_activity((int)$user['id'], $user['username'], 'login');

if ((int)$user['must_change_password'] === 1) {
    header('Location: settings.php?notice=change_required');
    exit;
}

header('Location: planner.php');
exit;

