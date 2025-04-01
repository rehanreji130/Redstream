<?php
$servername = "localhost";
$username = "root"; // Update as per your DB credentials
$password = ""; // Update as per your DB credentials
$dbname = "blood_availability"; // Your DB name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
