<?php
include('db_connection.php'); // Ensure database connection is available

/**
 * Sanitize user input to prevent SQL injection & XSS
 */
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

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
 * Send notification to a donor about a blood request
 */
function sendNotification($donor_id, $message) {
    global $conn;
    $query = "INSERT INTO notifications (donor_id, message, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $donor_id, $message);
    return $stmt->execute();
}

/**
 * Calculate distance between two geographical points (Haversine formula)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c; // Distance in km
}

/**
 * Get a user's full name based on their ID and role
 */
function getUserName($user_id, $role) {
    global $conn;
    $table = $role === 'hospital' ? 'hospitals' : ($role === 'recipient' ? 'recipients' : 'admin');
    $column = $role === 'admin' ? 'username' : 'name';
    
    $query = "SELECT $column FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    return $name ? $name : "Unknown";
}
?>
