<?php
session_start();

// Destroy the session and clear all session variables
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the login page or homepage
header("Location: admin_login.php"); // Redirect to the admin login page
exit();
?>
