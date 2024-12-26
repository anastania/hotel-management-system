<?php

class Logger {
    private static $logFile = __DIR__ . '/../logs/app.log';
    private static $errorLogFile = __DIR__ . '/../logs/error.log';
    
    public static function init() {
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    public static function info($message, array $context = []) {
        self::log('INFO', $message, $context);
    }

    public static function error($message, array $context = []) {
        self::log('ERROR', $message, $context, self::$errorLogFile);
    }

    public static function debug($message, array $context = []) {
        if ($_ENV['APP_DEBUG'] ?? false) {
            self::log('DEBUG', $message, $context);
        }
    }

    private static function log($level, $message, array $context = [], $file = null) {
        $file = $file ?? self::$logFile;
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$date] [$level] $message $contextStr\n";
        file_put_contents($file, $logMessage, FILE_APPEND);
    }
}
