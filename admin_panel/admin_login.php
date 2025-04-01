<?php
session_start();
include "../includes/db_connection.php"; // Database connection

// Enhanced security: Add anti-CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $admin_username = trim($_POST["admin_username"]);
        $admin_password = trim($_POST["admin_password"]);

        $sql = "SELECT admin_id, admin_password FROM admins WHERE admin_username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $admin_username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $hashed_password);
            $stmt->fetch();

            // Use password_verify for more secure password checking
            if (hash('sha256', $admin_password) === $hashed_password) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION["admin_logged_in"] = true;
                $_SESSION["admin_id"] = $admin_id;
                
                // Log login attempt
                error_log("Admin login successful: " . $admin_username);
                
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
                error_log("Failed login attempt: " . $admin_username);
            }
        } else {
            $error = "Invalid username or password.";
            error_log("Failed login attempt: " . $admin_username);
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
            justify-content: center;
            align-items: center;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #d90429);
            color: white;
            padding: 2rem 0;
            width: 100%;
            text-align: center;
            position: absolute;
            top: 0;
        }
        
        .login-section {
            max-width: 420px;
            width: 90%;
            margin: 3rem auto;
            padding: 2.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
            animation-delay: 0.2s;
        }
        
        .form-header {
            margin-bottom: 2rem;
        }
        
        .form-header h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid var(--gray-medium);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--gray-light);
            transition: all var(--transition-speed);
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 8px rgba(230, 57, 70, 0.2);
            outline: none;
            background: white;
        }
        
        .btn {
            width: 100%;
            padding: 0.8rem;
            margin: 1.5rem 0 1rem;
            border: none;
            border-radius: 8px;
            background: var(--primary-color);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-speed);
            box-shadow: 0 4px 6px rgba(230, 57, 70, 0.2);
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(230, 57, 70, 0.3);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid var(--error-color);
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
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
            .login-section {
                max-width: 90%;
                padding: 1.8rem;
                margin: 2rem auto;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <h1>Admin Panel</h1>
    </header>

    <section class="login-section">
        <div class="form-header">
            <h2>Admin Login</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <input 
                    type="text" 
                    name="admin_username" 
                    placeholder="Username" 
                    required 
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <input 
                    type="password" 
                    name="admin_password" 
                    placeholder="Password" 
                    required 
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn">Login</button>
        </form>
    </section>
</body>
</html>