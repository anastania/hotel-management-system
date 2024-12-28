<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'hotel_reservation');

// PayPal configuration
define('PAYPAL_CLIENT_ID', 'Ab8qiuB2ZEE8FT3NqHwEZEeNs7K_BpW9Sx6PNsHZ4vkNSLOtkAg9xCYqDH_dHxWZJZl_9ZGTqWkOHHjE');
define('PAYPAL_SECRET', 'EClx-0RmJ8nVYRGGjF8bluPWeUoKlw9R7mzZBUFuTDgLOHYMxW7UPEFawiL9bW1UHWdj9oDq0ExJP_Hs');
define('PAYPAL_MODE', 'sandbox');
define('PAYPAL_CURRENCY', 'EUR');

// Base URL
define('BASE_URL', 'http://localhost/Gestion_reservation_hotel');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to ensure proper handling of special characters
    mysqli_set_charset($conn, "utf8mb4");
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
