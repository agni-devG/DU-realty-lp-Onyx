<?php

declare(strict_types=1);

header('Content-Type: text/plain');

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$configPath = __DIR__ . '/mail-config.php';
$logPath = __DIR__ . '/mail-error.log';

echo "Mail setup check\n";
echo "================\n";
echo 'PHP version: ' . PHP_VERSION . "\n";
echo 'vendor/autoload.php: ' . (is_file($autoloadPath) ? 'FOUND' : 'MISSING') . "\n";
echo 'api/mail-config.php: ' . (is_file($configPath) ? 'FOUND' : 'MISSING') . "\n";
echo 'api folder writable: ' . (is_writable(__DIR__) ? 'YES' : 'NO') . "\n";

if (is_file($configPath)) {
    $config = require $configPath;
    echo 'SMTP host: ' . ($config['smtp_host'] ?? 'missing') . "\n";
    echo 'SMTP port: ' . ($config['smtp_port'] ?? 'missing') . "\n";
    echo 'SMTP user: ' . ($config['smtp_user'] ?? 'missing') . "\n";
    echo 'SMTP secure: ' . ($config['smtp_secure'] ?? 'missing') . "\n";
    echo 'Mail to: ' . ($config['mail_to'] ?? 'missing') . "\n";
    echo 'SMTP password: ' . (!empty($config['smtp_pass']) ? 'SET' : 'MISSING') . "\n";
}

$testWrite = @file_put_contents($logPath, '[' . date('Y-m-d H:i:s') . "] mail-check write test\n", FILE_APPEND);
echo 'Can write mail-error.log: ' . ($testWrite === false ? 'NO' : 'YES') . "\n";
