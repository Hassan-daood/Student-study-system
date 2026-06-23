<?php
declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'pectaa_study_planner';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function db_server(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function log_activity(?int $userId, string $username, string $action): void
{
    $stmt = db()->prepare(
        'INSERT INTO activity_logs (user_id, username, action, ip_address, user_agent)
         VALUES (:user_id, :username, :action, :ip_address, :user_agent)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':username' => $username,
        ':action' => $action,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ]);
}

function ensure_planner_records_table(): void
{
    db()->exec(
        'CREATE TABLE IF NOT EXISTS planner_records (
            user_id INT NOT NULL PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            planner_key VARCHAR(80) NOT NULL DEFAULT "pectaa_cs10_2026",
            data_json LONGTEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_planner_records_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,
            INDEX idx_planner_records_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}
