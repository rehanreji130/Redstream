<?php
session_start();

// Include the database connection
require_once('../includes/db_connection.php');
require_once('../includes/functions.php'); // Add a functions file for reusable code

// Redirect to login page if not logged in
if (!isset($_SESSION['hospital_id'])) {
    header("Location: hospital_login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission for adding a new donor
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate input data
    $donor_name = trim($_POST['donor_name'] ?? '');
    $donor_blood_type = trim($_POST['blood_type'] ?? '');
    $donor_phone = trim($_POST['contact'] ?? '');
    // Add country code +91 to phone number only if it doesn't already have it
    if (!str_starts_with($donor_phone, '+91')) {
        $donor_phone = "+91" . $donor_phone;
    }
    $donor_email = trim($_POST['email'] ?? '');
    $last_donation_date = trim($_POST['donation_date'] ?? '');
    $blood_units = trim($_POST['blood_units'] ?? ''); // Required blood units
    $hospital_id = $_SESSION['hospital_id']; // Get hospital ID from session
    
    // Basic validation
    if (empty($donor_name) || empty($donor_blood_type) || empty($donor_phone) || 
        empty($donor_email) || empty($last_donation_date) || empty($blood_units)) {
        $error_message = "All fields are required. Please complete the form.";
    } elseif (!filter_var($donor_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (!is_numeric($blood_units) || $blood_units <= 0) {
        $error_message = "Blood units must be a positive number greater than zero.";
    } else {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO donors (hospital_id, donor_name, donor_blood_type, donor_phone, donor_email, last_donation_date, blood_units) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("isssssd", $hospital_id, $donor_name, $donor_blood_type, $donor_phone, $donor_email, $last_donation_date, $blood_units);
        
        if ($stmt->execute()) {
            $success_message = "Donor added successfully!";
            // Clear form data after successful submission
            $donor_name = $donor_blood_type = $donor_phone = $donor_email = $last_donation_date = $blood_units = '';
        } else {
            $error_message = "Error adding donor: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get hospital name for display in header
$stmt = $conn->prepare("SELECT hospital_name FROM hospitals WHERE hospital_id = ?");
$stmt->bind_param("i", $_SESSION['hospital_id']);
$stmt->execute();
$result = $stmt->get_result();
$hospital_data = $result->fetch_assoc();
$hospital_name = $hospital_data['hospital_name'] ?? 'Hospital Dashboard';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blood Donor | <?php echo htmlspecialchars($hospital_name); ?></title>
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #457b9d;
            --light-color: #f1faee;
            --dark-color: #1d3557;
            --success-color: #2a9d8f;
            --warning-color: #e9c46a;
            --error-color: #e76f51;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .header-container h1 {
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        
        .nav-links {
            margin-top: 1rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .add-donor-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            padding: 2rem;
            max-width: 800px;
        }
        
        .add-donor-section h2 {
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 5px rgba(69, 123, 157, 0.5);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
            display: inline-block;
        }
        
        .btn:hover {
            background-color: #c1121f;
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            text-align: center;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .blood-type-select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            background-color: white;
        }
        
        .field-info {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.3rem;
        }
        
        .required-field::after {
            content: '*';
            color: var(--primary-color);
            margin-left: 4px;
        }
        
        .main-footer {
            background-color: var(--dark-color);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .add-donor-section {
                padding: 1.5rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-container">
                <h1>Add Blood Donor</h1>
                <p>Enter donor details to register a new blood donor</p>
                <div class="nav-links">
                    <a href="hospital_dashboard.php">Dashboard</a>
                    <a href="hospital_logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <section class="add-donor-section">
            <h2>Register New Donor</h2>

            <?php if (!empty($success_message)): ?>
                <div class="message success-message">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error-message">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Donor Registration Form -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
                <div class="form-group">
                    <label for="donor_name" class="required-field">Donor Name:</label>
                    <input type="text" id="donor_name" name="donor_name" required 
                           placeholder="Enter donor's full name" 
                           value="<?php echo htmlspecialchars($donor_name ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_type" class="required-field">Blood Type:</label>
                        <select id="blood_type" name="blood_type" class="blood-type-select" required>
                            <option value="" disabled <?php echo empty($donor_blood_type) ? 'selected' : ''; ?>>Select blood type</option>
                            <option value="A+"  >A+</option>
                            <option value="A-"  >A-</option>
                            <option value="B+"  >B+</option>
                            <option value="B-"  >B-</option>
                            <option value="AB+" >AB+</option>
                            <option value="AB-" >AB-</option>
                            <option value="O+"  >O+</option>
                            <option value="O-"  >O-</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="donation_date" class="required-field">Donation Date:</label>
                        <input type="date" id="donation_date" name="donation_date" required 
                               value="<?php echo htmlspecialchars($last_donation_date ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact" class="required-field">Contact Number:</label>
                        <input type="tel" id="contact" name="contact" required 
                               placeholder="Enter donor's phone number" 
                               value="<?php echo htmlspecialchars($donor_phone ?? ''); ?>">
                                <p class="field-info">no need for country code (eg:- +91)</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required-field">Email Address:</label>
                        <input type="email" id="email" name="email" required
                               placeholder="Enter donor's email address" 
                               value="<?php echo htmlspecialchars($donor_email ?? ''); ?>">
                        <p class="field-info">For notification about donation drives and emergencies</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="blood_units" class="required-field">Blood Units Donated:</label>
                    <input type="number" id="blood_units" name="blood_units" required min="0.5" step="0.5"
                           placeholder="Enter number of blood units (e.g., 1 or 0.5)" 
                           value="<?php echo htmlspecialchars($blood_units ?? ''); ?>">
                    <p class="field-info">Enter the number of blood units donated. Can include half units.</p>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Register Donor</button>
                </div>
                <p class="field-info">All fields are required</p>
            </form>

            <a href="hospital_dashboard.php" class="back-link">Back to Dashboard</a>
        </section>
    </div>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Blood Availability System | <?php echo htmlspecialchars($hospital_name); ?></p>
        </div>
    </footer>

    <script>
        // Form validation 
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const emailField = document.getElementById('email');
            const bloodUnitsField = document.getElementById('blood_units');
            
            form.addEventListener('submit', function(event) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'var(--error-color)';
                    } else {
                        field.style.borderColor = '#ddd';
                    }
                });
                
                // Email validation
                if (emailField.value.trim() !== '') {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(emailField.value.trim())) {
                        isValid = false;
                        emailField.style.borderColor = 'var(--error-color)';
                    }
                }
                
                // Blood units validation
                const bloodUnits = parseFloat(bloodUnitsField.value);
                if (isNaN(bloodUnits) || bloodUnits <= 0) {
                    isValid = false;
                    bloodUnitsField.style.borderColor = 'var(--error-color)';
                }
                
                if (!isValid) {
                    event.preventDefault();
                    alert('Please fill in all required fields correctly.');
                }
            });
            
            // Reset field styling on input
            const formFields = form.querySelectorAll('input, select');
            formFields.forEach(field => {
                field.addEventListener('input', function() {
                    this.style.borderColor = '#ddd';
                });
            });
        });
    </script>
</body>
</html>