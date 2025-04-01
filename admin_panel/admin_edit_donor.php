<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: main_index.php");
    exit();
}

// Include database connection
include('../includes/db_connection.php');

// Initialize variables
$donor_id = '';
$donor_name = '';
$donor_email = '';
$donor_blood_type = '';
$donor_phone = '';
$message = '';
$message_type = '';

// Check if donor ID is provided
if (isset($_GET['donor_id'])) {
    $donor_id = $_GET['donor_id'];
    
    // Fetch donor information
    $query = "SELECT * FROM donors WHERE donor_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $donor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $donor = mysqli_fetch_assoc($result);
        $donor_name = $donor['donor_name'];
        $donor_email = $donor['donor_email'];
        $donor_blood_type = $donor['donor_blood_type'];
        $donor_phone = isset($donor['donor_phone']) ? $donor['donor_phone'] : '';
    } else {
        $message = "Donor not found.";
        $message_type = "error";
    }
} else {
    header("Location: admin_manage_donors.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $donor_name = htmlspecialchars(trim($_POST['donor_name']));
    $donor_email = htmlspecialchars(trim($_POST['donor_email']));
    $donor_blood_type = htmlspecialchars(trim($_POST['donor_blood_type']));
    $donor_phone = htmlspecialchars(trim($_POST['donor_phone']));
    
    // Simple validation
    $errors = [];
    if (empty($donor_name)) $errors[] = "Name is required";
    if (empty($donor_email)) $errors[] = "Email is required";
    if (empty($donor_blood_type)) $errors[] = "Blood type is required";
    
    // If no errors, update the donor
    if (empty($errors)) {
        $update_query = "UPDATE donors SET 
                        donor_name = ?, 
                        donor_email = ?, 
                        donor_blood_type = ?, 
                        donor_phone = ? 
                        WHERE donor_id = ?";
                        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssi", $donor_name, $donor_email, $donor_blood_type, $donor_phone, $donor_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Donor information updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating donor information: " . mysqli_error($conn);
            $message_type = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e63946;
            --dark-color: #1d3557;
            --accent-color: #457b9d;
            --gray-medium: #e0e0e0;
            --success-color: #2e7d32;
            --success-bg: #e8f5e9;
            --error-color: #c62828;
            --error-bg: #ffebee;
            --font-primary: 'Poppins', sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-primary);
            line-height: 1.6;
            background-color: #f9f9f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #d90429);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        
        .header-container h1 {
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        
        .main-nav {
            background: var(--dark-color);
            padding: 1rem 0;
        }
        
        .main-nav ul {
            display: flex;
            justify-content: center;
            list-style: none;
            gap: 2rem;
        }
        
        .main-nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .edit-donor-section {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .edit-donor-section h2 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-medium);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background: var(--accent-color);
            color: white;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }
        
        .success-message {
            background: var(--success-bg);
            color: var(--success-color);
            border-left: 5px solid var(--success-color);
        }
        
        .error-message {
            background: var(--error-bg);
            color: var(--error-color);
            border-left: 5px solid var(--error-color);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .back-btn::before {
            content: '←';
        }
        
        .main-footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Edit Donor</h1>
            <p>Update donor information</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_manage_donors.php">Manage Donors</a></li>
            <li><a href="main_logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="edit-donor-section">
        <a href="admin_manage_donors.php" class="back-btn">Back to Donors List</a>
        <h2>Edit Donor Information</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="donor-form">
            <div class="form-group">
                <label for="donor_name">Full Name</label>
                <input type="text" id="donor_name" name="donor_name" class="form-control" value="<?php echo $donor_name; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="donor_email">Email</label>
                <input type="email" id="donor_email" name="donor_email" class="form-control" value="<?php echo $donor_email; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="donor_blood_type">Blood Type</label>
                <select id="donor_blood_type" name="donor_blood_type" class="form-control" required>
                    <option value="">Select Blood Type</option>
                    <option value="A+" <?php if ($donor_blood_type === 'A+') echo 'selected'; ?>>A+</option>
                    <option value="A-" <?php if ($donor_blood_type === 'A-') echo 'selected'; ?>>A-</option>
                    <option value="B+" <?php if ($donor_blood_type === 'B+') echo 'selected'; ?>>B+</option>
                    <option value="B-" <?php if ($donor_blood_type === 'B-') echo 'selected'; ?>>B-</option>
                    <option value="AB+" <?php if ($donor_blood_type === 'AB+') echo 'selected'; ?>>AB+</option>
                    <option value="AB-" <?php if ($donor_blood_type === 'AB-') echo 'selected'; ?>>AB-</option>
                    <option value="O+" <?php if ($donor_blood_type === 'O+') echo 'selected'; ?>>O+</option>
                    <option value="O-" <?php if ($donor_blood_type === 'O-') echo 'selected'; ?>>O-</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="donor_phone">Phone Number</label>
                <input type="text" id="donor_phone" name="donor_phone" class="form-control" value="<?php echo $donor_phone; ?>">
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Update Donor</button>
                <a href="admin_manage_donors.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability Website</p>
        </div>
    </footer>
</body>
</html>