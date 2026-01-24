<?php
function app_log($message, $context = []) {
    $logDir = BASE_PATH . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $contextJson = '';
    if (!empty($context)) {
        $contextJson = ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $line = "[{$timestamp}] {$message}{$contextJson}" . PHP_EOL;
    @file_put_contents($logDir . '/app.log', $line, FILE_APPEND);
}
