<?php
// /backend/check_confirm_status.php
require_once __DIR__ . "/logger.php";

declare(strict_types=1);

// Для отладки: включаем ошибки
ini_set('display_errors', '1');
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../db.php";

// Проверка CSRF
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['csrf'], $_COOKIE['csrf_token']) || $input['csrf'] !== $_COOKIE['csrf_token']) {
    log_event("CSRF check failed", ['input' => $input, 'cookie' => $_COOKIE['csrf_token'] ?? null]);
    echo json_encode(['confirmed' => false, 'error' => 'CSRF check failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_COOKIE['session_token'])) {
    log_event("Нет авторизации при check_confirm_status", ['cookies' => $_COOKIE]);
    echo json_encode(['confirmed' => false, 'error' => 'Нет авторизации'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT email_confirmed FROM users WHERE session_token = ?");
    $stmt->execute([$_COOKIE['session_token']]);
    $confirmed = (bool)$stmt->fetchColumn();

    log_event("Проверка статуса подтверждения", [
        'session_token' => $_COOKIE['session_token'],
        'confirmed' => $confirmed
    ]);

    echo json_encode(['confirmed' => $confirmed], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    log_event("Ошибка в check_confirm_status", ['error' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['confirmed' => false, 'error' => 'Server error'], JSON_UNESCAPED_UNICODE);
}
