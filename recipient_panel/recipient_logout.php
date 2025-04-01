<?php
session_start();
session_destroy(); // Destroy all session data

// Redirect to homepage after logout
header("Location: recipient_login.php");
exit();
?>
