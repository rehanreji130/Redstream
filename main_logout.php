<?php
session_start();

// Check if the user is an admin, hospital staff, or recipient and destroy their session accordingly
if (isset($_SESSION['admin_id'])) {
    // Admin logout
    unset($_SESSION['admin_id']);
    session_destroy();
    header("Location: main_index.php"); // Redirect to homepage after admin logout
    exit();
}

if (isset($_SESSION['hospital_id'])) {
    // Hospital staff logout
    unset($_SESSION['hospital_id']);
    session_destroy();
    header("Location: hospital_panel/hospital_login.php"); // Redirect to hospital login page after logout
    exit();
}

if (isset($_SESSION['recipient_id'])) {
    // Recipient logout
    unset($_SESSION['recipient_id']);
    session_destroy();
    header("Location: recipient_panel/recipient_login.php"); // Redirect to recipient login page after logout
    exit();
}

// If no session exists, just redirect to homepage
header("Location: main_index.php");
exit();
