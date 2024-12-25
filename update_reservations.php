<?php
require_once "includes/config.php";

// Get current date
$current_date = date('Y-m-d');

// Update completed reservations
$update_completed = "UPDATE reservations 
                    SET status = 'completed' 
                    WHERE date_depart < ? 
                    AND status = 'confirmed'";
$stmt_completed = mysqli_prepare($conn, $update_completed);
mysqli_stmt_bind_param($stmt_completed, "s", $current_date);
mysqli_stmt_execute($stmt_completed);

// Update expired pending reservations (pending for more than 24 hours)
$update_expired = "UPDATE reservations 
                  SET status = 'expired' 
                  WHERE status = 'pending' 
                  AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
mysqli_query($conn, $update_expired);

// Log the update
$log_message = date('Y-m-d H:i:s') . " - Reservation statuses updated\n";
file_put_contents("logs/reservation_updates.log", $log_message, FILE_APPEND);

echo "Reservation statuses updated successfully.";
?>
