<?php
// Start session
session_start();

// Check if recipient is logged in
if (!isset($_SESSION['recipient_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../includes/db_connection.php';

// Get recipient data
$recipient_id = $_SESSION['recipient_id'];
$sql = "SELECT * FROM recipients WHERE recipient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Recipient not found");
}

$recipient = $result->fetch_assoc();

// Handle form submission for profile update
$update_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['recipient_name'];
    $email = $_POST['recipient_email'];
    $phone = $_POST['recipient_phone'];
    $blood_type = $_POST['recipient_blood_type'];
    
    // Update recipient information
    $update_sql = "UPDATE recipients SET recipient_name = ?, recipient_email = ?, 
                  recipient_phone = ?, recipient_blood_type = ? 
                  WHERE recipient_id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $name, $email, $phone, $blood_type, $recipient_id);
    
    if ($update_stmt->execute()) {
        $update_message = "Profile updated successfully!";
        // Refresh recipient data
        $stmt->execute();
        $result = $stmt->get_result();
        $recipient = $result->fetch_assoc();
    } else {
        $update_message = "Error updating profile: " . $conn->error;
    }
}

// Get blood request history
$requests_sql = "SELECT br.*, h.hospital_name 
                FROM blood_requests br 
                JOIN hospitals h ON br.hospital_id = h.hospital_id 
                WHERE br.recipient_id = ? 
                ORDER BY br.request_date DESC";
$requests_stmt = $conn->prepare($requests_sql);
$requests_stmt->bind_param("i", $recipient_id);
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Profile</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    /* Red and White Theme for Blood Donation System - Enhanced for Recipient Profile */
:root {
  --primary-color: #e74c3c;
  --primary-dark: #c0392b;
  --primary-light: #f9ebea;
  --accent-color: #3498db;
  --text-color: #333333;
  --text-light: #777777;
  --text-white: #ffffff;
  --bg-color: #ffffff;
  --bg-light: #f8f9fa;
  --border-color: #eeeeee;
  --shadow-sm: 0 2px 5px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 10px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 8px 20px rgba(0, 0, 0, 0.1);
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --transition: all 0.3s ease;
  --success-color: #2ecc71;
  --success-dark: #27ae60;
  --info-color: #3498db;
  --info-dark: #2980b9;
  --secondary-color: #95a5a6;
  --secondary-dark: #7f8c8d;
}

/* General Layout */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: var(--text-color);
  background-color: #f5f5f5;
  margin: 0;
  padding: 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

h1, h2, h3, h4, h5, h6 {
  color: var(--text-color);
  margin-top: 0;
}

h1 {
  font-size: 2.2rem;
  color: var(--primary-color);
  border-bottom: 2px solid var(--primary-light);
  padding-bottom: 10px;
  margin-bottom: 25px;
}

/* Card Styles */
.card {
  background-color: var(--bg-color);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  padding: 25px;
  margin-bottom: 25px;
  transition: var(--transition);
  border-top: 4px solid var(--primary-color);
  animation: fadeIn 0.5s ease;
}

.card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-2px);
}

.card h2 {
  font-size: 1.5rem;
  margin-bottom: 20px;
  color: var(--primary-color);
}

/* Tab Navigation */
.tab-nav {
  display: flex;
  margin-bottom: 25px;
  border-bottom: 2px solid var(--border-color);
  position: relative;
}

.tab-button {
  padding: 12px 25px;
  background-color: transparent;
  border: none;
  border-radius: var(--radius-sm) var(--radius-sm) 0 0;
  cursor: pointer;
  font-weight: 500;
  transition: var(--transition);
  color: var(--text-light);
  position: relative;
  overflow: hidden;
  margin-right: 5px;
}

.tab-button:hover {
  color: var(--primary-color);
  background-color: var(--primary-light);
}

.tab-button.active {
  color: var(--primary-color);
  font-weight: 600;
}

.tab-indicator {
  position: absolute;
  bottom: -2px;
  height: 3px;
  background-color: var(--primary-color);
  transition: var(--transition);
}

.tab-content > div {
  display: none;
  animation: fadeIn 0.4s ease;
}

.tab-content > div.active {
  display: block;
  animation: fadeIn 0.4s ease, slideUp 0.5s ease;
}

.tab-content.transitioning {
  opacity: 0.6;
}

/* Profile Section */
.profile-container {
  display: grid;
  grid-template-columns: 1fr;
  gap: 25px;
  animation: fadeIn 0.5s ease;
}

@media (min-width: 768px) {
  .profile-container {
    grid-template-columns: 2fr 1fr;
  }
}

.profile-info {
  position: relative;
  overflow: hidden;
}

.profile-info.view-mode .edit-profile {
  display: none;
}

.profile-info.edit-mode .view-profile {
  display: none;
}

.profile-info.animating .view-profile,
.profile-info.animating .edit-profile {
  opacity: 0;
  transform: translateX(20px);
}

.view-profile, .edit-profile {
  transition: var(--transition);
}

/* Blood Type Display */
.blood-type-display {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100px;
  height: 100px;
  font-size: 2.2rem;
  font-weight: bold;
  color: var(--text-white);
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
  box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
  margin: 0 auto 25px;
  transition: var(--transition);
}

.blood-type-display:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
  animation: pulse 1.5s infinite;
}

/* Profile Fields */
.profile-field {
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid var(--border-color);
}

.profile-field:last-child {
  border-bottom: none;
}

.profile-field label {
  display: block;
  color: var(--text-light);
  font-size: 0.9rem;
  margin-bottom: 5px;
}

.profile-value {
  font-weight: 500;
  color: var(--text-color);
}

.profile-actions {
  margin-top: 25px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

/* Form Elements */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: var(--text-color);
  font-weight: 500;
}

input, select {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background-color: #f9f9f9;
  color: var(--text-color);
  font-size: 1rem;
  transition: var(--transition);
}

input:focus, select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
  background-color: var(--bg-color);
}

input.invalid-input, select.invalid-input {
  border-color: var(--primary-color);
  background-color: rgba(231, 76, 60, 0.05);
}

.error-message {
  color: var(--primary-color);
  font-size: 0.85rem;
  margin-top: 5px;
  display: block;
  animation: fadeIn 0.3s ease;
}

/* Buttons */
.btn {
  display: inline-block;
  padding: 10px 20px;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  transition: var(--transition);
  box-shadow: var(--shadow-sm);
  margin-right: 8px;
  margin-bottom: 5px;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.btn-primary {
  background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
  color: var(--text-white);
}

.btn-primary:hover {
  background: linear-gradient(to right, var(--primary-dark), var(--primary-dark));
}

.btn-danger {
  background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
  color: var(--text-white);
}

.btn-danger:hover {
  background: linear-gradient(to right, var(--primary-dark), var(--primary-dark));
}

.btn-success {
  background: linear-gradient(to right, var(--success-color), var(--success-dark));
  color: var(--text-white);
}

.btn-success:hover {
  background: linear-gradient(to right, var(--success-dark), var(--success-dark));
}

.btn-secondary {
  background: linear-gradient(to right, var(--secondary-color), var(--secondary-dark));
  color: var(--text-white);
}

.btn-secondary:hover {
  background: linear-gradient(to right, var(--secondary-dark), var(--secondary-dark));
}

.btn-info {
  background: linear-gradient(to right, var(--info-color), var(--info-dark));
  color: var(--text-white);
}

.btn-info:hover {
  background: linear-gradient(to right, var(--info-dark), var(--info-dark));
}

.btn-sm {
  padding: 6px 12px;
  font-size: 0.85rem;
}

/* Table Styles */
.table-responsive {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.table th {
  background-color: var(--primary-light);
  padding: 12px 15px;
  text-align: left;
  font-weight: 600;
  color: var(--text-color);
  border-bottom: 2px solid var(--primary-color);
}

.table td {
  padding: 12px 15px;
  border-bottom: 1px solid var(--border-color);
  transition: var(--transition);
}

.table tr {
  transition: background-color 0.3s ease;
}

.table tr:hover td {
  background-color: rgba(231, 76, 60, 0.05);
}

/* Alert Messages */
.alert {
  padding: 15px;
  border-radius: var(--radius-sm);
  margin-bottom: 20px;
  position: relative;
  animation: slideDown 0.4s ease;
}

.alert::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: rgba(255,255,255,0.5);
  width: 100%;
  animation: alert-timer 5s linear forwards;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border-left: 4px solid #28a745;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
  border-left: 4px solid var(--primary-color);
}

/* No Records Message */
.no-records {
  text-align: center;
  padding: 30px 0;
  color: var(--text-light);
}

.no-records a {
  margin-top: 15px;
  display: inline-block;
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes slideDown {
  from { transform: translateY(-20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

@keyframes alert-timer {
  from { width: 100%; }
  to { width: 0%; }
}

/* Custom Dialog Styling */
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.dialog {
  background-color: var(--bg-color);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  width: 90%;
  max-width: 400px;
  padding: 25px;
  animation: slideUp 0.3s ease;
}

.dialog-title {
  margin-top: 0;
  color: var(--primary-color);
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 15px;
  margin-bottom: 15px;
}

.dialog-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 25px;
}

/* Media Queries */
@media (max-width: 992px) {
  .container {
    padding: 15px;
  }
  
  h1 {
    font-size: 1.8rem;
  }
}

@media (max-width: 576px) {
  .profile-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    margin-bottom: 10px;
  }
  
  .tab-button {
    padding: 10px 15px;
    font-size: 0.9rem;
  }
  
  .blood-type-display {
    width: 80px;
    height: 80px;
    font-size: 1.8rem;
  }
}
   </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($recipient['recipient_name']); ?>!</h1>
        
        <?php if (!empty($update_message)): ?>
            <div class="alert <?php echo strpos($update_message, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $update_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-header">
            <!-- Profile picture moved outside and to the right side as a square -->
            <div class="tab-nav">
                <button class="tab-button active" data-tab="profile">Profile</button>
                <button class="tab-button" data-tab="requests">Blood Requests</button>
            </div>
            
            <div class="profile-picture-container">
                <?php 
                $profile_pic = "../uploads/profile_pictures/" . htmlspecialchars($recipient['profile_picture']);
                if (!file_exists($profile_pic)) {
                    $profile_pic = "../uploads/profile_pictures/default_avatar.png";
                }
                ?>
                <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-picture">
                <div class="blood-type-display">
                    <?php echo htmlspecialchars($recipient['recipient_blood_type']); ?>
                </div>
            </div>
        </div>
        
        <div class="tab-content">
            <div id="profile-tab" class="active">
                <div class="profile-container">
                    <div class="profile-info card">
                        <h2>Your Profile</h2>
                        
                        <div class="view-profile view-mode">
                            <div class="profile-field">
                                <label>Name:</label>
                                <div class="profile-value"><?php echo htmlspecialchars($recipient['recipient_name']); ?></div>
                            </div>
                            
                            <div class="profile-field">
                                <label>Email:</label>
                                <div class="profile-value"><?php echo htmlspecialchars($recipient['recipient_email']); ?></div>
                            </div>
                            
                            <div class="profile-field">
                                <label>Phone:</label>
                                <div class="profile-value"><?php echo htmlspecialchars($recipient['recipient_phone']); ?></div>
                            </div>
                            
                            <div class="profile-actions">
                                <button id="edit-profile-btn" class="btn btn-primary">Edit Profile</button>
                                <a href="recipient_search_blood.php" class="btn btn-info">Search Blood</a>
                            </div>
                        </div>
                        
                        <div class="edit-profile edit-mode">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="recipient_name">Name:</label>
                                    <input type="text" id="recipient_name" name="recipient_name" value="<?php echo htmlspecialchars($recipient['recipient_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="recipient_email">Email:</label>
                                    <input type="email" id="recipient_email" name="recipient_email" value="<?php echo htmlspecialchars($recipient['recipient_email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="recipient_phone">Phone:</label>
                                    <input type="text" id="recipient_phone" name="recipient_phone" value="<?php echo htmlspecialchars($recipient['recipient_phone']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="recipient_blood_type">Blood Type:</label>
                                    <select id="recipient_blood_type" name="recipient_blood_type" required>
                                        <?php
                                        $blood_types = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                                        foreach ($blood_types as $type) {
                                            $selected = ($recipient['recipient_blood_type'] == $type) ? 'selected' : '';
                                            echo "<option value=\"$type\" $selected>$type</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="profile-actions">
                                    <input type="submit" name="update_profile" value="Save Changes" class="btn btn-success">
                                    <button type="button" id="cancel-edit-btn" class="btn btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="requests-tab">
                <div class="profile-container">
                    <div class="card">
                        <h2>Your Blood Requests</h2>
                        <div class="profile-actions">
                            <a href="recipient_search_blood.php" class="btn btn-info">Search Blood</a>
                        </div>
                        <?php if ($requests_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Hospital</th>
                                            <th>Blood Type</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($request = $requests_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $request['request_id']; ?></td>
                                                <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                                <td><?php echo $request['blood_type']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-records">No blood requests found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM elements
    const profileInfo = document.querySelector('.profile-info');
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content > div');
    const bloodTypeDisplay = document.querySelector('.blood-type-display');
    
    // Apply visual enhancements to the UI
    applyVisualEnhancements();
    
    // Function to toggle profile edit mode with animation
    function toggleEditMode(showEdit) {
        if (showEdit) {
            profileInfo.classList.add('animating');
            setTimeout(() => {
                profileInfo.classList.remove('view-mode');
                profileInfo.classList.add('edit-mode');
                
                // Focus the first input field
                const firstInput = profileInfo.querySelector('input[type="text"]');
                if (firstInput) firstInput.focus();
                
                setTimeout(() => profileInfo.classList.remove('animating'), 10);
            }, 300);
        } else {
            profileInfo.classList.add('animating');
            setTimeout(() => {
                profileInfo.classList.remove('edit-mode');
                profileInfo.classList.add('view-mode');
                setTimeout(() => profileInfo.classList.remove('animating'), 10);
            }, 300);
        }
    }
    
    // Function to handle tab switching with smooth transitions
    function switchTab(tabId) {
        // Add transition class to container
        const tabContentContainer = document.querySelector('.tab-content');
        tabContentContainer.classList.add('transitioning');
        
        // After a short delay, change the active tab
        setTimeout(() => {
            // Remove active class from all buttons and tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Add active class to selected tab and content
            const selectedBtn = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
            if (selectedBtn) {
                selectedBtn.classList.add('active');
                // Add dynamic underline effect
                updateActiveTabIndicator(selectedBtn);
                
                const tabContentId = `${tabId}-tab`;
                const tabContent = document.getElementById(tabContentId);
                if (tabContent) {
                    tabContent.classList.add('active');
                    // Animate elements inside the tab
                    animateTabContent(tabContent);
                }
            }
            
            // Remove transition class after animation completes
            setTimeout(() => {
                tabContentContainer.classList.remove('transitioning');
            }, 300);
        }, 150);
    }
    
    // Function to animate tab content elements
    function animateTabContent(tabContent) {
        const elements = tabContent.querySelectorAll('.card, .table, tr');
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            setTimeout(() => {
                el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 50 * (index + 1));
        });
    }
    
    // Function to update the active tab indicator
    function updateActiveTabIndicator(activeButton) {
        const tabNav = document.querySelector('.tab-nav');
        
        // Create or get the indicator
        let indicator = document.querySelector('.tab-indicator');
        if (!indicator) {
            indicator = document.createElement('span');
            indicator.className = 'tab-indicator';
            tabNav.appendChild(indicator);
        }
        
        // Position the indicator
        const buttonRect = activeButton.getBoundingClientRect();
        const navRect = tabNav.getBoundingClientRect();
        
        indicator.style.width = `${buttonRect.width}px`;
        indicator.style.left = `${buttonRect.left - navRect.left}px`;
    }
    
    // Function to apply visual enhancements to the UI
    function applyVisualEnhancements() {
        // Add CSS for animations and visual improvements
        const style = document.createElement('style');
        style.textContent = `
            /* General animations */
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
            @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
            
            /* Profile header layout */
            .profile-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
                position: relative;
            }
            
            .tab-nav {
                flex-grow: 1;
                position: relative;
                border-bottom: 2px solid #eee;
            }
            
            /* Container transitions */
            .tab-content { transition: opacity 0.3s ease-in-out; }
            .tab-content.transitioning { opacity: 0.6; }
            
            /* Profile picture - now as a square on the right */
.profile-picture-container {
    width: 220px;
    margin-left: 20px;
    text-align: center;
}

.profile-picture {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border: 4px solid #e74c3c;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    border-radius: 8px; /* Square with slightly rounded corners */
    margin: 0; /* Remove any margin that might create unwanted space */
    padding: 0; /* Remove any padding that might create unwanted space */
}
            
            .profile-picture:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            }
            
            /* Tab navigation */
            .tab-button { position: relative; padding: 12px 20px; margin-right: 5px; background: transparent; border: none; border-radius: 4px 4px 0 0; cursor: pointer; transition: all 0.3s ease; }
            .tab-button:hover { background: rgba(0,0,0,0.05); }
            .tab-button.active { color: #e74c3c; font-weight: bold; }
            .tab-indicator { position: absolute; bottom: -2px; height: 3px; background-color: #e74c3c; transition: all 0.3s ease; }
            
            /* Cards */
            .card { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px; transition: all 0.3s ease; }
            .card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.15); }
            
            /* Profile view/edit transitions */
            .profile-info { position: relative; overflow: hidden; transition: all 0.3s ease; }
            .profile-info.animating .view-profile, .profile-info.animating .edit-profile { opacity: 0; transform: translateX(20px); }
            .view-profile, .edit-profile { transition: all 0.3s ease; }
            
            /* Blood type display */
            /* Blood type display */
            /* Profile container adjustments to move the box higher */
.profile-container {
    animation: fadeIn 0.5s ease;
    margin-top: -280px; /* Add negative margin to move the box up */
}

/* Apply the same margin-top to requests-tab */
#requests-tab {
    animation: fadeIn 0.5s ease;
    margin-top: -280px; /* Same negative margin as profile-container */
}

/* Adjust spacing between elements */
.tab-content {
    transition: opacity 0.3s ease-in-out;
    margin-top: 0; /* Remove any top margin */
}

/* Adjust profile header to reduce bottom spacing */
.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0; /* Reduce the bottom margin */
    position: relative;
}

/* Also ensure the profile picture remains properly sized and positioned */
.profile-picture-container {
    width: 300px;
    margin-left: 20px;
    text-align: left;
}

.profile-picture {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border: 4px solid #e74c3c;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 0;
    padding: 0;
}

/* Add compact card styling to reduce internal padding */
.card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 8px;
    transition: all 0.3s ease;
    padding: 15px; /* Reduce internal padding */
    margin-top: 10px; /* Reduce top margin */
}
.blood-type-display { 
    display: inline-block; 
    font-size: 2.5rem; 
    font-weight: bold; 
    padding: 15px 20px; 
    border-radius: 50%; 
    background: linear-gradient(135deg, #e74c3c, #c0392b); 
    color: white; 
    box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
    margin-top: 10px;
    margin-bottom: 0;
    transition: all 0.3s ease;
}
            .blood-type-display:hover { transform: scale(1.05); box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4); }
            
            /* Table styling */
            .table { border-collapse: separate; border-spacing: 0; width: 100%; }
            .table th { background-color: #f8f8f8; padding: 12px; }
            .table td { padding: 12px; border-top: 1px solid #eee; transition: all 0.2s ease; }
            .table tr:hover td { background-color: #f9f9f9; }
            
            /* Buttons */
            .btn { 
                transition: all 0.2s ease; 
                border-radius: 4px; 
                padding: 8px 16px; 
                border: none; 
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                margin-right: 8px;
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
            .btn-primary { background: linear-gradient(to right, #3498db, #2980b9); color: white; }
            .btn-danger { background: linear-gradient(to right, #e74c3c, #c0392b); color: white; }
            .btn-success { background: linear-gradient(to right, #2ecc71, #27ae60); color: white; }
            .btn-info { background: linear-gradient(to right, #1abc9c, #16a085); color: white; }
            .btn-secondary { background: #95a5a6; color: white; }
            
            /* Form elements */
            input, select { 
                padding: 10px; 
                border: 1px solid #ddd; 
                border-radius: 4px; 
                transition: all 0.2s ease;
                width: 100%;
            }
            input:focus, select:focus { 
                outline: none; 
                border-color: #3498db; 
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2); 
            }
            
            /* Alert messages */
            .alert {
                padding: 12px 15px;
                border-radius: 4px;
                margin-bottom: 20px;
                animation: slideUp 0.5s ease;
                position: relative;
                overflow: hidden;
            }
            .alert::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(255,255,255,0.5);
                width: 100%;
                animation: alert-timer 5s linear forwards;
            }
            @keyframes alert-timer {
                from { width: 100%; }
                to { width: 0%; }
            }
            .alert-success { background-color: #d4edda; color: #155724; border-left: 4px solid #28a745; }
            .alert-error { background-color: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
            
            /* Entry animations */
            .profile-container { animation: fadeIn 0.5s ease; }
            
            /* Profile actions */
            .profile-actions {
                margin-top: 15px;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            /* Profile picture container - modified to handle missing photos */
.profile-picture-container {
    width: 220px;
    margin-left: 20px;
    text-align: center;
    min-height: 200px; /* Ensure consistent height */
}

/* Default avatar styling */
.profile-picture {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border: 4px solid #e74c3c;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    border-radius: 8px;
    background-color: #f5f5f5; /* Add background color for default avatar */
    display: block; /* Ensure it takes up space even if empty */
}

/* Adjust profile header layout */
.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    position: relative;
    min-height: 240px; /* Ensure consistent height with or without photo */
}

/* Make tab nav take remaining space */
.tab-nav {
    flex-grow: 1;
    position: relative;
    border-bottom: 2px solid #eee;
    margin-right: 20px; /* Space between tabs and profile picture */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
    }
    .profile-picture-container {
        margin: 20px auto 0;
        width: 100%;
    }
    .tab-nav {
        width: 100%;
        margin-right: 0;
    }
}
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .profile-header {
                    flex-direction: column;
                }
                .profile-picture-container {
                    margin: 20px auto;
                    width: 100%;
                }
                .tab-nav {
                    width: 100%;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Add pulsing effect to blood type display
        if (bloodTypeDisplay) {
            bloodTypeDisplay.addEventListener('mouseover', function() {
                this.style.animation = 'pulse 1s ease infinite';
            });
            bloodTypeDisplay.addEventListener('mouseout', function() {
                this.style.animation = '';
            });
        }
        
        // Set up the active tab indicator on initial load
        const activeTab = document.querySelector('.tab-button.active');
        if (activeTab) {
            updateActiveTabIndicator(activeTab);
        }
    }
    
    // Set initial mode
    toggleEditMode(false);
    
    // Add event listeners
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleEditMode(true);
        });
    }
    
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleEditMode(false);
        });
    }
    
    // Tab navigation with event delegation
    const tabNav = document.querySelector('.tab-nav');
    if (tabNav) {
        tabNav.addEventListener('click', function(e) {
            const button = e.target.closest('.tab-button');
            if (button) {
                const tabId = button.getAttribute('data-tab');
                switchTab(tabId);
                
                // Update URL with hash for better navigation
                history.pushState(null, null, `#${tabId}`);
            }
        });
    }
    
    // Check URL hash on page load to set active tab
    if (window.location.hash) {
        const tabId = window.location.hash.substring(1);
        switchTab(tabId);
    } else {
        // Animate initial tab content
        const activeTabContent = document.querySelector('.tab-content > div.active');
        if (activeTabContent) {
            animateTabContent(activeTabContent);
        }
    }
    
    // Form validation for profile form with visual feedback
    const profileForm = document.querySelector('.edit-profile form');
    if (profileForm) {
        // Add input event listeners for real-time validation
        const inputs = profileForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                validateInput(this);
            });
            
            input.addEventListener('blur', function() {
                validateInput(this, true);
            });
        });
        
        profileForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate all inputs
            inputs.forEach(input => {
                if (!validateInput(input, true)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to the first invalid input
                const firstInvalid = profileForm.querySelector('.invalid-input');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
    
    // Function to validate input with visual feedback
    function validateInput(input, showError = false) {
        let isValid = true;
        let errorMessage = '';
        
        // Remove existing error message
        const existingError = input.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Remove invalid class
        input.classList.remove('invalid-input');
        
        // Check required fields
        if (input.hasAttribute('required') && !input.value.trim()) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Validate email
        if (input.type === 'email' && input.value.trim()) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(input.value.trim())) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        
        // Validate phone
        if (input.id === 'recipient_phone' && input.value.trim()) {
            const phonePattern = /^\d{10,15}$/;
            if (!phonePattern.test(input.value.replace(/[\s-]/g, ''))) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number (10-15 digits)';
            }
        }
        
        // Show error message if needed
        if (!isValid && showError) {
            input.classList.add('invalid-input');
            
            const errorSpan = document.createElement('span');
            errorSpan.className = 'error-message';
            errorSpan.textContent = errorMessage;
            errorSpan.style.color = '#e74c3c';
            errorSpan.style.fontSize = '0.8rem';
            errorSpan.style.display = 'block';
            errorSpan.style.marginTop = '5px';
            errorSpan.style.animation = 'fadeIn 0.3s ease';
            
            input.parentNode.appendChild(errorSpan);
        }
        
        return isValid;
    }
    
    // Add auto-close functionality to alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    }
});
</script>
</body>
</html>