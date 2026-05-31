<?php
ini_set('error_log', __DIR__ . '/../error_log');
date_default_timezone_set('Asia/Tehran');
chdir(dirname(__DIR__));
require_once 'config.php';
require_once 'functions.php';
require_once 'botapi.php';

$stmt = $pdo->prepare("SELECT * FROM backup_settings WHERE id = 1 LIMIT 1");
$stmt->execute();
$backupSettings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$backupSettings || $backupSettings['status'] !== 'on') {
    exit;
}

$targetChatId = trim((string)($backupSettings['target_chat_id'] ?? ''));
if ($targetChatId === '') {
    exit;
}

$intervalMinutes = max(1, intval($backupSettings['interval_minutes'] ?? 60));
$lastBackupAt = intval($backupSettings['last_backup_at'] ?? 0);
$now = time();
if ($lastBackupAt > 0 && ($now - $lastBackupAt) < ($intervalMinutes * 60)) {
    exit;
}

$backupResult = generateBotBackup();
if (empty($backupResult['success'])) {
    sendmessage($targetChatId, "❌ خطا در ساخت بکاپ خودکار:\n" . ($backupResult['error'] ?? 'خطای نامشخص'), null, 'HTML');
    exit;
}

$sendResult = sendDocument($targetChatId, $backupResult['path'], '🗄 بکاپ خودکار ربات');
@unlink($backupResult['path']);

if (!empty($sendResult['ok'])) {
    $stmt = $pdo->prepare("UPDATE backup_settings SET last_backup_at = ? WHERE id = 1");
    $stmt->execute([$now]);
} else {
    sendmessage($targetChatId, "❌ ارسال بکاپ خودکار به تلگرام ناموفق بود.", null, 'HTML');
}
