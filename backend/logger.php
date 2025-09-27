<?php
// logger.php — общий логгер

function log_event(string $message, array $context = []): void {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $file = $logDir . '/app.log';
    $date = date('Y-m-d H:i:s');
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'cli';

    $line = "[$date][$ip] $message";
    if ($context) {
        $line .= " " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}
