<?php 
session_start(); 

// Check if the user is logged in as a hospital staff member
if (!isset($_SESSION['hospital_id'])) {     
    header("Location: hospital_login.php"); // Redirect to hospital login if not logged in     
    exit(); 
}  

// Include the database connection
include('../includes/db_connection.php'); // Assuming you have this file to connect to the database  

$hospital_id = $_SESSION['hospital_id']; // Get the hospital's ID from the session  

// Query to get the hospital's information
$hospital_query = "SELECT hospital_name FROM hospitals WHERE hospital_id = $hospital_id";
$hospital_result = mysqli_query($conn, $hospital_query);
$hospital_data = mysqli_fetch_assoc($hospital_result);
$hospital_name = $hospital_data ? $hospital_data['hospital_name'] : 'Your Hospital';

// Query to get the hospital's blood donation information
$donations_query = "SELECT donor_id, donor_name, donor_blood_type, donor_phone, donor_email, last_donation_date FROM donors WHERE hospital_id = $hospital_id ORDER BY last_donation_date DESC"; 
$donations_result = mysqli_query($conn, $donations_query);

// Count total donors
$count_query = "SELECT COUNT(*) as total_donors FROM donors WHERE hospital_id = $hospital_id";
$count_result = mysqli_query($conn, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total_donors = $count_data['total_donors'];

// Count donors by blood type
$blood_types_query = "SELECT donor_blood_type, COUNT(*) as count FROM donors WHERE hospital_id = $hospital_id GROUP BY donor_blood_type";
$blood_types_result = mysqli_query($conn, $blood_types_query);
$blood_types_data = [];
while ($row = mysqli_fetch_assoc($blood_types_result)) {
    $blood_types_data[$row['donor_blood_type']] = $row['count'];
}

// Modified: Get recipient blood requests that match the hospital's available blood types
$search_history_query = "
    SELECT br.request_id, r.recipient_id, r.recipient_name, br.blood_type, 
           r.recipient_latitude as search_latitude, r.recipient_longitude as search_longitude, 
           br.request_date as search_datetime, r.recipient_phone,
           CASE WHEN DATEDIFF(NOW(), br.request_date) < 1 THEN 'urgent' ELSE 'normal' END as urgency,
           (6371 * acos(cos(radians(r.recipient_latitude)) * cos(radians(h.hospital_latitude)) * 
           cos(radians(h.hospital_longitude) - radians(r.recipient_longitude)) + 
           sin(radians(r.recipient_latitude)) * sin(radians(h.hospital_latitude)))) AS distance
    FROM blood_requests br
    JOIN recipients r ON br.recipient_id = r.recipient_id
    JOIN hospitals h ON h.hospital_id = $hospital_id
    WHERE br.blood_type IN (
        SELECT DISTINCT donor_blood_type 
        FROM donors 
        WHERE hospital_id = $hospital_id
    )
    AND br.request_status = 'pending'
    ORDER BY br.request_date DESC
    LIMIT 20";
$search_history_result = mysqli_query($conn, $search_history_query);

// Modified: Count recent blood requests for blood types the hospital has
$recent_searches_query = "
    SELECT COUNT(*) as count 
    FROM blood_requests 
    WHERE blood_type IN (
        SELECT DISTINCT donor_blood_type 
        FROM donors 
        WHERE hospital_id = $hospital_id
    )
    AND request_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    AND request_status = 'pending'";
$recent_searches_result = mysqli_query($conn, $recent_searches_query);
$recent_searches_data = mysqli_fetch_assoc($recent_searches_result);
$recent_searches_count = $recent_searches_data['count'];


?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">     
    <title>Hospital Dashboard | Blood Availability System</title>
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

/* Dashboard Stats */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    text-align: center;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.stat-card-icon {
    font-size: 2.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.stat-card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.stat-card-label {
    color: #666;
    font-size: 0.95rem;
}

/* Highlight Card Styles for Blood Requests and Matches */
.highlight-card {
    border-left: 4px solid var(--accent);
}

.highlight-card .stat-card-icon {
    color: var(--accent);
}

/* Blood Type Distribution */
.blood-type-distribution {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.blood-type-card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1rem;
    text-align: center;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.blood-type-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: var(--primary);
}

.blood-type-label {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.blood-type-count {
    font-size: 1.25rem;
    font-weight: 600;
}

.blood-type-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

/* Dashboard Section */
.dashboard-section {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 2rem;
    margin-bottom: 2rem;
}

.dashboard-section h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    position: relative;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dashboard-section h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background-color: var(--primary);
}

.section-desc {
    color: #666;
    margin-bottom: 1.5rem;
    font-size: 1rem;
}

/* Hospital Info */
.hospital-info {
    background-color: #f8f9fa;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid var(--primary);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.hospital-icon {
    font-size: 2rem;
    color: var(--primary);
}

.hospital-details h3 {
    font-size: 1.4rem;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
}

.hospital-details p {
    color: #666;
    font-size: 0.95rem;
}

/* Table Styling */
.table-container {
    overflow-x: auto;
    margin-top: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.donations-table, .search-history-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
}

.donations-table thead, .search-history-table thead {
    background-color: #f5f5f5;
}

.donations-table th, .search-history-table th {
    text-align: left;
    padding: 1rem;
    font-weight: 600;
    color: #555;
    border-bottom: 2px solid #ddd;
    white-space: nowrap;
}

.donations-table td, .search-history-table td {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.donations-table tr:last-child td, .search-history-table tr:last-child td {
    border-bottom: none;
}

.donations-table tr:hover td, .search-history-table tr:hover td {
    background-color: #f9f9f9;
}

.donor-name, .recipient-name {
    font-weight: 600;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.donor-phone, .donor-email {
    color: var(--accent);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.donor-phone:hover, .donor-email:hover {
    text-decoration: underline;
}

.donation-date, .search-time {
    white-space: nowrap;
    color: #666;
}

.search-time small {
    display: block;
    font-size: 0.8rem;
    color: #888;
    margin-top: 0.25rem;
}

/* Recent Search Highlighting */
.recent-search td {
    background-color: #fff8e1;
}

/* Blood Type Badge */
.blood-type-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    background-color: var(--primary-light);
    color: var(--primary-dark);
    font-weight: 600;
    text-align: center;
}

/* Urgency Badges */
.urgency-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    background-color: #e0e0e0;
    color: #555;
    font-weight: 600;
    text-align: center;
}

.urgency-badge.urgent {
    background-color: #ffecb3;
    color: #ff6f00;
}

.urgency-badge.emergency {
    background-color: #ffcdd2;
    color: #c62828;
}

/* New Badge */
.badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}

.new-badge {
    background-color: var(--accent);
    color: white;
}

/* Form Styling */
form {
    display: inline-block;
}

.btn-delete, .btn-edit {
    border: none;
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-delete {
    background-color: var(--error);
    color: white;
}

.btn-delete:hover {
    background-color: #c62828;
}

.btn-edit {
    background-color: var(--accent);
    color: white;
    margin-right: 0.5rem;
}

.btn-edit:hover {
    background-color: #1565c0;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Add Donor Button */
.add-donor-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: var(--success);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-decoration: none;
}

.add-donor-btn:hover {
    background-color: #388e3c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* View All Link */
.view-all-link {
    text-align: center;
    margin-top: 1.5rem;
}

.btn-view-all {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: var(--accent);
    color: white;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    transition: var(--transition);
}

.btn-view-all:hover {
    background-color: #1565c0;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2.5rem 2rem;
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    border: 2px dashed #ddd;
    margin: 2rem 0;
}

.empty-state-icon {
    font-size: 3.5rem;
    color: #ccc;
    margin-bottom: 1.5rem;
}

.empty-state-text {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 1.5rem;
}

/* Notification Styling */
.notification {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    max-width: 450px;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 1000;
}

.notification.show {
    transform: translateY(0);
    opacity: 1;
}

.notification i {
    font-size: 2rem;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.notification-message {
    color: #555;
    font-size: 0.95rem;
}

.notification-close {
    background: none;
    border: none;
    color: #888;
    cursor: pointer;
    font-size: 1rem;
    padding: 0.25rem;
    transition: color 0.2s ease;
}

.notification-close:hover {
    color: #333;
}

.urgent-notification {
    border-left: 4px solid var(--warning);
}

.urgent-notification i {
    color: var(--warning);
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

.footer-container a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: var(--transition);
}

.footer-container a:hover {
    color: white;
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 992px) {
    .dashboard-stats {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .blood-type-distribution {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 768px) {
    .header-container h1 {
        font-size: 2rem;
    }

    .dashboard-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .blood-type-distribution {
        grid-template-columns: repeat(4, 1fr);
    }

    .dashboard-section {
        padding: 1.5rem;
    }

    .donations-table th, 
    .donations-table td,
    .search-history-table th,
    .search-history-table td {
        padding: 0.75rem;
    }

    .main-nav ul {
        justify-content: space-around;
    }

    .notification {
        max-width: 85%;
        right: 20px;
        bottom: 20px;
    }
}

@media (max-width: 576px) {
    .header-container h1 {
        font-size: 1.8rem;
    }
    
    .dashboard-stats {
        grid-template-columns: 1fr;
    }

    .blood-type-distribution {
        grid-template-columns: repeat(2, 1fr);
    }

    .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn-edit, .btn-delete {
        width: 100%;
        justify-content: center;
        margin-right: 0;
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
    
    .notification {
        left: 20px;
        right: 20px;
        max-width: unset;
    }
}
    </style>
</head> 
<body>     
    <header class="main-header">         
        <div class="header-container">             
            <h1><i class="fas fa-hospital"></i> Bloodbank</h1>             
            <p>Welcome to your hospital's blood donation management center</p>         
        </div>     
    </header>      
    
    <nav class="main-nav">         
        <ul>                         
            <li><a href="hospital_add_donor.php"><i class="fas fa-user-plus"></i> Add Donor</a></li>             
            <li><a href="send_sms.php"><i class="fas fa-bell"></i> Send Notifications</a></li>    
            <li><a href="blood_inventory.php"><i class="fas fa-vial"></i> Blood Inventory</a></li>             
            <li><a href="hospital_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>         
        </ul>     
    </nav>      
    
    <div class="container">
        <!-- Hospital Info -->
        <div class="hospital-info">
            <div class="hospital-icon">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="hospital-details">
                <h3><?php echo $hospital_name; ?></h3>
                <p>Manage your blood donation inventory and donor information</p>
            </div>
        </div>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-value"><?php echo $total_donors; ?></div>
                <div class="stat-card-label">Total Donors</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-card-value">
                    <?php 
                    $recent_query = "SELECT COUNT(*) as count FROM donors WHERE hospital_id = $hospital_id AND last_donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    $recent_result = mysqli_query($conn, $recent_query);
                    $recent_data = mysqli_fetch_assoc($recent_result);
                    echo $recent_data['count'];
                    ?>
                </div>
                <div class="stat-card-label">Recent Donations (30 days)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon">
                    <i class="fas fa-tint"></i>
                </div>
                <div class="stat-card-value">
                    <?php echo count($blood_types_data); ?>
                </div>
                <div class="stat-card-label">Blood Types Available</div>
            </div>
            
            <!-- Modified: Card for blood requests -->
            <div class="stat-card highlight-card">
                <div class="stat-card-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-card-value"><?php echo $recent_searches_count; ?></div>
                <div class="stat-card-label">Recent Blood Requests (24h)</div>
            </div>
            
        
        </div>
        
        <!-- Blood Type Distribution -->
        <div class="dashboard-section">
            <h2><i class="fas fa-chart-pie"></i> Blood Type Distribution</h2>
            
            <div class="blood-type-distribution">
                <?php 
                $all_blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($all_blood_types as $type) {
                    $count = isset($blood_types_data[$type]) ? $blood_types_data[$type] : 0;
                    ?>
                    <div class="blood-type-card">
                        <div class="blood-type-label"><?php echo $type; ?></div>
                        <div class="blood-type-count"><?php echo $count; ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>
        
        <!-- Modified: Recent Blood Requests Section -->
        <section class="dashboard-section">
            <h2><i class="fas fa-search"></i> Recent Blood Requests</h2>
            <p class="section-desc">Recipients requesting blood types that match your hospital's inventory</p>
            
            <?php if (mysqli_num_rows($search_history_result) > 0) { ?>
                <div class="table-container">
                    <table class="search-history-table">
                        <thead>
                            <tr>
                                <th>Recipient</th>
                                <th>Blood Type</th>
                                <th>Urgency</th>
                                <th>Distance</th>
                                <th>Date/Time</th>
                                <th>Phone Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($search_history_result)) { 
                                // Format search date/time
                                $search_date = new DateTime($row['search_datetime']);
                                $now = new DateTime();
                                $time_diff = $now->diff($search_date);
                                
                                // Determine urgency level display
                                $urgency_class = '';
                                $urgency_label = 'Normal';
                                if (isset($row['urgency'])) {
                                    if ($row['urgency'] == 'urgent') {
                                        $urgency_class = 'urgent';
                                        $urgency_label = 'Urgent';
                                    } elseif ($row['urgency'] == 'emergency') {
                                        $urgency_class = 'emergency';
                                        $urgency_label = 'Emergency';
                                    }
                                }
                            ?>
                                <tr class="<?php echo $time_diff->h < 6 ? 'recent-search' : ''; ?>">
                                    <td class="recipient-name">
                                        <?php echo $row['recipient_name']; ?>
                                        <?php if ($time_diff->h < 6) { ?>
                                            <span class="badge new-badge">New</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="blood-type-badge"><?php echo $row['blood_type']; ?></span>
                                    </td>
                                    <td>
                                        <span class="urgency-badge <?php echo $urgency_class; ?>">
                                            <?php echo $urgency_label; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo round($row['distance'], 2); ?> km
                                    </td>
                                    <td class="search-time">
                                 
                                    <?php echo $search_date->format('M j, Y g:i A'); ?></small>
                                    </td>
                                    <td>
                                        <a href="tel:<?php echo $row['recipient_phone']; ?>">
                                            <?php echo $row['recipient_phone']; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
               
            <?php } else { ?>
                <!-- Empty State for Search History -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search-minus"></i>
                    </div>
                    <div class="empty-state-text">
                        No recent blood requests found matching your blood inventory.
                    </div>
                </div>
            <?php } ?>
        </section>
        
        <section class="dashboard-section">         
            <h2><i class="fas fa-list"></i> Donor Management</h2>
            
            <a href="hospital_add_donor.php" class="add-donor-btn">
                <i class="fas fa-plus-circle"></i> Add New Donor
            </a>
            
            <?php if (mysqli_num_rows($donations_result) > 0) { ?>
                <!-- Blood Donation Table -->
                <div class="table-container">
                    <table class="donations-table">             
                        <thead>                 
                            <tr>                                         
                                <th>Donor Name</th>
                                <th>Blood Type</th>                     
                                <th>Phone</th>           
                                <th>Email</th>            
                                <th>Last Donation</th>                     
                                <th>Actions</th>                 
                            </tr>             
                        </thead>             
                        <tbody>                 
                            <?php while ($row = mysqli_fetch_assoc($donations_result)) { 
                                // Calculate days since last donation
                                $last_date = new DateTime($row['last_donation_date']);
                                $today = new DateTime();
                                $days_diff = $today->diff($last_date)->days;
                            ?>                     
                                <tr>                         
                                                       
                                    <td class="donor-name"><?php echo $row['donor_name']; ?></td>
                                    <td>
                                        <span class="blood-type-badge"><?php echo $row['donor_blood_type']; ?></span>
                                    </td>                      
                                    <td>
                                        <a href="tel:<?php echo $row['donor_phone']; ?>" class="donor-phone">
                                            <i class="fas fa-phone"></i>
                                            <?php echo $row['donor_phone']; ?>
                                        </a>
                                    </td>             
                                    <td>
                                        <a href="mailto:<?php echo $row['donor_email']; ?>" class="donor-email">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo $row['donor_email']; ?>
                                        </a>
                                    </td>                   
                                    <td class="donation-date">
                                        <?php echo date('F j, Y', strtotime($row['last_donation_date'])); ?>
                                        <small>(<?php echo $days_diff; ?> days ago)</small>
                                    </td>                         
                                    <td>                             
                                        <div class="action-buttons">
                                            <a href="edit_donor.php?id=<?php echo $row['donor_id']; ?>" class="btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="delete_donor.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this donor?');">                                 
                                                <input type="hidden" name="donor_id" value="<?php echo $row['donor_id']; ?>">                                 
                                                <button type="submit" name="delete_donor" class="btn-delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>                             
                                            </form>
                                        </div>                         
                                    </td>                     
                                </tr>                 
                            <?php } ?>             
                        </tbody>         
                    </table>
                </div>
            <?php } else { ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-tint-slash"></i>
                    </div>
                    <div class="empty-state-text">
                        No donors have been added yet.
                    </div>
                    <a href="hospital_add_donor.php" class="add-donor-btn">
                        <i class="fas fa-plus-circle"></i> Add Your First Donor
                    </a>
                </div>
            <?php } ?>
        </section>
        
        <!-- Script to show notifications for urgent blood requests -->
        <script>
            
            document.addEventListener('DOMContentLoaded', function() {
                // Check for urgent requests
                const urgentRequests = document.querySelectorAll('.urgency-badge.urgent, .urgency-badge.emergency');
                
                if (urgentRequests.length > 0) {
                    // Create notification element
                    const notification = document.createElement('div');
                    notification.classList.add('notification', 'urgent-notification');
                    notification.innerHTML = `
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="notification-content">
                            <div class="notification-title">Urgent Blood Requests</div>
                            <div class="notification-message">You have ${urgentRequests.length} urgent blood request(s) that may match your inventory.</div>
                        </div>
                        <button class="notification-close"><i class="fas fa-times"></i></button>
                    `;
                    
                    // Add to document
                    document.body.appendChild(notification);
                    
                    // Show with animation
                    setTimeout(() => {
                        notification.classList.add('show');
                    }, 500);
                    
                    // Handle close button
                    notification.querySelector('.notification-close').addEventListener('click', function() {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    });
                }
            });
            
        </script>
    </div>
    
    <footer class="main-footer">         
        <div class="footer-container">             
            <p>&copy; 2025 Blood Availability System</p>             
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a> | <a href="../contact.php">Contact Us</a></p>         
        </div>     
    </footer>
</body> 
</html>