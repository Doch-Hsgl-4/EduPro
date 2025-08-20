<?php
// /backend/cleanup_inactive.php
require_once __DIR__ . "/../db.php";

$stmt = $pdo->prepare("DELETE FROM users WHERE email_confirmed = 0 AND confirm_sent_at < (NOW() - INTERVAL 1 DAY)");
$stmt->execute();
