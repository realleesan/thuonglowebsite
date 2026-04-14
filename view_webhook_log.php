<?php
/**
 * View Webhook Log from Server
 */

$logFile = __DIR__ . '/logs/webhook_debug.log';

header('Content-Type: text/plain');

if (!file_exists($logFile)) {
    die("Log file not found: {$logFile}\n");
}

echo "=== Last 100 lines of webhook_debug.log ===\n\n";

$lines = file($logFile);
$lastLines = array_slice($lines, -100);

foreach ($lastLines as $line) {
    echo $line;
}
