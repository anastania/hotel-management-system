<?php

// Initialize error handling first
require_once __DIR__ . '/ErrorHandler.php';
ErrorHandler::init();

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Load core files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AdminLogger.php';
require_once __DIR__ . '/Mailer.php';

// Initialize security
Security::init();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to safely transition to new features
function useNewFeatures() {
    static $enabled = null;
    
    if ($enabled === null) {
        // Check if all required components are available
        $enabled = class_exists('ErrorHandler') &&
                  class_exists('Database') &&
                  class_exists('Security') &&
                  class_exists('Logger');
    }
    
    return $enabled;
}

// Backward compatibility function for database operations
function getDatabase() {
    if (useNewFeatures()) {
        return Database::getInstance();
    } else {
        // Return existing database connection
        global $conn;
        return $conn;
    }
}

// Backward compatibility function for user authentication
function getCurrentUser() {
    if (useNewFeatures()) {
        return Auth::getInstance()->user();
    } else {
        // Return user using existing session method
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
}

// Error handling that works with both old and new code
function handleError($message, $redirect = null) {
    if (useNewFeatures()) {
        Logger::error($message);
        if ($redirect) {
            header("Location: $redirect");
            exit;
        }
    } else {
        // Existing error handling
        $_SESSION['error'] = $message;
        if ($redirect) {
            header("Location: $redirect");
            exit;
        }
    }
}
