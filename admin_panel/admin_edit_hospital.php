<?php
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: main_index.php");
    exit();
}

// Include database connection
include('../includes/db_connection.php');

// Initialize variables
$hospital_id = $hospital_name = $hospital_address = $hospital_phone = '';
$errors = [];

// Get hospital_id from GET or POST
$hospital_id = isset($_GET['hospital_id']) ? filter_input(INPUT_GET, 'hospital_id', FILTER_VALIDATE_INT) : 
               (isset($_POST['hospital_id']) ? filter_input(INPUT_POST, 'hospital_id', FILTER_VALIDATE_INT) : null);

// Validate hospital_id
if (!$hospital_id) {
    $_SESSION['error_message'] = "Invalid hospital ID.";
    header("Location: admin_manage_hospitals.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $hospital_name = filter_input(INPUT_POST, 'hospital_name', FILTER_SANITIZE_STRING);
    $hospital_address = filter_input(INPUT_POST, 'hospital_address', FILTER_SANITIZE_STRING);
    $hospital_phone = filter_input(INPUT_POST, 'hospital_phone', FILTER_SANITIZE_STRING);
    
    // Validation
    if (empty($hospital_name)) $errors[] = "Hospital name is required.";
    if (empty($hospital_address)) $errors[] = "Hospital address is required.";
    if (empty($hospital_phone)) $errors[] = "Hospital phone is required.";
    elseif (!preg_match('/^[0-9\+\-\(\) ]{10,15}$/', $hospital_phone)) $errors[] = "Enter valid phone (10-15 digits).";
    
    // Update hospital if no errors
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE hospitals SET hospital_name = ?, hospital_address = ?, hospital_phone = ? WHERE hospital_id = ?");
        $update_stmt->bind_param("sssi", $hospital_name, $hospital_address, $hospital_phone, $hospital_id);
        
        try {
            if ($update_stmt->execute()) {
                $_SESSION['success_message'] = "Hospital updated successfully.";
                header("Location: admin_manage_hospitals.php");
                exit();
            } else {
                $errors[] = "Update error: " . $conn->error;
            }
        } catch (Exception $e) {
            $errors[] = "Update error: " . $e->getMessage();
        }
    }
} else {
    // Fetch hospital data
    $stmt = $conn->prepare("SELECT hospital_name, hospital_address, hospital_phone FROM hospitals WHERE hospital_id = ?");
    $stmt->bind_param("i", $hospital_id);
    
    try {
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Hospital not found.";
            header("Location: admin_manage_hospitals.php");
            exit();
        }
        
        $hospital_data = $result->fetch_assoc();
        $hospital_name = $hospital_data['hospital_name'];
        $hospital_address = $hospital_data['hospital_address'];
        $hospital_phone = $hospital_data['hospital_phone'];
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error retrieving hospital data: " . $e->getMessage();
        header("Location: admin_manage_hospitals.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hospital | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #e63946;
            --primary-dark: #b71c1c;
            --secondary-color: #f1faee;
            --dark-color: #1d3557;
            --accent-color: #457b9d;
            --gray-medium: #e0e0e0;
            --error-color: #f44336;
            --error-light: #ffebee;
            --transition-speed: 0.3s;
            --border-radius: 8px;
            --box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        * {margin: 0; padding: 0; box-sizing: border-box;}
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #334155;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4efe9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #d90429);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        .header-container h1 {
            color: #ffffff;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .main-nav {
            background-color: var(--dark-color);
            padding: 0.75rem 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .main-nav ul {
            display: flex;
            justify-content: center;
            list-style: none;
            gap: 2rem;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .main-nav a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: all var(--transition-speed);
        }
        .form-container {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        .form-container h2 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 0.75rem;
        }
        .form-container h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 10px;
        }
        .form-group {margin-bottom: 1.5rem;}
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid var(--gray-medium);
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition-speed);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            border: none;
            font-family: 'Poppins', sans-serif;
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-secondary {
            background-color: var(--accent-color);
            color: white;
        }
        .errors-container {
            background-color: var(--error-light);
            border-left: 4px solid var(--error-color);
            color: var(--error-color);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
        }
        .main-footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: auto;
        }
        @media (max-width: 768px) {
            .form-container {padding: 1.5rem;}
            .btn {width: 100%;}
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Edit Hospital</h1>
            <p>Update hospital information</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_manage_hospitals.php"><i class="fas fa-arrow-left"></i> Back to Hospital List</a></li>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        </ul>
    </nav>

    <section class="form-container">
        <h2>Edit Hospital Details</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="errors-container">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="admin_edit_hospital.php">
            <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($hospital_id); ?>">
            
            <div class="form-group">
                <label for="hospital_name">Hospital Name</label>
                <input type="text" id="hospital_name" name="hospital_name" class="form-control" 
                       value="<?php echo htmlspecialchars($hospital_name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="hospital_address">Hospital Address</label>
                <textarea id="hospital_address" name="hospital_address" class="form-control" 
                          rows="3" required><?php echo htmlspecialchars($hospital_address); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="hospital_phone">Contact Number</label>
                <input type="text" id="hospital_phone" name="hospital_phone" class="form-control" 
                       value="<?php echo htmlspecialchars($hospital_phone); ?>" required>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Hospital
                </button>
                <a href="admin_manage_hospitals.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
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