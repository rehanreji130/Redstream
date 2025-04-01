<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in as a hospital staff member
if (!isset($_SESSION['hospital_id'])) {
    header("Location: hospital_login.php"); // Redirect if not logged in
    exit();
}

// Include the database connection
include('../includes/db_connection.php');

// Fetch donor details if donor_id is provided via GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $donor_id = intval($_GET['id']);
    
    $query = "SELECT donor_name, donor_blood_type, donor_phone FROM donors WHERE donor_id = ? AND hospital_id = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "ii", $donor_id, $_SESSION['hospital_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $donor_name, $donor_blood_type, $donor_phone);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    } else {
        die("Error fetching donor data: " . mysqli_error($conn));
    }
}

// Handle donor update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_donor'])) {
    // Validate and sanitize input
    $donor_id = isset($_POST['donor_id']) ? intval($_POST['donor_id']) : 0;
    $donor_name = isset($_POST['donor_name']) ? trim($_POST['donor_name']) : '';
    $donor_blood_type = isset($_POST['donor_blood_type']) ? trim($_POST['donor_blood_type']) : '';
    $donor_phone = isset($_POST['donor_phone']) ? trim($_POST['donor_phone']) : '';

    // Ensure required fields are not empty
    if ($donor_id > 0 && !empty($donor_name) && !empty($donor_blood_type) && !empty($donor_phone)) {
        // Prepare the update query
        $update_query = "UPDATE donors SET donor_name = ?, donor_blood_type = ?, donor_phone = ? WHERE donor_id = ? AND hospital_id = ?";
        
        if ($stmt = mysqli_prepare($conn, $update_query)) {
            // Bind the parameters and execute the query
            mysqli_stmt_bind_param($stmt, "sssii", $donor_name, $donor_blood_type, $donor_phone, $donor_id, $_SESSION['hospital_id']);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: hospital_dashboard.php?edit=success");
                exit();
            } else {
                error_log("MySQL Error: " . mysqli_stmt_error($stmt));
                header("Location: hospital_dashboard.php?edit=error");
                exit();
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("Statement Error: " . mysqli_error($conn));
            header("Location: hospital_dashboard.php?edit=error");
            exit();
        }
    } else {
        // Invalid input data
        header("Location: hospital_dashboard.php?edit=invalid");
        exit();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor</title>
    <link rel="stylesheet" href="">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Donor</h2>
        <form action="edit_donor.php" method="post">
            <input type="hidden" name="donor_id" value="<?php echo htmlspecialchars($donor_id); ?>">
            
            <div class="form-group">
                <label for="donor_name">Name:</label>
                <input type="text" id="donor_name" name="donor_name" value="<?php echo htmlspecialchars($donor_name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="donor_blood_type">Blood Type:</label>
                <input type="text" id="donor_blood_type" name="donor_blood_type" value="<?php echo htmlspecialchars($donor_blood_type); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="donor_phone">Phone:</label>
                <input type="text" id="donor_phone" name="donor_phone" value="<?php echo htmlspecialchars($donor_phone); ?>" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="edit_donor">Update Donor</button>
            </div>
        </form>
        <a href="hospital_dashboard.php" class="back-link">Back to Dashboard</a>
    </div>
</body>
</html>