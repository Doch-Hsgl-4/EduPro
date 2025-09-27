<?php
// db.php — подключение к MySQL
require_once __DIR__ . "/logger.php";


$host = 'db'; // имя сервиса из docker-compose, а не localhost
$dbname = 'academy';
$username = 'academy_user';
$password = 'userpass';

// Настройки PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // ошибки в виде исключений
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // ассоциативный массив по умолчанию
        PDO::ATTR_EMULATE_PREPARES => false, // использование нативных prepared statements
    ]);
} catch (PDOException $e) {
    // Ошибка подключения
    http_response_code(500);
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
    exit;
}
?>