<?php
session_start();

// Check if the user is logged in as a hospital staff member
if (!isset($_SESSION['hospital_id'])) {
    header("Location: hospital_login.php"); // Redirect if not logged in
    exit();
}

// Include the database connection
include('../includes/db_connection.php');

if (isset($_POST['delete_donor'])) {
    // Get the donor ID from the form
    $donor_id = $_POST['donor_id'];

    // Validate the donor ID (ensure it's a valid integer)
    if (is_numeric($donor_id)) {
        // Prepare the delete query
        $delete_query = "DELETE FROM donors WHERE donor_id = ? AND hospital_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        
        // Bind the parameters and execute the query
        mysqli_stmt_bind_param($stmt, "ii", $donor_id, $_SESSION['hospital_id']);
        $result = mysqli_stmt_execute($stmt);

        // Check if the donor was successfully deleted
        if ($result) {
            // Redirect to the hospital view donors page
            header("Location: hospital_dashboard.php?delete=success");
        } else {
            // Redirect with an error message
            header("Location: hospital_dashboard.php?delete=error");
        }
        
        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        // Invalid donor ID
        header("Location: hospital_dashboard.php?delete=invalid");
    }
}

mysqli_close($conn);
?>
