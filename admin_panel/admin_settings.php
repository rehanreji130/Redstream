<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: main_index.php"); // Redirect to homepage if not logged in as admin
    exit();
}

// Include the database connection
include('../includes/db_connection.php'); // Assuming you have this file to connect to the database

$admin_id = $_SESSION['admin_id'];

// Fetch the admin details from the database
$query = "SELECT * FROM admins WHERE admin_id = $admin_id"; // Adjust this query to your actual admins table
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Handle form submission for updating details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = mysqli_real_escape_string($conn, $_POST['password']);
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT); // Hash the password for security

    // Update the email and password in the database
    $update_query = "UPDATE admins SET email = '$new_email', password = '$new_password_hash' WHERE admin_id = $admin_id";
    if (mysqli_query($conn, $update_query)) {
        $message = "Your details have been updated successfully!";
    } else {
        $message = "Error updating details. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/main_styles.css"> <!-- Link to Main Stylesheet -->
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Admin Settings</h1>
            <p>Update your account settings</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_manage_users.php">Manage Users</a></li>
            <li><a href="admin_manage_hospitals.php">Manage Hospitals</a></li>
            <li><a href="admin_manage_donors.php">Manage Donors</a></li>
            <li><a href="main_logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="settings-section">
        <h2>Update Your Details</h2>

        <?php if (isset($message)) { ?>
            <p class="message"><?php echo $message; ?></p>
        <?php } ?>

        <form action="admin_settings.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $admin['email']; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter new password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Update</button>
            </div>
        </form>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability Website</p>
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a></p>
        </div>
    </footer>
</body>
</html>
