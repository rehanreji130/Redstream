<?php
session_start();
include('../includes/db_connection.php'); // Database connection

$message = "";
$message_type = "";

// Handle recipient registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']);
    $blood_type = mysqli_real_escape_string($conn, $_POST['blood_type']); // Blood type field
    $phone = mysqli_real_escape_string($conn, $_POST['phone']); // New phone number field
    
    // Check if email already exists
    $check_query = "SELECT * FROM recipients WHERE recipient_email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Email already registered. Please log in.";
        $message_type = "error";
    } else {
        // Handle profile picture upload
        $profile_picture = "default_avatar.png"; // Default image
        
        if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if(in_array($_FILES['profile_picture']['type'], $allowed_types) && $_FILES['profile_picture']['size'] <= $max_size) {
                $file_name = time() . '_' . $_FILES['profile_picture']['name'];
                $upload_dir = '../uploads/profile_pictures/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $file_name;
                
                if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    $profile_picture = $file_name;
                }
            } else {
                $message = "Invalid file type or size (max 2MB). Registration failed.";
                $message_type = "error";
            }
        }
        
        if($message_type != "error") {
            // Insert recipient details into the database
            $query = "INSERT INTO recipients (recipient_name, recipient_email, recipient_password, recipient_latitude, recipient_longitude, recipient_blood_type, recipient_phone, profile_picture) 
                      VALUES ('$name', '$email', '$password', '$latitude', '$longitude', '$blood_type', '$phone', '$profile_picture')";
            if (mysqli_query($conn, $query)) {
                $message = "Registration successful! You can now log in.";
                $message_type = "success";
            } else {
                $message = "Error in registration. Please try again.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Registration | Blood Availability System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        
        .register-section {
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
        
        .btn .btn-wave {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: btnWave 0.8s ease-out;
            pointer-events: none;
        }
        
        .location-btn {
            background: var(--accent-color);
            padding: 0.5rem;
            margin-top: 0.5rem;
            width: auto;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .location-btn:hover {
            background: #3d6985;
        }
        
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
        
        @media (max-width: 768px) {
            .register-section {
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
        .form-group select {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid var(--gray-medium);
    border-radius: 8px;
    font-size: 1rem;
    background: var(--gray-light);
    transition: all var(--transition-speed);
    appearance: none; /* Removes default browser styling */
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}

.form-group select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 8px rgba(230, 57, 70, 0.2);
    outline: none;
    background: white;
}

/* Custom dropdown arrow */
.form-group.select-container {
    position: relative;
}

.form-group.select-container::after {
    content: '\f078'; /* Font Awesome dropdown icon */
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 1rem;
    top: 2.7rem;
    color: var(--gray-dark);
    pointer-events: none;
}

/* Style for option elements */
.form-group select option {
    padding: 10px;
    background-color: white;
    color: var(--dark-color);
}

/* Style for the placeholder/disabled option */
.form-group select option[disabled] {
    color: var(--gray-dark);
}

/* Profile picture upload styles */
.profile-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 2rem;
}

.profile-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: var(--gray-medium);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 0.8rem;
    position: relative;
    border: 3px solid var(--light-color);
}

.profile-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-preview-icon {
    font-size: 2.5rem;
    color: var(--gray-dark);
}

.file-upload-container {
    position: relative;
    overflow: hidden;
    display: inline-block;
}

.file-upload-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    background: var(--light-color);
    color: var(--dark-color);
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-speed);
}

.file-upload-btn:hover {
    background: var(--accent-color);
    color: white;
}

.file-upload-btn i {
    margin-right: 0.5rem;
}

.file-upload-input {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.upload-hint {
    font-size: 0.8rem;
    color: var(--gray-dark);
    margin-top: 0.5rem;
    text-align: center;
}
    </style>
</head>
<body>
    <header class="main-header">
        <a href="../index.php" class="logo">
            <span class="logo-text">Redstream</span>
        </a>
        <div class="header-container">
            <h1>Recipient Registration</h1>
            <p>Create an account to search for available blood in nearby hospitals</p>
        </div>
        
        <!-- Decorative blood drops -->
        <div class="blood-drop" style="width: 80px; height: 80px; top: -20px; left: 15%;"></div>
        <div class="blood-drop" style="width: 40px; height: 40px; bottom: 20px; left: 25%;"></div>
        <div class="blood-drop" style="width: 60px; height: 60px; top: 10px; right: 20%;"></div>
        <div class="blood-drop" style="width: 30px; height: 30px; bottom: 30px; right: 30%;"></div>
    </header>

    <section class="register-section">
        <div class="form-header">
            <h2>Create Your Account</h2>
            <p>Register to locate blood donors and hospitals near you</p>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="message <?php echo ($message_type == "success") ? 'success-message' : 'error-message'; ?>">
                <i class="fas <?php echo ($message_type == "success") ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <form action="" method="POST" id="registrationForm" enctype="multipart/form-data">
            <!-- Profile Picture Upload -->
            <div class="profile-upload">
                <div class="profile-preview" id="profilePreview">
                    <i class="fas fa-user profile-preview-icon" id="defaultIcon"></i>
                    <img id="previewImage" src="" style="display: none;">
                </div>
                <div class="file-upload-container">
                    <label for="profile_picture" class="file-upload-btn">
                        <i class="fas fa-camera"></i> Upload Photo
                    </label>
                    <input type="file" name="profile_picture" id="profile_picture" class="file-upload-input" accept="image/jpeg, image/png, image/gif">
                </div>
                <div class="upload-hint">JPEG, PNG or GIF (Max. 2MB)</div>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" required>
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" placeholder="e.g., +1 123-456-7890" required>
                <i class="fas fa-phone input-icon"></i>
            </div>

            <div class="form-group select-container">
                <label for="blood_type">Blood Type</label>
                <select name="blood_type" id="blood_type" required>
                    <option value="" disabled selected>Select your blood type</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <i class="fas fa-eye-slash input-icon" id="togglePassword"></i>
                <div class="password-strength">
                    <div class="password-strength-meter" id="passwordStrengthMeter"></div>
                </div>
                <div class="password-hint" id="passwordHint">Use 8+ characters with at least 1 uppercase, number, and symbol</div>
            </div>

            <div class="form-group">
                <label>Your Location</label>
                <div style="display: flex; gap: 0.5rem;">
                    <div style="flex: 1;">
                        <input type="text" name="latitude" id="latitude" placeholder="Latitude" required readonly>
                    </div>
                    <div style="flex: 1;">
                        <input type="text" name="longitude" id="longitude" placeholder="Longitude" required readonly>
                    </div>
                </div>
                <button type="button" class="btn location-btn" id="getLocation">
                    <i class="fas fa-map-marker-alt"></i> Get My Location
                </button>
                <div id="locationStatus" class="password-hint" style="margin-top: 0.5rem;"></div>
            </div>

            <button type="submit" class="btn" id="registerBtn">Create Account</button>
        </form>

        <div class="form-links">
            Already have an account? <a href="recipient_login.php">Sign In</a>
        </div>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Redstream Blood Availability System</p>
            <div class="footer-links">
                <a href="../about.php">About</a>
                <a href="../privacy.php">Privacy Policy</a>
                <a href="../contact.php">Contact Us</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Profile picture preview
            const profileInput = document.getElementById('profile_picture');
            const previewImage = document.getElementById('previewImage');
            const defaultIcon = document.getElementById('defaultIcon');
            
            profileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                        defaultIcon.style.display = 'none';
                    }
                    reader.readAsDataURL(file);
                    
                    // Validate file size
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    if (file.size > maxSize) {
                        alert('File size exceeds 2MB. Please choose a smaller image.');
                        this.value = '';
                        previewImage.style.display = 'none';
                        defaultIcon.style.display = 'block';
                    }
                }
            });
            
            // Password toggle visibility
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Password strength meter
            const passwordInput = document.getElementById('password');
            const strengthMeter = document.getElementById('passwordStrengthMeter');
            const passwordHint = document.getElementById('passwordHint');
            
            passwordInput.addEventListener('input', function() {
                const val = passwordInput.value;
                let strength = 0;
                let message = '';
                
                if (val.length >= 8) strength += 25;
                if (val.match(/[A-Z]/)) strength += 25;
                if (val.match(/[0-9]/)) strength += 25;
                if (val.match(/[^A-Za-z0-9]/)) strength += 25;
                
                strengthMeter.style.width = strength + '%';
                
                if (strength <= 25) {
                    strengthMeter.style.backgroundColor = '#f44336';
                    message = 'Weak password';
                } else if (strength <= 50) {
                    strengthMeter.style.backgroundColor = '#ff9800';
                    message = 'Medium password';
                } else if (strength <= 75) {
                    strengthMeter.style.backgroundColor = '#2196f3';
                    message = 'Good password';
                } else {
                    strengthMeter.style.backgroundColor = '#4caf50';
                    message = 'Strong password';
                }
                
                passwordHint.textContent = message;
            });
            
            // Phone number validation
            const phoneInput = document.getElementById('phone');
            
            phoneInput.addEventListener('input', function() {
                // Remove any validation styling on input
                this.style.borderColor = '';
            });
            
            // Geolocation
            const getLocationBtn = document.getElementById('getLocation');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const locationStatus = document.getElementById('locationStatus');
            
            // Get location on page load
            tryGetLocation();
            
            // Get location on button click
            getLocationBtn.addEventListener('click', function() {
                tryGetLocation();
            });
            
            function tryGetLocation() {
                if (navigator.geolocation) {
                    locationStatus.textContent = "Fetching your location...";
                    locationStatus.style.color = "#2196f3";
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitudeInput.value = position.coords.latitude.toFixed(6);
                            longitudeInput.value = position.coords.longitude.toFixed(6);
                            locationStatus.textContent = "Location successfully captured!";
                            locationStatus.style.color = "#4caf50";
                        },
                        function(error) {
                            console.error("Error getting location: ", error);
                            locationStatus.textContent = "Unable to get your location. Please try again or enter manually.";
                            locationStatus.style.color = "#f44336";
                            
                            // Make inputs editable if geolocation fails
                            latitudeInput.readOnly = false;
                            longitudeInput.readOnly = false;
                        }
                    );
                } else {
                    locationStatus.textContent = "Geolocation is not supported by your browser. Please enter manually.";
                    locationStatus.style.color = "#f44336";
                    
                    // Make inputs editable if geolocation not supported
                    latitudeInput.readOnly = false;
                    longitudeInput.readOnly = false;
                }
            }
            
            // Button wave effect
            const buttons = document.querySelectorAll('.btn');
            
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('btn-wave');
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 800);
                });
            });
            
            // Form validation
            const form = document.getElementById('registrationForm');
            const emailInput = document.getElementById('email');
            const nameInput = document.getElementById('name');
            const bloodTypeInput = document.getElementById('blood_type');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Validate email format
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(emailInput.value)) {
                    emailInput.style.borderColor = '#f44336';
                    valid = false;
                    alert('Please enter a valid email address.');
                }
                
                // Validate name (no numbers or special characters)
                const namePattern = /^[A-Za-z\s]+$/;
                if (!namePattern.test(nameInput.value)) {
                    nameInput.style.borderColor = '#f44336';
                    valid = false;
                    alert('Name should contain only letters and spaces.');
                }
                
                // Validate password strength
                if (passwordInput.value.length < 8) {
                    passwordInput.style.borderColor = '#f44336';
                    valid = false;
                    alert('Password must be at least 8 characters long.');
                }
                
                // Check if blood type is selected
                if (bloodTypeInput.value === "") {
                    bloodTypeInput.style.borderColor = '#f44336';
                    valid = false;
                    alert('Please select your blood type.');
                }
                
                // Check if location is captured
                if (latitudeInput.value === "" || longitudeInput.value === "") {
                    locationStatus.textContent = "Location is required. Please click 'Get My Location'.";
                    locationStatus.style.color = "#f44336";
                    valid = false;
                    alert('Please provide your location to continue.');
                }
                
                // Prevent form submission if validation fails
                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>