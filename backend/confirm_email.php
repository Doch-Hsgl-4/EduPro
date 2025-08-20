<?php
// /backend/confirm_email.php
require_once __DIR__ . "/../db.php";

if (!isset($_GET['token'])) {
    die("Некорректная ссылка.");
}

$token = $_GET['token'];

// Подтверждение почты
$stmt = $pdo->prepare("SELECT id FROM users WHERE confirm_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Токен недействителен или уже использован.");
}

$stmt = $pdo->prepare("UPDATE users SET email_confirmed = 1, confirm_token = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

echo "<h2>✅ Почта подтверждена!</h2><p>Теперь вы можете перейти на <a href='/ru/map.php'>карту</a>.</p>";
