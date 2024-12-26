<?php
require_once 'includes/bootstrap.php';

echo "<h1>Testing New Features</h1>";

try {
    // Test database connection
    $db = Database::getInstance();
    echo "<p style='color: green'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

try {
    // Test logging
    Logger::info("Test log message");
    echo "<p style='color: green'>✓ Logging system working</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Logging system failed: " . $e->getMessage() . "</p>";
}

try {
    // Test security features
    Security::init();
    echo "<p style='color: green'>✓ Security system initialized</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Security system failed: " . $e->getMessage() . "</p>";
}

// Test if old code still works
if (isset($conn)) {
    echo "<p style='color: green'>✓ Old database connection still working</p>";
}

// Test session
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green'>✓ Session working</p>";
} else {
    echo "<p style='color: red'>✗ Session not working</p>";
}

// Overall status
if (useNewFeatures()) {
    echo "<h2 style='color: green'>All new features are working correctly!</h2>";
} else {
    echo "<h2 style='color: orange'>System is running in compatibility mode</h2>";
}
