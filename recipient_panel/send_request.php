<?php
session_start();
include('../includes/db_connection.php'); // Database connection

// Check if recipient is logged in
if (!isset($_SESSION['recipient_id'])) {
    header("Location: recipient_login.php");
    exit();
}

// Initialize variables
$message = "";
$messageType = ""; // 'success' or 'error'
$hospital = null;

// Check if hospital_id and blood_type are provided in URL
if (isset($_GET['hospital_id']) && isset($_GET['blood_type'])) {
    $hospital_id = (int)$_GET['hospital_id'];
    $blood_type = $_GET['blood_type'];
    $recipient_id = $_SESSION['recipient_id'];
    
    // Validate blood type
    $valid_blood_types = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    if (!in_array($blood_type, $valid_blood_types)) {
        $message = "Invalid blood type specified.";
        $messageType = "error";
    } else {
        // Get hospital details
        $hospital_query = "SELECT * FROM hospitals WHERE hospital_id = ?";
        $stmt = $conn->prepare($hospital_query);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $hospital = $result->fetch_assoc();
            
            // Check if there is already a pending request for this recipient and hospital
            $check_query = "SELECT * FROM blood_requests 
                           WHERE recipient_id = ? 
                           AND hospital_id = ? 
                           AND blood_type = ? 
                           AND request_status = 'pending'";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("iis", $recipient_id, $hospital_id, $blood_type);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $message = "You already have a pending request for this blood type at this hospital.";
                $messageType = "error";
            } else {
                // Process form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_request'])) {
                    // Insert the blood request
                    $insert_query = "INSERT INTO blood_requests (recipient_id, hospital_id, blood_type) 
                                    VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("iis", $recipient_id, $hospital_id, $blood_type);
                    
                    if ($insert_stmt->execute()) {
                        $message = "Blood request successfully sent to " . htmlspecialchars($hospital['hospital_name']) . ". They will contact you soon.";
                        $messageType = "success";
                    } else {
                        $message = "Failed to send blood request. Please try again.";
                        $messageType = "error";
                    }
                    $insert_stmt->close();
                }
            }
            $check_stmt->close();
        } else {
            $message = "Hospital not found.";
            $messageType = "error";
        }
        $stmt->close();
    }
} else {
    $message = "Missing required information.";
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Blood Request</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #d32f2f;
        --primary-dark: #b71c1c;
        --primary-light: #ffcdd2;
        --secondary: #f5f5f5;
        --text-dark: #333333;
        --text-light: #ffffff;
        --accent: #2979ff;
        --success: #43a047;
        --error: #e53935;
        --border-radius: 8px;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    /* Header Styles */
    .main-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: var(--text-light);
        padding: 1.5rem 0;
        box-shadow: var(--shadow);
    }

    .header-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
        text-align: center;
    }

    .header-container h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .header-container p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    /* Main Content Area */
    .container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    /* User Navigation */
    .user-nav {
        background-color: white;
        padding: 1rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-weight: 600;
    }

    .user-name {
        font-weight: 600;
    }

    .nav-links {
        display: flex;
        gap: 1rem;
    }

    .nav-link {
        text-decoration: none;
        color: #555;
        font-weight: 500;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .nav-link:hover {
        color: var(--primary);
    }

    /* Request Blood Section */
    .request-blood-section {
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    /* Message Styling */
    .message {
        padding: 1.25rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: opacity 0.5s ease;
    }

    .success {
        background-color: #e8f5e9;
        color: var(--success);
        border-left: 4px solid var(--success);
    }

    .error {
        background-color: #ffebee;
        color: var(--error);
        border-left: 4px solid var(--error);
    }

    /* Request Confirmation Section */
    .request-confirmation {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .request-confirmation h2 {
        color: var(--primary);
        margin-bottom: 0.5rem;
        text-align: center;
        font-size: 1.8rem;
        position: relative;
        padding-bottom: 10px;
    }

    .request-confirmation h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background-color: var(--primary);
    }

    /* Hospital Details */
    .hospital-details {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .hospital-details h3 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 1.4rem;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
    }

    .hospital-info {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .info-item i {
        color: var(--primary);
        width: 20px;
        text-align: center;
    }

    .info-item a {
        color: var(--accent);
        text-decoration: none;
    }

    .info-item a:hover {
        text-decoration: underline;
    }

    /* Request Disclaimer */
    .request-disclaimer {
        background-color: #fff9c4;
        padding: 1.25rem;
        border-radius: var(--border-radius);
        border-left: 4px solid #fbc02d;
    }

    .request-disclaimer p {
        margin-bottom: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .request-disclaimer ul {
        margin-left: 2rem;
    }

    .request-disclaimer li {
        margin-bottom: 0.5rem;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .primary-btn {
        background-color: var(--primary);
        color: white;
        border: none;
    }

    .primary-btn:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .secondary-btn {
        background-color: #f5f5f5;
        color: #555;
        border: 1px solid #ddd;
    }

    .secondary-btn:hover {
        background-color: #e0e0e0;
        transform: translateY(-2px);
    }

    /* Success Actions */
    .success-actions, .error-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    /* Footer Styling */
    .main-footer {
        background-color: #333;
        color: #fff;
        padding: 2rem 0;
        margin-top: 3rem;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
        text-align: center;
    }

    .footer-container p {
        margin: 0.5rem 0;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .footer-links {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .footer-link {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .footer-link:hover {
        color: white;
        text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .header-container h1 {
            font-size: 2rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .user-nav {
            flex-direction: column;
            gap: 1rem;
        }

        .nav-links {
            width: 100%;
            justify-content: space-around;
        }

        .success-actions, .error-actions {
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .header-container h1 {
            font-size: 1.8rem;
        }

        .request-blood-section {
            padding: 1.5rem;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1><i class="fas fa-heartbeat"></i> Send Blood Request</h1>
            <p>Request blood from hospitals with available supply</p>
        </div>
    </header>

    <div class="container">
        <!-- User Navigation -->
        <div class="user-nav">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div class="user-name">
                        <?php echo isset($_SESSION['recipient_name']) ? htmlspecialchars($_SESSION['recipient_name']) : 'Recipient'; ?>
                    </div>
                    <small>Blood Recipient</small>
                </div>
            </div>
            <div class="nav-links">
                <a href="recipient_search_blood.php" class="nav-link"><i class="fas fa-search"></i> Search Blood</a>
                <a href="search_history.php" class="nav-link"><i class="fas fa-history"></i> My Search History</a>
                <a href="recipient_logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <section class="request-blood-section">
            <?php if (!empty($message)) { ?>
                <div class="message <?php echo $messageType; ?>">
                    <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>

                <?php if ($messageType === 'success') { ?>
                    <div class="success-actions">
                        <a href="recipient_search_blood.php" class="btn primary-btn">
                            <i class="fas fa-search"></i> Search For More
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="error-actions">
                        <a href="recipient_search_blood.php" class="btn primary-btn">
                            <i class="fas fa-arrow-left"></i> Back to Search
                        </a>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php if ($hospital && $messageType !== 'success' && $_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
                <div class="request-confirmation">
                    <h2><i class="fas fa-paper-plane"></i> Confirm Blood Request</h2>
                    
                    <div class="hospital-details">
                        <h3><?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
                        <div class="hospital-info">
                            <div class="info-item">
                                <i class="fas fa-tint"></i>
                                <span>Blood Type: <strong><?php echo htmlspecialchars($blood_type); ?></strong></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span>Contact: <a href="tel:<?php echo htmlspecialchars($hospital['hospital_phone']); ?>"><?php echo htmlspecialchars($hospital['hospital_phone']); ?></a></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Address: <?php echo htmlspecialchars($hospital['hospital_address']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="request-disclaimer">
                        <p><i class="fas fa-info-circle"></i> By confirming this request:</p>
                        <ul>
                            <li>The hospital will be notified of your blood need</li>
                            <li>Your contact information will be shared with the hospital</li>
                            <li>The hospital may contact you to verify your request</li>
                            <li>You can cancel this request at any time from your dashboard</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-actions">
                            <a href="recipient_search_blood.php" class="btn secondary-btn">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="confirm_request" class="btn primary-btn">
                                <i class="fas fa-check"></i> Confirm Request
                            </button>
                        </div>
                    </form>
                </div>
            <?php } ?>
        </section>
    </div>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability System</p>
            <p>Helping connect blood recipients with available donors</p>
            <div class="footer-links">
                <a href="about.php" class="footer-link">About</a>
                <a href="privacy.php" class="footer-link">Privacy Policy</a>
                <a href="terms.php" class="footer-link">Terms of Service</a>
                <a href="contact.php" class="footer-link">Contact Us</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Auto-hide messages after 5 seconds
            setTimeout(function() {
                const messages = document.querySelector('.message');
                if (messages) {
                    messages.style.opacity = '0';
                    setTimeout(function() {
                        messages.style.display = 'none';
                    }, 500);
                }
            }, 5000);
        });
    </script>
</body>
</html>