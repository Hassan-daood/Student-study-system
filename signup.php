<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.html');
    exit;
}

$username = strtolower(trim((string)($_POST['username'] ?? '')));
$temporaryPassword = (string)($_POST['temporary_password'] ?? '');
$newPassword = (string)($_POST['new_password'] ?? '');
$confirmPassword = (string)($_POST['confirm_password'] ?? '');

if ($username === '' || $temporaryPassword === '' || $newPassword === '') {
    exit('All fields are required. <a href="signup.html">Go back</a>');
}

if (strlen($newPassword) < 6) {
    exit('New password must be at least 6 characters. <a href="signup.html">Go back</a>');
}

if ($newPassword !== $confirmPassword) {
    exit('Passwords do not match. <a href="signup.html">Go back</a>');
}

$stmt = db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($temporaryPassword, $user['password_hash'])) {
    log_activity(null, $username, 'failed_signup');
    exit('Invalid username or temporary password. <a href="signup.html">Try again</a>');
}

$update = db()->prepare(
    'UPDATE users
     SET password_hash = :password_hash, must_change_password = 0, activated_at = COALESCE(activated_at, NOW())
     WHERE id = :id'
);
$update->execute([
    ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
    ':id' => (int)$user['id'],
]);

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = $user['username'];
log_activity((int)$user['id'], $user['username'], 'signup_password_set');

header('Location: planner.php');
exit;

