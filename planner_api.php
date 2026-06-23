<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();
ensure_planner_records_table();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode((string)$raw, true);

if (!is_array($payload) || !isset($payload['state']) || !is_array($payload['state'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid planner data']);
    exit;
}

$dataJson = json_encode($payload['state'], JSON_UNESCAPED_UNICODE);
if ($dataJson === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Planner data could not be encoded']);
    exit;
}

$stmt = db()->prepare(
    'INSERT INTO planner_records (user_id, username, planner_key, data_json)
     VALUES (:user_id, :username, "pectaa_cs10_2026", :data_json)
     ON DUPLICATE KEY UPDATE
        username = VALUES(username),
        data_json = VALUES(data_json),
        updated_at = CURRENT_TIMESTAMP'
);

$stmt->execute([
    ':user_id' => (int)$_SESSION['user_id'],
    ':username' => current_username(),
    ':data_json' => $dataJson,
]);

echo json_encode(['ok' => true]);
