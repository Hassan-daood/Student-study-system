<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

$stmt = db()->prepare('SELECT must_change_password FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => (int)$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: login.html?error=login_required');
    exit;
}

if ((int)$user['must_change_password'] === 1) {
    header('Location: settings.php?notice=change_required');
    exit;
}

ensure_planner_records_table();

$plannerPath = __DIR__ . '/PECTAA_CS10_Smart_Planner.html';
$html = file_get_contents($plannerPath);

if ($html === false) {
    http_response_code(500);
    exit('Planner file not found.');
}

$username = htmlspecialchars(current_username(), ENT_QUOTES, 'UTF-8');
$savedState = [];
$recordStmt = db()->prepare('SELECT data_json FROM planner_records WHERE user_id = :user_id LIMIT 1');
$recordStmt->execute([':user_id' => (int)$_SESSION['user_id']]);
$record = $recordStmt->fetch();
if ($record && isset($record['data_json'])) {
    $decoded = json_decode((string)$record['data_json'], true);
    if (is_array($decoded)) {
        $savedState = $decoded;
    }
}

$plannerConfig = '<script>window.PECTAA_CURRENT_USER = ' .
    json_encode(current_username(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) .
    '; window.PECTAA_INITIAL_STATE = ' .
    json_encode($savedState, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) .
    ';</script>';

$accountBar = <<<HTML
<div class="account-bar">
  <span>Logged in: <strong>{$username}</strong></span>
  <a href="settings.php">Account Settings</a>
  <a href="logout.php">Logout</a>
</div>
<style>
.account-bar {
  position: sticky;
  top: 0;
  z-index: 9999;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  padding: 9px 16px;
  background: #102d21;
  color: #fff;
  font-family: Arial, sans-serif;
  font-size: 13px;
}
.account-bar a {
  color: #fff;
  text-decoration: none;
  font-weight: 700;
  border: 1px solid rgba(255,255,255,.38);
  border-radius: 6px;
  padding: 5px 9px;
}
.account-bar a:hover { background: rgba(255,255,255,.12); }
@media print { .account-bar { display: none !important; } }
</style>
HTML;

$html = preg_replace('/<body([^>]*)>/i', '<body$1>' . $accountBar, $html, 1);
$html = preg_replace('/<script>/i', $plannerConfig . '<script>', $html, 1);
echo $html;
