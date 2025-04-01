<?php
session_start(); // Start the session

// Session timeout settings (Optional)
$session_timeout = 30 * 60; // 30 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
    session_unset();     // Unset all session variables
    session_destroy();   // Destroy the session
    header("Location: main_login.php?session_expired=1"); // Redirect after timeout
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

/**
 * Check if an admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Check if a hospital staff member is logged in
 */
function isHospitalLoggedIn() {
    return isset($_SESSION['hospital_id']);
}

/**
 * Check if a recipient is logged in
 */
function isRecipientLoggedIn() {
    return isset($_SESSION['recipient_id']);
}

/**
 * Redirect users if they are not logged in
 */
function requireLogin($role) {
    if ($role === 'admin' && !isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit();
    } elseif ($role === 'hospital' && !isHospitalLoggedIn()) {
        header("Location: hospital_login.php");
        exit();
    } elseif ($role === 'recipient' && !isRecipientLoggedIn()) {
        header("Location: recipient_login.php");
        exit();
    }
}

/**
 * Log out and destroy session
 */
function logout() {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: main_login.php");
    exit();
}
?>
