<?php
// /backend/send_confirm_email.php
require_once __DIR__ . "/logger.php";

header("Content-Type: application/json; charset=UTF-8");



// Проверка CSRF (double-submit cookie)
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['csrf'], $_COOKIE['csrf_token']) || $input['csrf'] !== $_COOKIE['csrf_token']) {
    echo json_encode(['ok' => false, 'error' => 'CSRF check failed']);
    exit;
}

// !!! Настрой подключение к БД через PDO
require_once __DIR__ . "/../db.php"; // в db.php у тебя PDO $pdo

// Получаем email пользователя из сессии или куки (как у тебя реализовано при регистрации)
if (!isset($_COOKIE['session_token'])) {
    echo json_encode(['ok' => false, 'error' => 'Нет авторизации']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, email, email_confirmed FROM users WHERE session_token = ?");
$stmt->execute([$_COOKIE['session_token']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(['ok' => false, 'error' => 'Пользователь не найден']);
    exit;
}
if ($user['email_confirmed']) {
    echo json_encode(['ok' => true, 'already' => true]);
    exit;
}

// Генерация токена подтверждения
$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare("UPDATE users SET confirm_token = ?, confirm_sent_at = NOW() WHERE id = ?");
$stmt->execute([$token, $user['id']]);

// Отправка письма через Gmail (PHPMailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . "/../vendor/autoload.php";

$mail = new PHPMailer(true);

try {
    // !!! Настрой Gmail-аккаунт
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'eduproacademynew@gmail.com'; // !!!
    $mail->Password = 'tvmp zvzx vmdg tbjk';    // !!! (пароль приложения Gmail)
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('eduproacademynew@gmail.com', 'Онлайн Академия'); // !!!
    $mail->addAddress($user['email']);

    $link = "http://localhost:8080/backend/confirm_email.php?token=" . urlencode($token);

    $mail->isHTML(true);
    $mail->Subject = "Подтверждение почты — Онлайн Академия";
    $mail->Body = "Здравствуйте!<br><br>Для подтверждения вашей почты перейдите по ссылке:<br>
                   <a href='{$link}'>Подтвердить почту</a><br><br>
                   Если вы не регистрировались, просто игнорируйте это письмо.";

    $mail->send();
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Ошибка отправки: ' . $mail->ErrorInfo]);
}
