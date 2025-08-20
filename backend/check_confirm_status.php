<?php
// /backend/check_confirm_status.php
header("Content-Type: application/json; charset=UTF-8");

session_start();

// Проверка CSRF
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['csrf'], $_COOKIE['csrf_token']) || $input['csrf'] !== $_COOKIE['csrf_token']) {
    echo json_encode(['confirmed' => false, 'error' => 'CSRF check failed']);
    exit;
}

require_once __DIR__ . "/../db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['confirmed' => false, 'error' => 'Нет авторизации']);
    exit;
}

$stmt = $pdo->prepare("SELECT email_confirmed FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$confirmed = $stmt->fetchColumn();

echo json_encode(['confirmed' => (bool)$confirmed]);
