<?php
// /backend/register.php
require_once __DIR__ . "/logger.php";


declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'self'; base-uri 'self'; form-action 'self';");

// Разрешим только POST JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errors' => ['common' => 'Метод не поддерживается']]);
    exit;
}

require __DIR__ . '/../backend/db.php'; // твой db.php (пути проверь)

function json_error(array $errors, int $status = 400) {
    http_response_code($status);
    echo json_encode(['ok' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    exit;
}
function ok_response() {
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

// Читаем JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    json_error(['common' => 'Некорректный запрос'], 400);
}

$username = isset($data['username']) ? trim((string)$data['username']) : '';
$email    = isset($data['email'])    ? trim((string)$data['email'])    : '';
$password = isset($data['password']) ? (string)$data['password']       : '';
$csrf     = isset($data['csrf'])     ? (string)$data['csrf']           : '';

// CSRF double-submit cookie
if (empty($_COOKIE['csrf_token']) || !hash_equals($_COOKIE['csrf_token'], $csrf)) {
    json_error(['common' => 'Истекла сессия формы. Обновите страницу.'], 403);
}

// Валидация
$errors = [];

// Username: 3-30, кир/лат/цифры _ . -, не только спецсимволы
if ($username === '' || mb_strlen($username) < 3 || mb_strlen($username) > 30) {
    $errors['username'] = 'Имя должно быть от 3 до 30 символов.';
} else {
    // Проверим состав
    if (!preg_match('/^[A-Za-zА-Яа-яЁё0-9._-]{3,30}$/u', $username)) {
        $errors['username'] = 'Разрешены буквы (лат/кир), цифры и символы _ . -.';
    } elseif (!preg_match('/[A-Za-zА-Яа-яЁё0-9]/u', $username)) {
        $errors['username'] = 'Имя не может состоять только из специальных символов.';
    }
}

// Email: простая проверка + наличие @ и .
$emailLower = mb_strtolower($email, 'UTF-8');
if ($emailLower === '' || mb_strlen($emailLower) > 254 || strpos($emailLower, '@') === false || strpos($emailLower, '.') === false) {
    $errors['email'] = 'Введите корректный email.';
}

// Password: 8-64, без пробелов, буквы + цифры
if ($password === '' || mb_strlen($password) < 8 || mb_strlen($password) > 64 || preg_match('/\s/u', $password)) {
    $errors['password'] = 'Пароль должен быть от 8 до 64 символов, без пробелов.';
} else {
    if (!preg_match('/[A-Za-zА-Яа-яЁё]/u', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Пароль должен содержать буквы и цифры.';
    }
}

if ($errors) {
    json_error($errors, 422);
}

// Rate limit (MVP): 5 попыток на IP / 10 минут, 3 на email / 10 минут
const RATE_SALT = 'change_this_salt_to_random_secret_please';
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ip_hash = hash('sha256', $ip . RATE_SALT);
$email_hash = hash('sha256', $emailLower . RATE_SALT);

// Смотрим попытки за последние 10 минут
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM registration_attempts WHERE ip_hash = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)");
    $stmt->execute([$ip_hash]);
    $ipCount = (int)$stmt->fetchColumn();

    if ($ipCount >= 5) {
        // Запишем неуспешную попытку
        $pdo->prepare("INSERT INTO registration_attempts (ip_hash, email_hash, success) VALUES (?,?,0)")
            ->execute([$ip_hash, $email_hash]);
        json_error(['common' => 'Слишком много попыток. Попробуйте позже.'], 429);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM registration_attempts WHERE email_hash = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)");
    $stmt->execute([$email_hash]);
    $emailCount = (int)$stmt->fetchColumn();

    if ($emailCount >= 3) {
        $pdo->prepare("INSERT INTO registration_attempts (ip_hash, email_hash, success) VALUES (?,?,0)")
            ->execute([$ip_hash, $email_hash]);
        json_error(['common' => 'Слишком много попыток. Попробуйте позже.'], 429);
    }
} catch (Throwable $e) {
    // Не валим процесс — логика MVP
}

// Проверка уникальности email и username
try {
    // email по нижнему регистру
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$emailLower]);
    if ($stmt->fetch()) {
        $pdo->prepare("INSERT INTO registration_attempts (ip_hash, email_hash, success) VALUES (?,?,0)")
            ->execute([$ip_hash, $email_hash]);
        json_error(['email' => 'Почта уже занята.'], 409);
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $pdo->prepare("INSERT INTO registration_attempts (ip_hash, email_hash, success) VALUES (?,?,0)")
            ->execute([$ip_hash, $email_hash]);
        json_error(['username' => 'Ник уже занят.'], 409);
    }
} catch (Throwable $e) {
    json_error(['common' => 'Ошибка сервера. Попробуйте позже.'], 500);
}

// Всё ок — создаём пользователя
try {
    // Хеш пароля
    if (defined('PASSWORD_ARGON2ID')) {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
    $confirm_token = bin2hex(random_bytes(32));
    $session_token = bin2hex(random_bytes(32));

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("
        INSERT INTO users (
            username, email, password_hash, role,
            email_confirmed, confirm_token, confirm_sent_at, session_token
        ) VALUES (?, ?, ?, 'student', 0, ?, NOW(), ?)
    ");
    $stmt->execute([$username, $emailLower, $hash, $confirm_token, $session_token]);
    $pdo->commit();


    // успех — запись попытки
    $pdo->prepare("INSERT INTO registration_attempts (ip_hash, email_hash, success) VALUES (?,?,1)")
        ->execute([$ip_hash, $email_hash]);

    // Куки сессии (ожидание верификации)
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    setcookie('session_token', $session_token, [
        'expires'  => time() + 12 * 3600, // 12 часов
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Короткое приветствие для UI
    setcookie('welcome_name', $username, [
        'expires'  => time() + 2 * 3600,  // 2 часа
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);

    // Возвращаем ok=true — фронт сделает редирект
    ok_response();

} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    json_error(['common' => 'Ошибка сервера. Попробуйте позже.'], 500);
}
