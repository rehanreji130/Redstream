<?php
session_start();

// Include the database connection
include('../includes/db_connection.php');

// Handle hospital registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security

    // Check if email already exists
    $check_query = "SELECT * FROM hospitals WHERE hospital_email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "This email is already registered. Please use another email.";
        $message_type = "error";
    } else {
        // Insert the new hospital into the database
        $query = "INSERT INTO hospitals (hospital_name, hospital_email, hospital_phone, hospital_address, hospital_latitude, hospital_longitude, hospital_password) 
                  VALUES ('$name', '$email', '$phone', '$address', '$latitude', '$longitude', '$password_hash')";
        
        if (mysqli_query($conn, $query)) {
            $message = "Registration successful! You can now <a href='hospital_login.php'>log in</a>.";
            $message_type = "success";
        } else {
            $message = "Error during registration. Please try again.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Registration | Blood Availability System</title>
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
    text-align: center;
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

/* Registration Form Styling */
.register-section {
    max-width: 800px;
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

.register-section h2 {
    color: var(--dark-color);
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
}

.message {
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    animation: fadeIn 0.5s ease-out;
    text-align: left;
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
    padding-left: 2.5rem;
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

.form-group i {
    position: absolute;
    left: 1rem;
    top: 2.7rem;
    color: var(--gray-dark);
}

.form-group input.valid {
    border-color: var(--success-color);
    background-color: rgba(76, 175, 80, 0.05);
}

.form-group input.invalid {
    border-color: var(--error-color);
    background-color: rgba(244, 67, 54, 0.05);
}

.error-hint {
    display: none;
    color: var(--error-color);
    font-size: 0.8rem;
    margin-top: 0.3rem;
    animation: fadeIn 0.3s ease-out;
}

/* Coordinates Styling */
.coordinates-heading {
    color: var(--dark-color);
    font-size: 1.2rem;
    margin: 1.5rem 0 0.5rem;
    text-align: left;
}

.coordinates-note {
    text-align: left;
    font-size: 0.9rem;
    color: var(--gray-dark);
    margin-bottom: 1rem;
}

.coordinates-note i {
    color: var(--accent-color);
    margin-right: 0.5rem;
}

.coordinates-container {
    display: flex;
    gap: 1rem;
}

.coordinates-container .form-group {
    flex: 1;
}

/* Password Field Styling */
.password-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: var(--gray-dark);
    transition: color var(--transition-speed);
}

.password-toggle:hover {
    color: var(--accent-color);
}

.password-strength {
    height: 4px;
    background: var(--gray-medium);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.strength-text {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    margin-top: 0.3rem;
    color: var(--gray-dark);
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

.btn:disabled {
    background: var(--gray-dark);
    cursor: not-allowed;
}

/* Loading animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Login Link */
.login-link {
    margin-top: 1.5rem;
    font-size: 0.95rem;
    color: var(--gray-dark);
}

.login-link a {
    color: var(--accent-color);
    text-decoration: none;
    font-weight: 500;
    transition: color var(--transition-speed);
}

.login-link a:hover {
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

/* Responsive Design */
@media (max-width: 768px) {
    .register-section {
        max-width: 95%;
        padding: 1.8rem;
        margin: 2rem auto;
    }
    
    .header-container h1 {
        font-size: 1.8rem;
    }
    
    .logo {
        position: static;
        justify-content: center;
        margin-bottom: 1rem;
    }
    
    .coordinates-container {
        flex-direction: column;
        gap: 0;
    }
}
   </style>
</head>
<body>
    <header class="main-header">
    <a href="../main_index.php" class="logo">
            <div class="logo-text">Redstream</div>
        </a>
        <div class="header-container">
            <h1><i class="fas fa-hospital"></i> Hospital Registration</h1>
            <p>Join our Blood Availability Network</p>
        </div>
    </header>

    <section class="register-section">
        <h2>Register Your Hospital</h2>

        <?php if (isset($message)) { ?>
            <p class="message <?php echo $message_type == 'success' ? 'success-message' : 'error-message'; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </p>
        <?php } ?>

        <form id="registrationForm" action="hospital_register.php" method="POST" novalidate>
            <div class="form-group">
                <label for="name">Hospital Name</label>
                <input type="text" id="name" name="name" required placeholder="Enter hospital name">
                <i class="fas fa-hospital-alt"></i>
                <div class="error-hint" id="nameError">Please enter your hospital name</div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter hospital email">
                <i class="fas fa-envelope"></i>
                <div class="error-hint" id="emailError">Please enter a valid email address</div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="Enter hospital phone number">
                <i class="fas fa-phone-alt"></i>
                <div class="error-hint" id="phoneError">Please enter a valid phone number</div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required placeholder="Enter hospital address">
                <i class="fas fa-map-marker-alt"></i>
                <div class="error-hint" id="addressError">Please enter your hospital address</div>
            </div>
            
            <h3 class="coordinates-heading">Location Coordinates</h3>
            <p class="coordinates-note">
                <i class="fas fa-info-circle"></i> Please enter the exact coordinates of your hospital location for accurate mapping.
            </p>
            
            <div class="coordinates-container">
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="text" id="latitude" name="latitude" required placeholder="E.g., 40.7128">
                    <i class="fas fa-map-pin"></i>
                    <div class="error-hint" id="latitudeError">Please enter a valid latitude</div>
                </div>
                
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="text" id="longitude" name="longitude" required placeholder="E.g., -74.0060">
                    <i class="fas fa-map-pin"></i>
                    <div class="error-hint" id="longitudeError">Please enter a valid longitude</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Create a secure password">
                    <i class="fas fa-lock"></i>
                    <span class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
                <div class="strength-text">
                    <span id="strengthText">Password strength</span>
                    <span id="strengthLevel"></span>
                </div>
                <div class="error-hint" id="passwordError">Password must be at least 8 characters with numbers and special characters</div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="password-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm your password">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="error-hint" id="confirmPasswordError">Passwords do not match</div>
            </div>
            
            <div class="form-group">
                <button type="submit" id="submitBtn" class="btn">
                    <span id="submitText">Register Hospital</span>
                    <span id="loadingIndicator" style="display: none;" class="loading"></span>
                </button>
            </div>
            <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');

            function autoDetectLocation() {
                if ('geolocation' in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitudeInput.value = position.coords.latitude.toFixed(6);
                            longitudeInput.value = position.coords.longitude.toFixed(6);
                        },
                        function(error) {
                            console.error("Error getting location", error);
                            latitudeInput.removeAttribute('readonly');
                            longitudeInput.removeAttribute('readonly');
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 30000
                        }
                    );
                } else {
                    latitudeInput.removeAttribute('readonly');
                    longitudeInput.removeAttribute('readonly');
                }
            }

            autoDetectLocation();
        });
    </script>
        </form>

        <p class="login-link">Already registered? <a href="hospital_login.php">Login here</a></p>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Redstream Blood Availability System | Connecting Donors & Hospitals</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');
            const togglePassword = document.getElementById('togglePassword');
            const passwordStrength = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            const strengthLevel = document.getElementById('strengthLevel');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            
            // Password strength meter
            password.addEventListener('input', function() {
                const val = this.value;
                const strength = checkPasswordStrength(val);
                updatePasswordStrengthUI(strength);
                
                // Show/hide error based on strength
                if (val.length > 0 && strength.score < 2) {
                    document.getElementById('passwordError').style.display = 'block';
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else if (val.length > 0) {
                    document.getElementById('passwordError').style.display = 'none';
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else {
                    document.getElementById('passwordError').style.display = 'none';
                    this.classList.remove('valid');
                    this.classList.remove('invalid');
                }
            });
            
            // Check password match
            confirmPassword.addEventListener('input', function() {
                if (this.value && this.value !== password.value) {
                    document.getElementById('confirmPasswordError').style.display = 'block';
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else if (this.value) {
                    document.getElementById('confirmPasswordError').style.display = 'none';
                    this.classList.add('valid');
                    this.classList.remove('invalid');
                } else {
                    document.getElementById('confirmPasswordError').style.display = 'none';
                    this.classList.remove('valid');
                    this.classList.remove('invalid');
                }
            });
            
            // Input validation for live feedback
            const inputs = document.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateInput(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('invalid')) {
                        validateInput(this);
                    }
                });
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate all fields first
                let isValid = true;
                inputs.forEach(input => {
                    if (!validateInput(input)) {
                        isValid = false;
                    }
                });
                
                // Check password match
                if (password.value !== confirmPassword.value) {
                    document.getElementById('confirmPasswordError').style.display = 'block';
                    confirmPassword.classList.add('invalid');
                    isValid = false;
                }
                
                // Check password strength
                const strength = checkPasswordStrength(password.value);
                if (strength.score < 2) {
                    document.getElementById('passwordError').style.display = 'block';
                    password.classList.add('invalid');
                    isValid = false;
                }
                
                // If valid, submit the form
                if (isValid) {
                    // Show loading state
                    submitText.style.display = 'none';
                    loadingIndicator.style.display = 'inline-block';
                    submitBtn.disabled = true;
                    
                    // Submit the form after a short delay (simulating server processing)
                    setTimeout(() => {
                        this.submit();
                    }, 800);
                } else {
                    // Scroll to first error
                    const firstError = document.querySelector('input.invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Helper Functions
            function validateInput(input) {
                const id = input.id;
                const value = input.value.trim();
                const errorElement = document.getElementById(id + 'Error');
                
                let isValid = true;
                
                switch(id) {
                    case 'name':
                        isValid = value.length >= 3;
                        break;
                    case 'email':
                        isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                        break;
                    case 'phone':
                        isValid = /^[\d\+\-\(\) ]{10,15}$/.test(value);
                        break;
                    case 'address':
                        isValid = value.length >= 5;
                        break;
                    case 'latitude':
                        isValid = /^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}$/.test(value);
                        break;
                    case 'longitude':
                        isValid = /^-?((1[0-7][0-9])|([1-9]?[0-9]))\.{1}\d{1,6}$/.test(value);
                        break;
                    case 'password':
                        // Password validation is handled separately
                        return true;
                    case 'confirmPassword':
                        // Confirmation is handled separately
                        return true;
                }
                
                if (!value) {
                    errorElement.textContent = 'This field is required';
                    errorElement.style.display = 'block';
                    input.classList.add('invalid');
                    input.classList.remove('valid');
                    return false;
                } else if (!isValid) {
                    errorElement.style.display = 'block';
                    input.classList.add('invalid');
                    input.classList.remove('valid');
                    return false;
                } else {
                    errorElement.style.display = 'none';
                    input.classList.remove('invalid');
                    input.classList.add('valid');
                    return true;
                }
            }
            
            function checkPasswordStrength(password) {
                let score = 0;
                
                // Length check
                if (password.length >= 8) score++;
                if (password.length >= 12) score++;
                
                // Complexity checks
                if (/[0-9]/.test(password)) score++;  // Has number
                if (/[a-z]/.test(password)) score++;  // Has lowercase
                if (/[A-Z]/.test(password)) score++;  // Has uppercase
                if (/[^a-zA-Z0-9]/.test(password)) score++;  // Has special character
                
                const result = {
                    score: Math.min(score, 5),
                    label: '',
                    color: ''
                };
                
                // Define strength levels
                switch(true) {
                    case (score <= 1):
                        result.label = 'Very Weak';
                        result.color = '#f44336';
                        break;
                    case (score === 2):
                        result.label = 'Weak';
                        result.color = '#ff9800';
                        break;
                    case (score === 3):
                        result.label = 'Fair';
                        result.color = '#ffeb3b';
                        break;
                    case (score === 4):
                        result.label = 'Good';
                        result.color = '#8bc34a';
                        break;
                    case (score >= 5):
                        result.label = 'Strong';
                        result.color = '#4caf50';
                        break;
                }
                
                return result;
            }
            
            function updatePasswordStrengthUI(strength) {
                // Update the width and color of the strength bar
                passwordStrength.style.width = (strength.score * 20) + '%';
                passwordStrength.style.backgroundColor = strength.color;
                
                // Update the text
                strengthLevel.textContent = strength.label;
                strengthLevel.style.color = strength.color;
            }
        });
    </script>
</body>
</html>