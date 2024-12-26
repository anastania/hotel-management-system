<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class ErrorHandler {
    private static $logger;
    
    public static function init() {
        // Set up error handling
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
        
        // Initialize logger
        self::initLogger();
    }
    
    private static function initLogger() {
        self::$logger = new Logger('hotel_reservation');
        
        // Create logs directory if it doesn't exist
        $logsDir = __DIR__ . '/../logs';
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0777, true);
        }
        
        // Add handlers
        $formatter = new LineFormatter(
            "[%datetime%] %level_name%: %message% %context% %extra%\n",
            "Y-m-d H:i:s"
        );
        
        // Main log file with rotation
        $rotatingHandler = new RotatingFileHandler(
            $logsDir . '/app.log',
            30, // Keep 30 days of logs
            Logger::DEBUG
        );
        $rotatingHandler->setFormatter($formatter);
        self::$logger->pushHandler($rotatingHandler);
        
        // Separate error log
        $errorHandler = new StreamHandler(
            $logsDir . '/error.log',
            Logger::ERROR
        );
        $errorHandler->setFormatter($formatter);
        self::$logger->pushHandler($errorHandler);
    }
    
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $message = sprintf(
            'Error: %s in %s on line %d',
            $errstr,
            $errfile,
            $errline
        );
        
        self::logError($message, [
            'type' => $errno,
            'file' => $errfile,
            'line' => $errline
        ]);
        
        if (ini_get('display_errors')) {
            self::displayError($message);
        }
        
        return true;
    }
    
    public static function handleException($exception) {
        $message = sprintf(
            'Exception: %s in %s on line %d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        self::logError($message, [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if (ini_get('display_errors')) {
            self::displayError($message);
        } else {
            self::displayFriendlyError();
        }
    }
    
    public static function handleFatalError() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = sprintf(
                'Fatal Error: %s in %s on line %d',
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            self::logError($message, [
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
            
            if (ini_get('display_errors')) {
                self::displayError($message);
            } else {
                self::displayFriendlyError();
            }
        }
    }
    
    private static function logError($message, array $context = []) {
        if (self::$logger) {
            self::$logger->error($message, $context);
            
            // Store in database if available
            try {
                if (class_exists('Database')) {
                    $db = Database::getInstance();
                    $db->insert('error_logs', [
                        'error_type' => $context['type'] ?? 'unknown',
                        'error_message' => $message,
                        'stack_trace' => $context['trace'] ?? null
                    ]);
                }
            } catch (Exception $e) {
                // If database logging fails, just continue
                self::$logger->error('Failed to log error to database: ' . $e->getMessage());
            }
        }
    }
    
    private static function displayError($message) {
        if (php_sapi_name() === 'cli') {
            echo $message . "\n";
        } else {
            include __DIR__ . '/../templates/error.php';
        }
    }
    
    private static function displayFriendlyError() {
        if (php_sapi_name() === 'cli') {
            echo "An error occurred. Please check the error logs for details.\n";
        } else {
            include __DIR__ . '/../templates/error.php';
        }
    }
}
