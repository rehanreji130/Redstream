<?php 
session_start();
include('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM hospitals WHERE hospital_email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $hospital = mysqli_fetch_assoc($result);
        if (password_verify($password, $hospital['hospital_password'])) {
            $_SESSION['hospital_id'] = $hospital['hospital_id'];
            $_SESSION['hospital_email'] = $hospital['hospital_email'];
            header("Location: hospital_dashboard.php");
            exit();
        } else {
            $message = "Incorrect password. Please try again.";
            $message_type = "error";
        }
    } else {
        $message = "No hospital found with that email address.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Login | Redstream Blood Availability System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main_styles.css">
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
        
        /* Password Strength */
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: var(--gray-medium);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.5s ease, background-color 0.5s ease;
        }
        
        .password-hint {
            font-size: 0.8rem;
            color: var(--gray-dark);
            margin-top: 0.3rem;
            transition: all var(--transition-speed);
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
              <i class="fas fa-tint logo-icon"></i>
            <div class="logo-text">Redstream</div>
        </a>
        <div id="blood-drops-container"></div>
        <div class="header-container">
            <h1>Hospital Login</h1>
            <p>Access your dashboard and manage blood inventory</p>
        </div>
        <!-- Blood drop decorations will be added by JS -->
    </header>

    <section class="login-section">
        <div class="form-header">
            <h2>Hospital Portal</h2>
            <p>Enter your credentials to access your account</p>
        </div>
        
        <?php if (isset($message)) { ?>
            <div class="message <?php echo isset($message_type) ? $message_type . '-message' : 'error-message'; ?>">
                <i class="message-icon">
                    <?php echo (isset($message_type) && $message_type == 'success') ? '✓' : '⚠'; ?>
                </i>
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form action="hospital_login.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your hospital email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
                <span class="input-icon" id="togglePassword">👁️</span>
            </div>
            <div class="form-links" style="text-align: right;">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn" id="loginBtn">Login</button>
        </form>

        <div class="divider">OR</div>
        

        <div class="form-links">
            Don't have an account? <a href="hospital_register.php">Register here</a>
        </div>
        
        <button onclick="window.location.href='../main_index.php';" class="btn btn-secondary">
            Back to Home
        </button>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-links">
                <a href="../about.php">About</a>
                <a href="../contact.php">Contact</a>
                <a href="../privacy-policy.php">Privacy Policy</a>
                <a href="../terms-of-service.php">Terms of Service</a>
            </div>
            <p>&copy; 2025 Redstream Blood Availability System</p>
        </div>
    </footer>

    <script>
        // Add blood drop decorations to header
        function createBloodDrops() {
            const header = document.querySelector('.main-header');
            for (let i = 0; i < 8; i++) {
                const drop = document.createElement('div');
                drop.classList.add('blood-drop');
                
                // Random size between 20px and 80px
                const size = Math.random() * 60 + 20;
                drop.style.width = `${size}px`;
                drop.style.height = `${size}px`;
                
                // Random position
                drop.style.left = `${Math.random() * 100}%`;
                drop.style.top = `${Math.random() * 100}%`;
                
                // Random opacity
                drop.style.opacity = Math.random() * 0.4 + 0.1;
                
                header.appendChild(drop);
            }
        }
        
        // Toggle password visibility
        function setupPasswordToggle() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
                });
            }
        }
        
        // Add button wave effect
        function setupButtonEffects() {
            const buttons = document.querySelectorAll('.btn');
            
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - this.getBoundingClientRect().left;
                    const y = e.clientY - this.getBoundingClientRect().top;
                    
                    const wave = document.createElement('span');
                    wave.classList.add('btn-wave');
                    wave.style.left = `${x}px`;
                    wave.style.top = `${y}px`;
                    
                    this.appendChild(wave);
                    
                    setTimeout(() => {
                        wave.remove();
                    }, 800);
                });
            });
        }
        
        // Form validation
        function setupFormValidation() {
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Basic email validation
                    if (emailInput && !isValidEmail(emailInput.value)) {
                        e.preventDefault();
                        showInputError(emailInput, 'Please enter a valid email address');
                        isValid = false;
                    }
                    
                    // Password not empty check
                    if (passwordInput && passwordInput.value.trim() === '') {
                        e.preventDefault();
                        showInputError(passwordInput, 'Password cannot be empty');
                        isValid = false;
                    }
                    
                    // If all valid, show loading state
                    if (isValid) {
                        const submitBtn = document.getElementById('loginBtn');
                        if (submitBtn) {
                            submitBtn.textContent = 'Logging in...';
                            submitBtn.disabled = true;
                        }
                    }
                });
            }
            
            // Input focus effects
            const formInputs = document.querySelectorAll('.form-group input');
            formInputs.forEach(input => {
                // Focus styling
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                    
                    // Basic validation on blur
                    if (this.type === 'email' && this.value !== '' && !isValidEmail(this.value)) {
                        showInputError(this, 'Please enter a valid email address');
                    } else {
                        this.style.borderColor = '';
                        const errorMsg = this.parentElement.querySelector('.input-error');
                        if (errorMsg) errorMsg.remove();
                    }
                });
            });
        }
        
        // Helper to validate email format
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Helper to show input error
        function showInputError(inputElement, message) {
            inputElement.style.borderColor = 'var(--error-color)';
            
            // Remove any existing error message
            const existingError = inputElement.parentElement.querySelector('.input-error');
            if (existingError) existingError.remove();
            
            // Add new error message
            const errorMessage = document.createElement('div');
            errorMessage.classList.add('input-error');
            errorMessage.style.color = 'var(--error-color)';
            errorMessage.style.fontSize = '0.8rem';
            errorMessage.style.marginTop = '0.3rem';
            errorMessage.textContent = message;
            
            inputElement.parentElement.appendChild(errorMessage);
        }
        
        // Auto-dismiss messages after a few seconds
        function setupMessageDismissal() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.height = '0';
                    message.style.margin = '0';
                    message.style.padding = '0';
                    message.style.transition = 'all 0.5s ease-out';
                    
                    setTimeout(() => {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        }
        
        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            createBloodDrops();
            setupPasswordToggle();
            setupButtonEffects();
            setupFormValidation();
            setupMessageDismissal();
        });
        
    </script>
</body>
</html>