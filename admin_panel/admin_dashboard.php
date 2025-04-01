<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: main_index.php"); // Redirect to homepage if not logged in as admin
    exit();
}

// Database connection
include "../includes/db_connection.php";

// Initialize variables with default values
$total_hospitals = 0;
$total_recipients = 0;
$total_donors = 0;

// Debug connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
} else {
    // Fetch statistics using direct queries for simplicity and troubleshooting
    try {
        // Check if tables exist first
        $table_check_query = "SHOW TABLES LIKE 'hospitals'";
        $table_result = $conn->query($table_check_query);
        $hospitals_table_exists = ($table_result && $table_result->num_rows > 0);
        
        if ($hospitals_table_exists) {
            $hospitals_result = $conn->query("SELECT COUNT(*) AS total FROM hospitals");
            if ($hospitals_result && $row = $hospitals_result->fetch_assoc()) {
                $total_hospitals = $row['total'];
            }
        } else {
            error_log("Hospitals table does not exist");
        }
        
        $table_check_query = "SHOW TABLES LIKE 'recipients'";
        $table_result = $conn->query($table_check_query);
        $recipients_table_exists = ($table_result && $table_result->num_rows > 0);
        
        if ($recipients_table_exists) {
            $recipients_result = $conn->query("SELECT COUNT(*) AS total FROM recipients");
            if ($recipients_result && $row = $recipients_result->fetch_assoc()) {
                $total_recipients = $row['total'];
            }
        } else {
            error_log("Recipients table does not exist");
        }
        
        $table_check_query = "SHOW TABLES LIKE 'donors'";
        $table_result = $conn->query($table_check_query);
        $donors_table_exists = ($table_result && $table_result->num_rows > 0);
        
        if ($donors_table_exists) {
            $donors_result = $conn->query("SELECT COUNT(*) AS total FROM donors");
            if ($donors_result && $row = $donors_result->fetch_assoc()) {
                $total_donors = $row['total'];
            }
        } else {
            error_log("Donors table does not exist");
        }
    } catch (Exception $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Blood Availability</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #e63946;
            --primary-dark: #b71c1c;
            --primary-light: #ffcdd2;
            --secondary-color: #f1faee;
            --dark-color: #1d3557;
            --light-color: #a8dadc;
            --accent-color: #457b9d;
            --gray-light: #f5f5f5;
            --gray-medium: #e0e0e0;
            --gray-dark: #757575;
            --success-color: #4caf50;
            --error-color: #f44336;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4efe9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #d90429);
            color: white;
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .header-container {
            text-align: center;
            padding: 1rem 0;
            position: relative;
            z-index: 2;
        }
        
        .header-container h1 {
            color: #ffffff;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 1s ease-out;
        }
        
        .header-container p {
            color: #ffffff;
            font-size: 1.2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out;
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
            transition: color var(--transition-speed);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .main-nav ul li a:hover {
            color: var(--primary-color);
        }
        
        .admin-dashboard {
            max-width: 1200px;
            width: 90%;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .dashboard-header {
            margin-bottom: 2rem;
        }
        
        .dashboard-header h2 {
            font-size: 1.8rem;
            color: var(--dark-color);
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: var(--secondary-color);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: transform var(--transition-speed);
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-color);
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-box h3 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .stat-box p {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-box .stat-link {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.5rem;
            transition: all var(--transition-speed);
        }
        
        .stat-box .stat-link:hover {
            color: var(--primary-color);
            transform: translateX(3px);
        }
        
        .debug-info {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #6c757d;
        }
        
        .debug-info h3 {
            margin-bottom: 0.5rem;
            color: #495057;
        }
        
        .debug-info pre {
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 0.9rem;
            padding: 0.5rem;
            background: #e9ecef;
            border-radius: 4px;
        }
        
        .main-footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: auto;
        }
        
        .footer-container p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .footer-container a {
            color: var(--light-color);
            text-decoration: none;
            transition: color var(--transition-speed);
        }
        
        .footer-container a:hover {
            color: white;
            text-decoration: underline;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .main-nav ul {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
            
            .header-container h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Welcome to the Admin Dashboard</h1>
            <p>Manage the platform and monitor activity</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_manage_hospitals.php"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
            <li><a href="admin_manage_recipients.php"><i class="fas fa-user-injured"></i> Manage Recipients</a></li>
            <li><a href="admin_manage_donors.php"><i class="fas fa-user-plus"></i> Manage Donors</a></li>
            <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <section class="admin-dashboard">
        <div class="dashboard-header">
            <h2>Admin Overview</h2>
        </div>

        <div class="dashboard-stats">
            <div class="stat-box">
                <h3><i class="fas fa-hospital"></i> Total Hospitals</h3>
                <p><?php echo htmlspecialchars($total_hospitals); ?></p>
                <a href="admin_manage_hospitals.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-box">
                <h3><i class="fas fa-user-injured"></i> Total Recipients</h3>
                <p><?php echo htmlspecialchars($total_recipients); ?></p>
                <a href="admin_manage_recipients.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="stat-box">
                <h3><i class="fas fa-user-plus"></i> Total Donors</h3>
                <p><?php echo htmlspecialchars($total_donors); ?></p>
                <a href="admin_manage_donors.php" class="stat-link">Manage <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        
        <?php if (isset($_GET['debug']) && $_GET['debug'] === 'true'): ?>
        <div class="debug-info">
            <h3>Database Debugging Information</h3>
            <pre>
Database connection status: <?php echo $conn ? 'Connected' : 'Failed'; ?>

Tables existence:
- Hospitals table: <?php echo $hospitals_table_exists ? 'Exists' : 'Does not exist'; ?>
- Recipients table: <?php echo $recipients_table_exists ? 'Exists' : 'Does not exist'; ?>
- Donors table: <?php echo $donors_table_exists ? 'Exists' : 'Does not exist'; ?>

Data counts:
- Total hospitals: <?php echo $total_hospitals; ?>
- Total recipients: <?php echo $total_recipients; ?>
- Total donors: <?php echo $total_donors; ?>
            </pre>
            <p>Add '?debug=true' to the URL to see this debugging information.</p>
        </div>
        <?php endif; ?>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability Website</p>
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a></p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to current nav link
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.main-nav a');
            navLinks.forEach(function(link) {
                if (link.getAttribute('href') === currentPath.substring(currentPath.lastIndexOf('/') + 1)) {
                    link.style.color = 'var(--primary-color)';
                }
            });
        });
    </script>
</body>
</html>