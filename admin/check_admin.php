<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    // Store the requested URL for redirect after login
    $_SESSION["redirect_url"] = $_SERVER["REQUEST_URI"];
    header("location: ../login.php");
    exit;
}
?>
