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
$recipient_id = 0;
$recipient_name = '';
$recipient_email = '';
$recipient_blood_type = '';
$recipient_phone = '';
$recipient_latitude = '';
$recipient_longitude = '';
$errors = [];

// Get recipient data if ID is provided
if (isset($_GET['recipient_id'])) {
    $recipient_id = filter_input(INPUT_GET, 'recipient_id', FILTER_VALIDATE_INT);
    
    if (!$recipient_id) {
        $_SESSION['error_message'] = "Invalid recipient ID.";
        header("Location: admin_manage_recipients.php");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM recipients WHERE recipient_id = ?");
    $stmt->bind_param("i", $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Recipient not found.";
        header("Location: admin_manage_recipients.php");
        exit();
    }
    
    $recipient = $result->fetch_assoc();
    $recipient_name = $recipient['recipient_name'];
    $recipient_email = $recipient['recipient_email'];
    $recipient_blood_type = $recipient['recipient_blood_type'];
    $recipient_phone = $recipient['recipient_phone'];
    $recipient_latitude = $recipient['recipient_latitude'] ?? '';
    $recipient_longitude = $recipient['recipient_longitude'] ?? '';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $recipient_id = filter_input(INPUT_POST, 'recipient_id', FILTER_VALIDATE_INT);
    $recipient_name = filter_input(INPUT_POST, 'recipient_name', FILTER_SANITIZE_STRING);
    $recipient_email = filter_input(INPUT_POST, 'recipient_email', FILTER_SANITIZE_EMAIL);
    $recipient_blood_type = filter_input(INPUT_POST, 'recipient_blood_type', FILTER_SANITIZE_STRING);
    $recipient_phone = filter_input(INPUT_POST, 'recipient_phone', FILTER_SANITIZE_STRING);
    $recipient_latitude = filter_input(INPUT_POST, 'recipient_latitude', FILTER_SANITIZE_STRING);
    $recipient_longitude = filter_input(INPUT_POST, 'recipient_longitude', FILTER_SANITIZE_STRING);
    
    // Validate form data
    if (empty($recipient_name)) $errors[] = "Name is required.";
    if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($recipient_blood_type)) $errors[] = "Blood type is required.";
    if (empty($recipient_phone)) $errors[] = "Phone number is required.";
    
    // Update recipient if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE recipients SET 
                recipient_name = ?, 
                recipient_email = ?, 
                recipient_blood_type = ?, 
                recipient_phone = ?, 
                recipient_latitude = ?,
                recipient_longitude = ?
                WHERE recipient_id = ?");
                
        $stmt->bind_param("ssssddi", 
            $recipient_name, 
            $recipient_email, 
            $recipient_blood_type, 
            $recipient_phone,
            $recipient_latitude,
            $recipient_longitude,
            $recipient_id
        );
        
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Recipient updated successfully.";
                header("Location: admin_manage_recipients.php");
                exit();
            } else {
                $_SESSION['error_message'] = "No changes made or recipient not found.";
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error updating recipient: " . $e->getMessage();
        }
    }
}

$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipient | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #e63946;
            --dark-color: #1d3557;
            --accent-color: #457b9d;
            --gray-medium: #e0e0e0;
            --border-radius-md: 8px;
            --box-shadow-medium: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition-speed: 0.3s;
            --font-primary: 'Poppins', sans-serif;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: var(--font-primary);
            line-height: 1.6;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4efe9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #d90429);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .header-container h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .main-nav {
            background: var(--dark-color);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        
        .edit-section {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--box-shadow-medium);
        }
        
        .edit-section h2 {
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--border-radius-md);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-medium);
            border-radius: var(--border-radius-md);
            font-family: var(--font-primary);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-container {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--accent-color);
            color: white;
            border: none;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #757575;
            border: 1px solid #e0e0e0;
        }
        
        .footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        
        .coordinates-container {
            display: flex;
            gap: 1rem;
        }
        
        .coordinates-container .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Edit Recipient</h1>
            <p>Update recipient information</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_manage_recipients.php">Back to Recipients</a></li>
            <li><a href="main_logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="edit-section">
        <h2>Edit Recipient Details</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="message error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_edit_recipient.php">
            <input type="hidden" name="recipient_id" value="<?php echo $recipient_id; ?>">
            
            <div class="form-group">
                <label for="recipient_name">Name</label>
                <input type="text" id="recipient_name" name="recipient_name" class="form-control" 
                       value="<?php echo htmlspecialchars($recipient_name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="recipient_email">Email</label>
                <input type="email" id="recipient_email" name="recipient_email" class="form-control" 
                       value="<?php echo htmlspecialchars($recipient_email); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="recipient_blood_type">Blood Type</label>
                <select id="recipient_blood_type" name="recipient_blood_type" class="form-control" required>
                    <?php foreach ($blood_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($recipient_blood_type === $type) ? 'selected' : ''; ?>>
                            <?php echo $type; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="recipient_phone">Phone Number</label>
                <input type="tel" id="recipient_phone" name="recipient_phone" class="form-control" 
                       value="<?php echo htmlspecialchars($recipient_phone); ?>" required>

            
            <div class="btn-container">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="admin_manage_recipients.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </section>

    <footer class="footer">
        <p>&copy; 2025 Blood Availability Website</p>
    </footer>

    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const name = document.getElementById('recipient_name').value.trim();
                const email = document.getElementById('recipient_email').value.trim();
                const phone = document.getElementById('recipient_phone').value.trim();
                
                if (!name || !email || !phone) {
                    e.preventDefault();
                    alert('Please fill all required fields.');
                }
                
                // Validate coordinates if provided
                const lat = document.getElementById('recipient_latitude').value.trim();
                const lng = document.getElementById('recipient_longitude').value.trim();
                
                if ((lat && !isValidCoordinate(lat, -90, 90)) || 
                    (lng && !isValidCoordinate(lng, -180, 180))) {
                    e.preventDefault();
                    alert('Please enter valid latitude (-90 to 90) and longitude (-180 to 180) values.');
                }
            });
            
            function isValidCoordinate(value, min, max) {
                const num = parseFloat(value);
                return !isNaN(num) && num >= min && num <= max;
            }
        });
    </script>
</body>
</html>