<?php
session_start();

// Check if the user is logged in as a hospital staff member
if (!isset($_SESSION['hospital_id'])) {
    header("Location: hospital_login.php");
    exit();
}

// Include the database connection
include('../includes/db_connection.php');

// Include Twilio PHP SDK
require_once '../vendor/autoload.php';
use Twilio\Rest\Client;

// Get hospital ID from session
$hospital_id = $_SESSION['hospital_id'];

// Get hospital information
$hospital_query = "SELECT hospital_name FROM hospitals WHERE hospital_id = ?";
$stmt = $conn->prepare($hospital_query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital_data = $result->fetch_assoc();
$hospital_name = $hospital_data ? $hospital_data['hospital_name'] : 'Your Hospital';

// Twilio configuration
$account_sid = 'YOUR_TWILIO_SID';
$auth_token = 'YOUR_TWILIO_AUTH_TOKEN';
$twilio_number = '+1234567890'; // Your Twilio number

// Initialize Twilio client
$twilio = new Client($account_sid, $auth_token);

// Handle form submission for sending notifications
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check which form was submitted
    if (isset($_POST['send_individual'])) {
        // Send to individual donor
        $donor_id = $_POST['donor_id'];
        $message = $_POST['message'];
        
        // Get donor information
        $donor_query = "SELECT donor_name, donor_phone FROM donors WHERE donor_id = ? AND hospital_id = ?";
        $stmt = $conn->prepare($donor_query);
        $stmt->bind_param("ii", $donor_id, $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $donor = $result->fetch_assoc();
        
        if ($donor) {
            // Send SMS
            try {
                $sms = $twilio->messages->create(
                    $donor['donor_phone'],
                    [
                        'from' => $twilio_number,
                        'body' => $message
                    ]
                );
                
                // Log the notification
                $log_query = "INSERT INTO notifications (hospital_id, donor_id, message, sent_at) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($log_query);
                $stmt->bind_param("iis", $hospital_id, $donor_id, $message);
                $stmt->execute();
                
                $notification = "Message sent successfully to " . $donor['donor_name'];
                $notification_type = "success";
            } catch (Exception $e) {
                $notification = "Error sending message: " . $e->getMessage();
                $notification_type = "error";
            }
        } else {
            $notification = "Donor not found";
            $notification_type = "error";
        }
    } elseif (isset($_POST['send_blood_type'])) {
        // Send to all donors of a specific blood type
        $blood_type = $_POST['blood_type'];
        $message = $_POST['message'];
        $successful = 0;
        $failed = 0;
        
        // Get all donors with the selected blood type
        $donors_query = "SELECT donor_id, donor_name, donor_phone FROM donors WHERE donor_blood_type = ? AND hospital_id = ?";
        $stmt = $conn->prepare($donors_query);
        $stmt->bind_param("si", $blood_type, $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($donor = $result->fetch_assoc()) {
            // Send SMS to each donor
            try {
                $sms = $twilio->messages->create(
                    $donor['donor_phone'],
                    [
                        'from' => $twilio_number,
                        'body' => $message
                    ]
                );
                
                // Log the notification
                $log_query = "INSERT INTO notifications (hospital_id, donor_id, message, sent_at) VALUES (?, ?, ?, NOW())";
                $stmt_log = $conn->prepare($log_query);
                $stmt_log->bind_param("iis", $hospital_id, $donor['donor_id'], $message);
                $stmt_log->execute();
                
                $successful++;
            } catch (Exception $e) {
                $failed++;
            }
        }
        
        if ($successful > 0) {
            $notification = "Successfully sent messages to $successful donors" . ($failed > 0 ? " ($failed failed)" : "");
            $notification_type = "success";
        } else {
            $notification = "Failed to send messages. Please check the phone numbers and try again.";
            $notification_type = "error";
        }
    } elseif (isset($_POST['send_emergency'])) {
        // Send emergency notification to all donors
        $message = $_POST['message'];
        $successful = 0;
        $failed = 0;
        
        // Get all donors
        $donors_query = "SELECT donor_id, donor_name, donor_phone FROM donors WHERE hospital_id = ?";
        $stmt = $conn->prepare($donors_query);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($donor = $result->fetch_assoc()) {
            // Send SMS to each donor
            try {
                $sms = $twilio->messages->create(
                    $donor['donor_phone'],
                    [
                        'from' => $twilio_number,
                        'body' => $message
                    ]
                );
                
                // Log the notification
                $log_query = "INSERT INTO notifications (hospital_id, donor_id, message, sent_at) VALUES (?, ?, ?, NOW())";
                $stmt_log = $conn->prepare($log_query);
                $stmt_log->bind_param("iis", $hospital_id, $donor['donor_id'], $message);
                $stmt_log->execute();
                
                $successful++;
            } catch (Exception $e) {
                $failed++;
            }
        }
        
        if ($successful > 0) {
            $notification = "Emergency alert sent to $successful donors" . ($failed > 0 ? " ($failed failed)" : "");
            $notification_type = "success";
        } else {
            $notification = "Failed to send emergency alerts. Please check the phone numbers and try again.";
            $notification_type = "error";
        }
    }
}

// Get the list of donors for individual messaging
$donors_query = "SELECT donor_id, donor_name, donor_blood_type, donor_phone, last_donation_date FROM donors WHERE hospital_id = ? ORDER BY donor_name";
$stmt = $conn->prepare($donors_query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$donors_result = $stmt->get_result();

// Get unique blood types for the blood type filter
$blood_types_query = "SELECT DISTINCT donor_blood_type FROM donors WHERE hospital_id = ? ORDER BY donor_blood_type";
$stmt = $conn->prepare($blood_types_query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$blood_types_result = $stmt->get_result();

// Get recent notification logs
$logs_query = "SELECT n.notification_id, n.message, n.sent_at, d.donor_name, d.donor_blood_type 
               FROM notifications n 
               JOIN donors d ON n.donor_id = d.donor_id 
               WHERE n.hospital_id = ? 
               ORDER BY n.sent_at DESC LIMIT 20";
$stmt = $conn->prepare($logs_query);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$logs_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Notifications | Blood Availability System</title>
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
            --warning: #ff9800;
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

        /* Navigation Styles */
        .main-nav {
            background-color: var(--primary-dark);
            padding: 0.5rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .main-nav ul {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            list-style: none;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .main-nav li {
            margin: 0.5rem 1rem;
        }

        .main-nav a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .main-nav a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .main-nav a i {
            font-size: 1rem;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Page Title */
        .page-title {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .page-title i {
            font-size: 2.5rem;
            color: var(--primary);
        }

        .page-title-text h1 {
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .page-title-text p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Notification Form Card */
        .notification-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background-color: var(--primary);
            color: white;
            padding: 1.25rem 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 2rem;
        }

        .tab-link {
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }

        .tab-link:hover {
            color: var(--primary);
        }

        .tab-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(41, 121, 255, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .blood-type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #d5d5d5;
            transform: translateY(-2px);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #f57c00;
            transform: translateY(-2px);
        }

        /* Template Buttons */
        .templates {
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .template-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .template-btn:hover {
            background-color: #e9e9e9;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background-color: #e8f5e9;
            border-left: 4px solid var(--success);
            color: #2e7d32;
        }

        .alert-error {
            background-color: #ffebee;
            border-left: 4px solid var(--error);
            color: #c62828;
        }

        .alert i {
            font-size: 1.5rem;
        }

        /* Tabs Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }

        .logs-table thead {
            background-color: #f5f5f5;
        }

        .logs-table th {
            text-align: left;
            padding: 1rem;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #ddd;
            white-space: nowrap;
        }

        .logs-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .logs-table tr:last-child td {
            border-bottom: none;
        }

        .logs-table tr:hover td {
            background-color: #f9f9f9;
        }

        .message-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .timestamp {
            white-space: nowrap;
            color: #666;
            font-size: 0.9rem;
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

        /* Character Counter */
        .char-counter {
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .char-counter.warning {
            color: var(--warning);
        }

        .char-counter.danger {
            color: var(--error);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container h1 {
                font-size: 2rem;
            }

            .page-title-text h1 {
                font-size: 1.75rem;
            }

            .main-nav ul {
                justify-content: space-around;
            }

            .tab-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .header-container h1 {
                font-size: 1.8rem;
            }

            .tabs {
                flex-direction: column;
                border-bottom: none;
                margin-bottom: 1.5rem;
            }

            .tab-link {
                border: 1px solid #ddd;
                border-bottom: none;
                border-radius: 0;
                text-align: center;
            }

            .tab-link:last-child {
                border-bottom: 1px solid #ddd;
            }

            .tab-link.active {
                border-left: 3px solid var(--primary);
                border-bottom: 1px solid #ddd;
            }

            .main-nav ul {
                flex-direction: column;
                align-items: center;
            }

            .main-nav li {
                margin: 0.25rem 0;
                width: 100%;
                text-align: center;
            }

            .main-nav a {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1><i class="fas fa-hospital"></i> Bloodbank</h1>
            <p>Blood donation management center</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="hospital_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="hospital_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <i class="fas fa-bell"></i>
            <div class="page-title-text">
                <h1>Donor Notifications</h1>
                <p>Send SMS notifications to your blood donors</p>
            </div>
        </div>

        <?php if (isset($notification)): ?>
            <div class="alert alert-<?php echo $notification_type; ?>">
                <i class="fas fa-<?php echo $notification_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <div><?php echo $notification; ?></div>
            </div>
        <?php endif; ?>

        <div class="notification-card">
            <div class="card-header">
                <i class="fas fa-sms"></i> Send SMS Notifications
            </div>
            <div class="card-body">
                <div class="tabs">
                    <div class="tab-link active" data-tab="individual">Individual Donor</div>
                    <div class="tab-link" data-tab="blood-type">By Blood Type</div>
                    <div class="tab-link" data-tab="emergency">Emergency Alert</div>
                </div>

                <!-- Individual Donor Tab -->
                <div id="individual" class="tab-content active">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="donor_id" class="form-label">Select Donor</label>
                            <select name="donor_id" id="donor_id" class="form-control" required>
                                <option value="">-- Select a donor --</option>
                                <?php while ($donor = $donors_result->fetch_assoc()): ?>
                                    <option value="<?php echo $donor['donor_id']; ?>">
                                        <?php echo $donor['donor_name']; ?> 
                                        <span class="blood-type-badge"><?php echo $donor['donor_blood_type']; ?></span>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="individual_message" class="form-label">Message</label>
                            <div class="templates">
                                <span class="template-btn" data-target="individual_message" data-template="Hello from <?php echo $hospital_name; ?>. We need your blood type for an urgent patient. Please contact us at your earliest convenience.">Urgent Request</span>
                                <span class="template-btn" data-target="individual_message" data-template="<?php echo $hospital_name; ?> is running low on your blood type. Your donation would be greatly appreciated. Please schedule your next donation.">Donation Request</span>
                                <span class="template-btn" data-target="individual_message" data-template="Thank you for your recent blood donation at <?php echo $hospital_name; ?>. Your generosity helps save lives!">Thank You</span>
                            </div>
                            <textarea name="message" id="individual_message" class="form-control message-input" required maxlength="160"></textarea>
                            <div class="char-counter"><span id="individual_count">0</span>/160 characters</div>
                        </div>

                        <button type="submit" name="send_individual" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>

                <!-- By Blood Type Tab -->
                <div id="blood-type" class="tab-content">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="blood_type" class="form-label">Select Blood Type</label>
                            <select name="blood_type" id="blood_type" class="form-control" required>
                                <option value="">-- Select blood type --</option>
                                <?php 
                                // Reset the result pointer
                                $blood_types_result->data_seek(0);
                                while ($blood_type = $blood_types_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $blood_type['donor_blood_type']; ?>"><?php echo $blood_type['donor_blood_type']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="blood_type_message" class="form-label">Message</label>
                            <div class="templates">
                                <span class="template-btn" data-target="blood_type_message" data-template="Urgent: <?php echo $hospital_name; ?> needs your blood type for emergency cases. Please contact us if you can donate in the next 24 hours.">Emergency Request</span>
                                <span class="template-btn" data-target="blood_type_message" data-template="<?php echo $hospital_name; ?> is running low on your blood type. Your donation would be greatly appreciated. Please schedule your next donation.">Low Supply</span>
                                <span class="template-btn" data-target="blood_type_message" data-template="Blood Drive: <?php echo $hospital_name; ?> is hosting a blood drive on [DATE]. Your blood type is in high demand. Walk-ins welcome!">Blood Drive</span>
                            </div>
                            <textarea name="message" id="blood_type_message" class="form-control message-input" required maxlength="160"></textarea>
                            <div class="char-counter"><span id="blood_type_count">0</span>/160 characters</div>
                        </div>

                        <button type="submit" name="send_blood_type" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send to All Donors with Selected Blood Type
                        </button>
                    </form>
                </div>

                <!-- Emergency Alert Tab -->
                <div id="emergency" class="tab-content">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="emergency_message" class="form-label">Emergency Message (to ALL donors)</label>
                            <div class="templates">
                                <span class="template-btn" data-target="emergency_message" data-template="URGENT: <?php echo $hospital_name; ?> is experiencing a critical blood shortage. We need all blood types immediately. Please come to donate if you are eligible.">Critical Shortage</span>
                                <span class="template-btn" data-target="emergency_message" data-template="EMERGENCY ALERT: <?php echo $hospital_name; ?> requires immediate blood donations following a major incident. All blood types needed. Please help if you can.">Mass Casualty</span>
                                <span class="template-btn" data-target="emergency_message" data-template="URGENT APPEAL: <?php echo $hospital_name; ?> blood bank reserves are critically low. Your donation could save lives. Please respond to this emergency.">Urgent Appeal</span>
                            </div>
                            <textarea name="message" id="emergency_message" class="form-control message-input" required maxlength="160"></textarea>
                            <div class="char-counter"><span id="emergency_count">0</span>/160 characters</div>
                        </div>

                        <button type="submit" name="send_emergency" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle"></i> Send Emergency Alert to ALL Donors
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Notification Logs -->
        <div class="notification-card">
            <div class="card-header">
                <i class="fas fa-history"></i> Recent Notification History
            </div>
            <div class="card-body">
                <?php if ($logs_result->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th>Recipient</th>
                                    <th>Blood Type</th>
                                    <th>Message</th>
                                    <th>Sent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($log = $logs_result->fetch_assoc()): ?>
                                    <tr>
                                    <td><?php echo $log['donor_name']; ?></td>
                                    <td><?php echo $log['donor_blood_type']; ?></td>
                                    <td class="message-cell"><?php echo $log['message']; ?></td>
                                    <td class="timestamp"><?php echo date('M d, Y g:i A', strtotime($log['sent_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No notification logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $hospital_name; ?> Blood Donation Management System</p>
            <p>Helping save lives through blood donation</p>
        </div>
    </footer>

    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabLinks.forEach(function(tabLink) {
                tabLink.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs
                    tabLinks.forEach(function(link) {
                        link.classList.remove('active');
                    });
                    
                    tabContents.forEach(function(content) {
                        content.classList.remove('active');
                    });
                    
                    // Add active class to current tab
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Message template functionality
            const templateButtons = document.querySelectorAll('.template-btn');
            templateButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const template = this.getAttribute('data-template');
                    const targetElement = document.getElementById(targetId);
                    
                    targetElement.value = template;
                    updateCharCount(targetElement);
                });
            });
            
            // Character counter functionality
            const messageInputs = document.querySelectorAll('.message-input');
            messageInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    updateCharCount(this);
                });
            });
            
            function updateCharCount(element) {
                const counterId = element.id + '_count';
                const counter = document.getElementById(counterId);
                const charCount = element.value.length;
                const maxLength = parseInt(element.getAttribute('maxlength'));
                
                counter.textContent = charCount;
                
                const counterElement = counter.parentElement;
                counterElement.classList.remove('warning', 'danger');
                
                if (charCount > maxLength * 0.9) {
                    counterElement.classList.add('danger');
                } else if (charCount > maxLength * 0.7) {
                    counterElement.classList.add('warning');
                }
            }
        });
    </script>
</body>
</html>