<?php
session_start();

// Destroy the session and clear all session variables
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the hospital login page
header("Location: hospital_login.php"); // Redirect to the hospital login page
exit();
?>
