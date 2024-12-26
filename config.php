<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);

// PayPal Configuration
define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID']);
define('PAYPAL_CLIENT_SECRET', $_ENV['PAYPAL_CLIENT_SECRET']);
define('PAYPAL_MODE', $_ENV['PAYPAL_MODE']);
define('PAYPAL_CURRENCY', $_ENV['PAYPAL_CURRENCY']);

// Application URLs
define('BASE_URL', $_ENV['BASE_URL']);
define('PAYPAL_RETURN_URL', BASE_URL . '/payment_success.php');
define('PAYPAL_CANCEL_URL', BASE_URL . '/payment_cancel.php');

// SMTP Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_PORT', $_ENV['SMTP_PORT']);
define('SMTP_USER', $_ENV['SMTP_USER']);
define('SMTP_PASS', $_ENV['SMTP_PASS']);
define('SMTP_FROM', $_ENV['SMTP_FROM']);
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME']);

// Security Configuration
define('APP_SECRET', $_ENV['APP_SECRET']);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();
?>
