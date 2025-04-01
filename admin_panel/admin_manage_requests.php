<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: main_index.php"); // Redirect to homepage if not logged in as admin
    exit();
}

// Include the database connection
include('db_connection.php'); // Assuming you have this file to connect to the database

// Query to get all blood requests
$query = "SELECT * FROM blood_requests"; // Adjust this query to your actual blood_requests table
$result = mysqli_query($conn, $query);

// Handle request approval/rejection
if (isset($_GET['approve'])) {
    $request_id = $_GET['approve'];
    $update_query = "UPDATE blood_requests SET status = 'Approved' WHERE request_id = $request_id";
    mysqli_query($conn, $update_query);
    header("Location: admin_manage_requests.php"); // Refresh the page after approval
    exit();
}

if (isset($_GET['reject'])) {
    $request_id = $_GET['reject'];
    $update_query = "UPDATE blood_requests SET status = 'Rejected' WHERE request_id = $request_id";
    mysqli_query($conn, $update_query);
    header("Location: admin_manage_requests.php"); // Refresh the page after rejection
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Requests | Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/main_styles.css"> <!-- Link to Main Stylesheet -->
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Manage Blood Requests</h1>
            <p>View, approve, or reject blood requests from recipients</p>
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

    <section class="manage-requests-section">
        <h2>Blood Request List</h2>

        <!-- Blood Requests Table -->
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Recipient Name</th>
                    <th>Blood Type</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['request_id']; ?></td>
                        <td><?php echo $row['recipient_name']; ?></td>
                        <td><?php echo $row['blood_type']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <?php if ($row['status'] == 'Pending') { ?>
                                <a href="admin_manage_requests.php?approve=<?php echo $row['request_id']; ?>" onclick="return confirm('Are you sure you want to approve this request?');">Approve</a> | 
                                <a href="admin_manage_requests.php?reject=<?php echo $row['request_id']; ?>" onclick="return confirm('Are you sure you want to reject this request?');">Reject</a>
                            <?php } else { ?>
                                <span>Completed</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability Website</p>
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a></p>
        </div>
    </footer>
</body>
</html>
