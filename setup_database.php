<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$temporaryPassword = 'MMS@2026';
$usernames = [];
for ($i = 1; $i <= 70; $i++) {
    $usernames[] = 'student' . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
}

try {
    $server = db_server();
    $server->exec(
        'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $pdo = db();
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM("student", "admin") NOT NULL DEFAULT "student",
            must_change_password TINYINT(1) NOT NULL DEFAULT 1,
            activated_at DATETIME NULL,
            last_login_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            username VARCHAR(50) NOT NULL,
            action VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_username (username),
            INDEX idx_activity_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    ensure_planner_records_table();

    $insert = $pdo->prepare(
        'INSERT IGNORE INTO users (username, password_hash, must_change_password)
         VALUES (:username, :password_hash, 1)'
    );

    foreach ($usernames as $username) {
        $insert->execute([
            ':username' => $username,
            ':password_hash' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
        ]);
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Database setup failed</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PECTAA Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; line-height: 1.5; color: #172033; }
        table { border-collapse: collapse; width: 100%; margin-top: 18px; }
        th, td { border: 1px solid #d7dde8; padding: 8px 10px; text-align: left; }
        th { background: #eef3f9; }
        .ok { background: #e8f8ef; border: 1px solid #9fd8b6; padding: 12px 16px; border-radius: 8px; }
        code { background: #f2f4f8; padding: 2px 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>PECTAA Study Planner Database Ready</h1>
    <div class="ok">
        Database <code><?= htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') ?></code> ready hai.
        Total users in database: <strong><?= $count ?></strong>.
    </div>
    <p>Student's temporary passwords have been generated.they can change their password after first login.</p>
    <p><strong>Temporary password for all students:</strong> <code><?= htmlspecialchars($temporaryPassword, ENT_QUOTES, 'UTF-8') ?></code></p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Temporary Password</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usernames as $index => $username): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($temporaryPassword, ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
