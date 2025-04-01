<?php
session_start();

// Include the database connection
include('../includes/db_connection.php'); // Adjust the path if necessary

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query to check if the recipient exists with the given email
    $query = "SELECT * FROM recipients WHERE recipient_email = '$email'";
    $result = mysqli_query($conn, $query);

    // Check for query execution error
    if (!$result) {
        die('Error executing query: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        $recipient = mysqli_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $recipient['recipient_password'])) {
            // Start the session and store recipient information
            $_SESSION['recipient_id'] = $recipient['recipient_id'];
            $_SESSION['recipient_email'] = $recipient['email'];
            header("Location: recipient_search_blood.php"); // Redirect to recipient dashboard
            exit();
        } else {
            $message = "Incorrect password. Please try again.";
            $message_type = "error";
        }
    } else {
        $message = "No recipient found with that email address.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Login | Blood Availability System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        
        .blood-drop {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            z-index: 1;
        }
        
        .logo {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            z-index: 3;
        }
        
        .logo-icon {
            font-size: 1.8rem;
            margin-right: 0.5rem;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        /* Login Form Styling */
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
            flex: 1;
        }
        
        .form-header {
            margin-bottom: 2rem;
        }
        
        .form-header h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .form-header p {
            color: var(--gray-dark);
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
            position: relative;
        }
        
        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            transition: all var(--transition-speed);
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
        
        .form-group .input-icon {
            position: absolute;
            right: 1rem;
            top: 2.7rem;
            color: var(--gray-dark);
            cursor: pointer;
        }
        
        /* Button Styling */
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
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(230, 57, 70, 0.3);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--accent-color);
            margin-top: 0.5rem;
        }
        
        .btn-secondary:hover {
            background: #3d6985;
            box-shadow: 0 6px 12px rgba(69, 123, 157, 0.3);
        }
        
        .btn .btn-wave {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: btnWave 0.8s ease-out;
            pointer-events: none;
        }
        
        /* Login Options */
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--gray-dark);
            font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-medium);
        }
        
        .divider::before {
            margin-right: 0.8rem;
        }
        
        .divider::after {
            margin-left: 0.8rem;
        }
        
        .alt-login {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alt-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gray-light);
            border: 1px solid var(--gray-medium);
            transition: all var(--transition-speed);
            cursor: pointer;
        }
        
        .alt-login-btn:hover {
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        /* Message Styling */
        .message {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s ease-out;
        }
        
        .message i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid var(--success-color);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid var(--error-color);
        }
        
        /* Links */
        .form-links {
            text-align: center;
            margin: 1.5rem 0;
            font-size: 0.9rem;
            color: var(--gray-dark);
        }
        
        .form-links a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: color var(--transition-speed);
        }
        
        .form-links a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        /* Footer */
        .main-footer {
            background-color: var(--dark-color);
            color: white;
            padding: 1.5rem 0;
            text-align: center;
            margin-top: auto;
        }
        
        .footer-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-container p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 0.5rem 0;
        }
        
        .footer-links a {
            color: var(--light-color);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color var(--transition-speed);
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        /* Animations */
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
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        @keyframes btnWave {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .login-section {
                max-width: 90%;
                padding: 1.8rem;
                margin: 2rem auto;
            }
            
            .header-container h1 {
                font-size: 2rem;
            }
            
            .logo {
                position: static;
                justify-content: center;
                margin-bottom: 1rem;
            }
            
            .form-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <a href="../main_index.php" class="logo">
            <span class="logo-text">Redstream</span>
        </a>
        
        <!-- Create animated blood drops in the header -->
        <div id="blood-drops-container"></div>
        
        <div class="header-container">
            <h1>Recipient Login</h1>
            <p>Log in to search for available blood at your nearest hospital</p>
        </div>
    </header>

    <section class="login-section">
        <div class="form-header">
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your account</p>
        </div>

        <div id="message-container">
            <?php if (isset($message)) { ?>
                <div class="message <?php echo isset($message_type) ? $message_type . '-message' : 'error-message'; ?>">
                    <i class="fas <?php echo isset($message_type) && $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php } ?>
        </div>

        <form action="recipient_login.php" method="POST" id="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
                <i class="fas fa-envelope input-icon"></i>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
                <i class="fas fa-eye-slash input-icon" id="password-toggle"></i>
            </div>
            
            <div class="form-links">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn" id="login-btn">
                <span>Login</span>
            </button>
        </form>

        <div class="divider">Or continue with</div>
        
       
        </div>

        <div class="form-links">
            Don't have an account? <a href="recipient_register.php">Register now</a>
        </div>
        
        <button onclick="window.location.href='../main_index.php';" class="btn btn-secondary">
            <i class="fas fa-home"></i> Back to Home
        </button>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Redstream Blood Availability System</p>
            <div class="footer-links">
                <a href="../about.php">About</a>
                <a href="../privacy.php">Privacy Policy</a>
                <a href="../contact.php">Contact</a>
                <a href="../faq.php">FAQ</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create animated blood drops
            const bloodDropsContainer = document.getElementById('blood-drops-container');
            for (let i = 0; i < 10; i++) {
                createBloodDrop(bloodDropsContainer);
            }
            
            // Toggle password visibility
            const passwordToggle = document.getElementById('password-toggle');
            const passwordField = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    passwordToggle.classList.remove('fa-eye-slash');
                    passwordToggle.classList.add('fa-eye');
                } else {
                    passwordField.type = 'password';
                    passwordToggle.classList.remove('fa-eye');
                    passwordToggle.classList.add('fa-eye-slash');
                }
            });
            
            // Form validation
            const loginForm = document.getElementById('login-form');
            const emailField = document.getElementById('email');
            const messageContainer = document.getElementById('message-container');
            
            loginForm.addEventListener('submit', function(event) {
                let isValid = true;
                const email = emailField.value.trim();
                
                // Very basic email validation
                if (!email.includes('@') || !email.includes('.')) {
                    showMessage('Please enter a valid email address', 'error');
                    isValid = false;
                }
                
                if (!isValid) {
                    event.preventDefault();
                } else {
                    // Add button animation when form is submitting
                    const loginBtn = document.getElementById('login-btn');
                    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                    loginBtn.disabled = true;
                    
                    // The form will submit normally if validation passes
                }
            });
            
            // Button wave effect
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Only proceed if the click was directly on the button or its child elements
                    if (e.currentTarget === this) {
                        const x = e.clientX - e.target.getBoundingClientRect().left;
                        const y = e.clientY - e.target.getBoundingClientRect().top;
                        
                        const wave = document.createElement('span');
                        wave.classList.add('btn-wave');
                        wave.style.left = `${x}px`;
                        wave.style.top = `${y}px`;
                        
                        this.appendChild(wave);
                        
                        setTimeout(() => {
                            wave.remove();
                        }, 800);
                    }
                });
            });
            
            // Social login placeholders
            const socialButtons = document.querySelectorAll('.alt-login-btn');
            socialButtons.forEach(button => {
                button.addEventListener('click', function() {
                    showMessage('Social login integration coming soon!', 'success');
                });
            });
        });
        
        // Create animated blood drops
        function createBloodDrop(container) {
            const drop = document.createElement('div');
            drop.classList.add('blood-drop');
            
            // Random size between 20px and 100px
            const size = Math.random() * 80 + 20;
            drop.style.width = `${size}px`;
            drop.style.height = `${size}px`;
            
            // Random position
            drop.style.left = `${Math.random() * 100}%`;
            drop.style.top = `${Math.random() * 100}%`;
            
            // Random opacity
            drop.style.opacity = Math.random() * 0.5 + 0.1;
            
            // Random animation duration
            const duration = Math.random() * 10 + 15;
            drop.style.animation = `fadeInDown ${duration}s infinite`;
            
            container.appendChild(drop);
        }
        
        // Show message function
        function showMessage(text, type) {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `
                <div class="message ${type}-message">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    ${text}
                </div>
            `;
            
            // Automatically remove success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    const message = document.querySelector('.success-message');
                    if (message) {
                        message.style.animation = 'fadeOut 0.5s ease-out forwards';
                        setTimeout(() => {
                            messageContainer.innerHTML = '';
                        }, 500);
                    }
                }, 5000);
            }
        }
    </script>
</body>
</html>