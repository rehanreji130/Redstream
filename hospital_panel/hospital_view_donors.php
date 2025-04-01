<?php
session_start();

// Include the database connection
include('../includes/db_connection.php'); // Make sure this file contains your database connection details

// Redirect to login page if not logged in
if (!isset($_SESSION['hospital_id'])) {
    header("Location: hospital_login.php");
    exit();
}

$hospital_id = $_SESSION['hospital_id']; // Get hospital ID from session

// Fetch the list of donors associated with this hospital
$query = "SELECT * FROM donors WHERE hospital_id = '$hospital_id' ORDER BY last_donation_date DESC";
$result = mysqli_query($conn, $query);

// Check if there are any donors in the database
$donors = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Donors | Hospital Dashboard</title>
    <link rel="stylesheet" href="../assets/css/main_styles.css"> <!-- Link to Main Stylesheet -->
    
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Donor List</h1>
            <p>Here is the list of blood donors registered in your hospital</p>
        </div>
    </header>

    <section class="donors-list-section">
        <h2>Registered Donors</h2>

        <?php if (count($donors) > 0) { ?>
            <table class="donors-table">
                <thead>
                    <tr>
                        <th>Donor Name</th>
                        <th>Blood Type</th>
                        <th>Contact Info</th>
                        <th>Donation Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donors as $donor) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                            <td><?php echo htmlspecialchars($donor['donor_blood_type']); ?></td>
                            <td><?php echo htmlspecialchars($donor['donor_phone']); ?></td>
                            <td><?php echo htmlspecialchars($donor['last_donation_date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No donors found in your hospital's records.</p>
        <?php } ?>

        <p><a href="hospital_dashboard.php">Back to Dashboard</a></p>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability System</p>
        </div>
    </footer>
</body>
</html>
