<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$message = '';
$error = '';
$notice = (string)($_GET['notice'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int)$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        $error = 'Current password ghalat hai.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password kam az kam 6 characters ka hona chahiye.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password aur confirm password match nahi kar rahe.';
    } else {
        $update = db()->prepare(
            'UPDATE users SET password_hash = :password_hash, must_change_password = 0, activated_at = COALESCE(activated_at, NOW()) WHERE id = :id'
        );
        $update->execute([
            ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => (int)$_SESSION['user_id'],
        ]);
        log_activity((int)$_SESSION['user_id'], current_username(), 'password_changed');
        $message = 'Password update ho gaya.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <style>
        body { min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; background: #f0f4f9; color: #172033; }
        .box { width: min(440px, 90vw); background: white; border: 1px solid #d7dde8; border-radius: 10px; padding: 24px; box-shadow: 0 14px 35px rgba(20, 36, 55, .12); }
        h1 { margin: 0 0 6px; font-size: 24px; }
        .user { color: #617086; margin-bottom: 18px; }
        label { display: block; margin-top: 12px; font-weight: 700; font-size: 13px; }
        input { width: 100%; box-sizing: border-box; margin-top: 6px; padding: 10px; border: 1px solid #c3cede; border-radius: 7px; font-size: 15px; }
        button, a.button { display: inline-block; margin-top: 18px; padding: 10px 18px; border: 0; border-radius: 7px; background: #1a3a5c; color: white; font-weight: 700; text-decoration: none; cursor: pointer; }
        a.button.secondary { background: #617086; }
        .ok { background: #e8f8ef; border: 1px solid #9fd8b6; color: #185c35; padding: 10px; border-radius: 7px; }
        .err { background: #fdeaea; border: 1px solid #efaaaa; color: #8a1f1f; padding: 10px; border-radius: 7px; }
        .notice { background: #fff8dc; border: 1px solid #e7c65a; color: #6c5100; padding: 10px; border-radius: 7px; }
    </style>
</head>
<body>
    <form class="box" method="POST">
        <h1>Account Settings</h1>
        <div class="user">Logged in as <strong><?= htmlspecialchars(current_username(), ENT_QUOTES, 'UTF-8') ?></strong></div>

        <?php if ($notice === 'change_required'): ?>
            <p class="notice">Pehli dafa login ke baad apna password change karna zaroori hai.</p>
        <?php endif; ?>
        <?php if ($message !== ''): ?>
            <p class="ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label for="current_password">Current Password</label>
        <input id="current_password" name="current_password" type="password" required>

        <label for="new_password">New Password</label>
        <input id="new_password" name="new_password" type="password" minlength="6" required>

        <label for="confirm_password">Confirm New Password</label>
        <input id="confirm_password" name="confirm_password" type="password" minlength="6" required>

        <button type="submit">Update Password</button>
        <a class="button secondary" href="planner.php">Back to Planner</a>
    </form>
</body>
</html>

