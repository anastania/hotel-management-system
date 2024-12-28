<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    // Add payment_id column if it doesn't exist
    $sql = "SHOW COLUMNS FROM reservations LIKE 'payment_id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        $alter_sql = "ALTER TABLE reservations
                     ADD COLUMN payment_id VARCHAR(255) NULL,
                     ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending',
                     ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        
        if (mysqli_query($conn, $alter_sql)) {
            echo "Table 'reservations' updated successfully with payment columns.<br>";
        } else {
            throw new Exception("Error updating table: " . mysqli_error($conn));
        }
    } else {
        echo "Payment columns already exist in the reservations table.<br>";
    }

    echo "Database update completed successfully.";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
