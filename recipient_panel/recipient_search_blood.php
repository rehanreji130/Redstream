<?php
session_start();
include('../includes/db_connection.php'); // Database connection

// Check if recipient is logged in
if (!isset($_SESSION['recipient_id'])) {
    header("Location: recipient_login.php");
    exit();
}

// Variables
$message = "";
$results = [];

// Handle blood search
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize input
    $blood_type = $_POST['blood_type'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $urgency = $_POST['urgency']; // Added this but not used in database yet
    
    // Get recipient details for logging
    $recipient_id = $_SESSION['recipient_id'];
    
    // Update recipient's location in the database
    $update_location = $conn->prepare("UPDATE recipients SET recipient_latitude = ?, recipient_longitude = ? WHERE recipient_id = ?");
    $update_location->bind_param("ddi", $latitude, $longitude, $recipient_id);
    $update_location->execute();
    $update_location->close();
    
    // Validate location input
    if (empty($latitude) || empty($longitude)) {
        $message = "Location not detected. Please enter manually.";
    } else {
        // Store the search in search history table
$store_search = $conn->prepare("INSERT INTO search_history (recipient_id, blood_type, latitude, longitude, urgency, search_date) VALUES (?, ?, ?, ?, ?, NOW())");
$store_search->bind_param("isdds", $recipient_id, $blood_type, $latitude, $longitude, $urgency);
$store_search->execute();
$store_search->close();
        // Get hospital ID for the blood request
        // We'll insert this in a later step after finding a hospital
        
        // Fetch hospitals with requested blood type and sort by distance using prepared statement
        $query = "
            SELECT h.hospital_id, h.hospital_name, h.hospital_phone, h.hospital_latitude, h.hospital_longitude, d.donor_blood_type, 
                SUM(d.blood_units) as available_units,
                ( 6371 * acos( cos( radians(?) ) * cos( radians( h.hospital_latitude ) ) * cos( radians( h.hospital_longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( h.hospital_latitude ) ) ) ) AS distance
            FROM donors d
            INNER JOIN hospitals h ON d.hospital_id = h.hospital_id
            WHERE d.donor_blood_type = ?
            GROUP BY h.hospital_id
            HAVING SUM(d.blood_units) > 0
            ORDER BY distance ASC"; // Sort by nearest hospital

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ddds", $latitude, $longitude, $latitude, $blood_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        } else {
            $message = "No hospitals found with the selected blood type.";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Availability Search</title>
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
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

/* Modified search content layout - search on left */
.search-content {
    display: flex;
    flex-direction: row-reverse;
    gap: 2rem;
    width: 100%;
}

/* Left side for search form */
.search-form-container {
    width: 280px; /* More compact width */
    flex-shrink: 0;
}

/* Right side for results */
.results-container {
    flex: 1;
    min-width: 0; /* Prevents flex items from overflowing */
}

/* Compact Form Styling */
form {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem; /* Reduced from 1.5rem */
    margin-top: 1rem; /* Reduced from 1.5rem */
    background-color: white;
    padding: 1.25rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border-left: 4px solid var(--primary);
}

.form-group {
    margin-bottom: 0.75rem; /* Reduced from 1rem */
}

label {
    display: block;
    margin-bottom: 0.4rem; /* Reduced from 0.5rem */
    font-weight: 600;
    color: #555;
    font-size: 0.9rem; /* Made slightly smaller */
}

select, input {
    width: 100%;
    padding: 0.65rem; /* Reduced from 0.75rem */
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 0.95rem; /* Made slightly smaller */
    transition: var(--transition);
}

select:focus, input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.2);
}

button {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.65rem 1.25rem; /* Reduced from 0.75rem 1.5rem */
    border-radius: var(--border-radius);
    font-size: 0.95rem; /* Made slightly smaller */
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
}

button:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
}

/* Search section heading */
.search-blood-section h2 {
    font-size: 1.5rem; /* Reduced from 1.8rem */
    margin-bottom: 0.75rem;
    color: var(--primary-dark);
    text-align: left;
}

.search-blood-section h2::after {
    content: '';
    display: block;
    width: 60px; /* Reduced from 80px */
    height: 3px; /* Reduced from 4px */
    background-color: var(--primary);
    margin-top: 0.3rem;
}

/* Location Info */
.location-info {
    background-color: #f8f9fa;
    padding: 0.85rem; /* Reduced from 1rem */
    border-radius: var(--border-radius);
    margin-bottom: 1.25rem; /* Reduced from 1.5rem */
    border-left: 4px solid var(--accent);
    font-size: 0.85rem; /* Made slightly smaller */
}

.location-info p {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}

.location-info i {
    color: var(--accent);
}

/* Messages */
.error-message {
    background-color: #ffebee;
    color: var(--error);
    padding: 0.85rem; /* Reduced from 1rem */
    border-radius: var(--border-radius);
    margin: 1.25rem 0; /* Reduced from 1.5rem */
    text-align: center;
    font-weight: 500;
    border-left: 4px solid var(--error);
    font-size: 0.9rem; /* Made slightly smaller */
}

.success-message {
    background-color: #e8f5e9;
    color: var(--success);
    padding: 0.85rem; /* Reduced from 1rem */
    border-radius: var(--border-radius);
    margin: 1.25rem 0; /* Reduced from 1.5rem */
    text-align: center;
    font-weight: 500;
    border-left: 4px solid var(--success);
    font-size: 0.9rem; /* Made slightly smaller */
}

/* Results Section */
.results-section {
    margin-top: 1.5rem; /* Reduced from 2rem */
}

.results-section h3 {
    color: var(--text-dark);
    margin-bottom: 0.85rem; /* Reduced from 1rem */
    font-size: 1.4rem; /* Reduced from 1.5rem */
    border-bottom: 2px solid #eee;
    padding-bottom: 0.4rem; /* Reduced from 0.5rem */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.85rem; /* Reduced from 1rem */
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
    border-radius: var(--border-radius);
    overflow: hidden;
}

thead {
    background-color: #f5f5f5;
}

th {
    text-align: left;
    padding: 0.85rem; /* Reduced from 1rem */
    font-weight: 600;
    color: #555;
    border-bottom: 2px solid #ddd;
}

td {
    padding: 0.85rem; /* Reduced from 1rem */
    border-bottom: 1px solid #eee;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background-color: #f9f9f9;
}

.hospital-name {
    font-weight: 600;
    color: var(--primary);
}

.distance-cell {
    text-align: right;
    font-weight: 600;
}

.blood-type-cell {
    text-align: center;
    font-weight: 700;
}

.blood-type-badge {
    display: inline-block;
    padding: 0.2rem 0.65rem; /* Reduced from 0.25rem 0.75rem */
    border-radius: 50px;
    background-color: var(--primary-light);
    color: var(--primary-dark);
    font-size: 0.9rem; /* Made slightly smaller */
}

.contact-cell a {
    color: var(--accent);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.contact-cell a:hover {
    text-decoration: underline;
}

/* Card Layout for larger screens */
@media (min-width: 768px) {
    .hospital-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Slightly smaller cards */
        gap: 1.25rem; /* Reduced from 1.5rem */
        margin-top: 1.25rem; /* Reduced from 1.5rem */
    }

    .hospital-card {
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 1.25rem; /* Reduced from 1.5rem */
        transition: var(--transition);
    }

    .hospital-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .hospital-card h4 {
        color: var(--primary);
        margin-bottom: 0.65rem; /* Reduced from 0.75rem */
        font-size: 1.1rem; /* Reduced from 1.2rem */
    }

    .hospital-card-info {
        display: flex;
        flex-direction: column;
        gap: 0.65rem; /* Reduced from 0.75rem */
    }

    .hospital-card-detail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hospital-card-detail i {
        color: var(--primary);
        width: 20px;
    }
}

/* User Navigation */
.user-nav {
    background-color: white;
    padding: 0.85rem; /* Reduced from 1rem */
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem; /* Reduced from 2rem */
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.65rem; /* Reduced from 0.75rem */
}

.user-avatar {
    width: 36px; /* Reduced from 40px */
    height: 36px; /* Reduced from 40px */
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
    gap: 0.85rem; /* Reduced from 1rem */
}

.nav-link {
    text-decoration: none;
    color: #555;
    font-weight: 500;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.95rem; /* Made slightly smaller */
}

.nav-link:hover {
    color: var(--primary);
}

/* Footer Styling */
.main-footer {
    background-color: #333;
    color: #fff;
    padding: 1.75rem 0; /* Reduced from 2rem */
    margin-top: 2.5rem; /* Reduced from 3rem */
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.25rem; /* Reduced from 1.5rem */
    text-align: center;
}

.footer-container p {
    margin: 0.4rem 0; /* Reduced from 0.5rem */
    font-size: 0.85rem; /* Reduced from 0.9rem */
    color: rgba(255, 255, 255, 0.7);
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 1.25rem; /* Reduced from 1.5rem */
    margin-top: 0.85rem; /* Reduced from 1rem */
}

.footer-link {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-size: 0.85rem; /* Reduced from 0.9rem */
    transition: var(--transition);
}

.footer-link:hover {
    color: white;
    text-decoration: underline;
}

/* Loading Animation */
.loader {
    display: none;
    width: 100%;
    text-align: center;
    padding: 0.85rem; /* Reduced from 1rem */
}

.loader-spinner {
    border: 3px solid rgba(0, 0, 0, 0.1); /* Reduced from 4px */
    border-left-color: var(--primary);
    border-radius: 50%;
    width: 26px; /* Reduced from 30px */
    height: 26px; /* Reduced from 30px */
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .search-content {
        flex-direction: column;
    }
    
    .search-form-container {
        width: 100%;
        max-width: 400px;
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 768px) {
    .header-container h1 {
        font-size: 1.8rem;
    }

    table {
        display: block;
        overflow-x: auto;
    }

    .user-nav {
        flex-direction: column;
        gap: 0.85rem;
    }

    .nav-links {
        width: 100%;
        justify-content: space-around;
    }
}

@media (max-width: 480px) {
    .header-container h1 {
        font-size: 1.6rem;
    }

    .search-blood-section {
        padding: 1.25rem;
    }

    th, td {
        padding: 0.65rem;
    }
}
    </style>


</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1><i class="fas fa-heartbeat"></i> Recipient Dashboard</h1>
            <p>Find hospitals with available blood near you</p>
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
                <a href="recipient_profile.php" class="nav-link"><i class="fas fa-id-card"></i> My Profile</a>
                <a href="search_history.php" class="nav-link"><i class="fas fa-history"></i> My Search History</a>
                <a href="recipient_logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <section class="search-blood-section">
            <h2><i class="fas fa-search"></i> Find Blood Near You</h2>
            
            <div class="search-content">
                <!-- Left side - Results -->
                <div class="results-container">
                    <?php if (!empty($message)) { ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php } ?>

                    <?php if (!empty($results)) { ?>
                        <div class="results-section">
                            <h3><i class="fas fa-hospital"></i> Available Hospitals Near You</h3>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Hospital</th>
                                        <th>Contact</th>
                                        <th>Blood Type</th>
                                        <th>Available Units</th>
                                        <th>Distance</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $hospital) { ?>
                                        <tr>
                                            <td class="hospital-name"><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                            <td class="contact-cell">
                                                <a href="tel:<?php echo htmlspecialchars($hospital['hospital_phone']); ?>">
                                                    <i class="fas fa-phone"></i>
                                                    <?php echo htmlspecialchars($hospital['hospital_phone']); ?>
                                                </a>
                                            </td>
                                            <td class="blood-type-cell">
                                                <span class="blood-type-badge"><?php echo htmlspecialchars($hospital['donor_blood_type']); ?></span>
                                            </td>
                                            <td class="units-cell"><?php echo $hospital['available_units']; ?> units</td>
                                            <td class="distance-cell"><?php echo round($hospital['distance'], 2); ?> km</td>
                                            <td class="action-cell">
                                                <a href="send_request.php?hospital_id=<?php echo (int)$hospital['hospital_id']; ?>&blood_type=<?php echo urlencode($hospital['donor_blood_type']); ?>" class="request-btn">
                                                    <i class="fas fa-paper-plane"></i> Send Request
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        
                            <!-- Alternative Card View for Larger Screens -->
                            <div class="hospital-cards">
                                <?php foreach ($results as $hospital) { ?>
                                    <div class="hospital-card">
                                        <h4><?php echo htmlspecialchars($hospital['hospital_name']); ?></h4>
                                        <div class="hospital-card-info">
                                            <div class="hospital-card-detail">
                                                <i class="fas fa-tint"></i>
                                                <span>Blood Type: <strong><?php echo htmlspecialchars($hospital['donor_blood_type']); ?></strong></span>
                                            </div>
                                            <div class="hospital-card-detail">
                                                <i class="fas fa-flask"></i>
                                                <span>Available: <strong><?php echo $hospital['available_units']; ?> units</strong></span>
                                            </div>
                                            <div class="hospital-card-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>Distance: <strong><?php echo round($hospital['distance'], 2); ?> km</strong></span>
                                            </div>
                                            <div class="hospital-card-detail">
                                                <i class="fas fa-phone"></i>
                                                <a href="tel:<?php echo htmlspecialchars($hospital['hospital_phone']); ?>"><?php echo htmlspecialchars($hospital['hospital_phone']); ?></a>
                                            </div>
                                            <div class="hospital-card-action">
                                                <a href="send_request.php?hospital_id=<?php echo (int)$hospital['hospital_id']; ?>&blood_type=<?php echo urlencode($hospital['donor_blood_type']); ?>" class="request-btn-card">
                                                    <i class="fas fa-paper-plane"></i> Send Blood Request
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } else if ($_SERVER['REQUEST_METHOD'] != 'POST') { ?>
                       
                    <?php } ?>
                </div>

                    <form action="" method="POST" id="searchForm">
                        <div class="form-group">
                            <label for="blood_type"><i class="fas fa-tint"></i> Select Blood Type:</label>
                            <select name="blood_type" id="blood_type" required>
                                <option value="A+">A+ (A Positive)</option>
                                <option value="A-">A- (A Negative)</option>
                                <option value="B+">B+ (B Positive)</option>
                                <option value="B-">B- (B Negative)</option>
                                <option value="O+">O+ (O Positive)</option>
                                <option value="O-">O- (O Negative)</option>
                                <option value="AB+">AB+ (AB Positive)</option>
                                <option value="AB-">AB- (AB Negative)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="latitude"><i class="fas fa-map-marker-alt"></i> Your Latitude:</label>
                            <input type="text" name="latitude" id="latitude" required placeholder="Detecting your location...">
                        </div>

                        <div class="form-group">
                            <label for="longitude"><i class="fas fa-map-marker-alt"></i> Your Longitude:</label>
                            <input type="text" name="longitude" id="longitude" required placeholder="Detecting your location...">
                        </div>

                        <div class="form-group">
                            <label for="urgency"><i class="fas fa-exclamation-circle"></i> Urgency Level:</label>
                            <select name="urgency" id="urgency">
                                <option value="normal">Normal - Within 24 hours</option>
                                <option value="urgent">Urgent - Within 6 hours</option>
                                <option value="emergency">Emergency - Immediate</option>
                            </select>
                        </div>

                        <button type="submit" id="searchButton">
                            <i class="fas fa-search"></i> Find Nearby Hospitals
                        </button>
                    </form>

                    <div id="loader" class="loader">
                        <div class="loader-spinner"></div>
                        <p>Searching for blood availability...</p>
                    </div>
                </div>
            </div>
        </section>

       
    </div>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability System</p>
            <p>Helping connect blood recipients with available donors</p>
            <div class="footer-links">
                <a href="../about.php" class="footer-link">About</a>
                <a href="privacy.php" class="footer-link">Privacy Policy</a>
                <a href="terms.php" class="footer-link">Terms of Service</a>
                <a href="../contact.php" class="footer-link">Contact Us</a>
            </div>
        </div>
    </footer>

   <!-- JavaScript for Auto Location Detection and UX improvements -->
    <script> 
        document.addEventListener("DOMContentLoaded", function() {
            const latitudeInput = document.getElementById("latitude");
            const longitudeInput = document.getElementById("longitude");
            const searchForm = document.getElementById("searchForm");
            const searchButton = document.getElementById("searchButton");
            const loader = document.getElementById("loader");

            // Geolocation detection
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        latitudeInput.value = position.coords.latitude.toFixed(6);
                        longitudeInput.value = position.coords.longitude.toFixed(6);
                        
                        // Update placeholder text
                        latitudeInput.placeholder = "Enter latitude";
                        longitudeInput.placeholder = "Enter longitude";
                    },
                    function(error) {
                        console.warn("Geolocation error: " + error.message);
                        
                        // Update placeholder text to indicate manual entry needed
                        latitudeInput.placeholder = "Enter latitude manually";
                        longitudeInput.placeholder = "Enter longitude manually";
                        
                        // Show a notification to the user
                        const locationInfo = document.querySelector(".location-info");
                        locationInfo.innerHTML = `
                            <p style="color: var(--error);">
                                <i class="fas fa-exclamation-triangle"></i>
                                Location access denied or unavailable. Please enter your coordinates manually
                                or <a href="#" id="retryLocation">try again</a>.
                            </p>
                        `;
                        
                        // Add retry functionality
                        document.getElementById("retryLocation").addEventListener("click", function(e) {
                            e.preventDefault();
                            requestGeolocation();
                        });
                    },
                    { timeout: 10000, enableHighAccuracy: true }
                );
            } else {
                latitudeInput.placeholder = "Geolocation not supported";
                longitudeInput.placeholder = "Geolocation not supported";
            }
            
            // Show loading animation when searching
            searchForm.addEventListener("submit", function() {
                searchButton.disabled = true;
                searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                loader.style.display = "block";
            });
            
            // Function to request geolocation
            function requestGeolocation() {
                if (navigator.geolocation) {
                    const locationInfo = document.querySelector(".location-info");
                    locationInfo.innerHTML = `
                        <p>
                            <i class="fas fa-spinner fa-spin"></i>
                            Detecting your location...
                        </p>
                    `;
                    
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            latitudeInput.value = position.coords.latitude.toFixed(6);
                            longitudeInput.value = position.coords.longitude.toFixed(6);
                            
                            locationInfo.innerHTML = `
                                <p style="color: var(--success);">
                                    <i class="fas fa-check-circle"></i>
                                    Location detected successfully!
                                </p>
                                <p><small><i class="fas fa-database"></i> Your search information will be shared with hospitals to help improve blood availability.</small></p>
                            `;
                        },
                        function(error) {
                            locationInfo.innerHTML = `
                                <p style="color: var(--error);">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Location detection failed: ${error.message}. Please enter coordinates manually or <a href="#" id="retryLocation">try again</a>.
                                </p>
                            `;
                            
                            document.getElementById("retryLocation").addEventListener("click", function(e) {
                                e.preventDefault();
                                requestGeolocation();
                            });
                        },
                        { timeout: 10000, enableHighAccuracy: true }
                    );
                }
            }
            
            // Add input validation for coordinates
            function validateCoordinates() {
                const lat = parseFloat(latitudeInput.value);
                const lng = parseFloat(longitudeInput.value);
                
                if (isNaN(lat) || lat < -90 || lat > 90) {
                    alert("Please enter a valid latitude between -90 and 90");
                    latitudeInput.focus();
                    return false;
                }
                
                if (isNaN(lng) || lng < -180 || lng > 180) {
                    alert("Please enter a valid longitude between -180 and 180");
                    longitudeInput.focus();
                    return false;
                }
                
                return true;
            }
            
            // Apply validation before form submission
            searchForm.addEventListener("submit", function(e) {
                if (!validateCoordinates()) {
                    e.preventDefault();
                    return false;
                }
                
                // If validation passes, show loading state
                searchButton.disabled = true;
                searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                loader.style.display = "block";
                return true;
            });
            
            // Add manual location input helpers
            const manualLocationButton = document.createElement("button");
            manualLocationButton.type = "button";
            manualLocationButton.className = "manual-location-btn";
            manualLocationButton.innerHTML = '<i class="fas fa-map-pin"></i> Use Current Location';
            manualLocationButton.style.marginTop = "0.5rem";
            manualLocationButton.style.backgroundColor = "#f0f0f0";
            manualLocationButton.style.color = "#333";
            manualLocationButton.style.border = "1px solid #ddd";
            
            // Insert the button after longitude input
            longitudeInput.parentNode.appendChild(manualLocationButton);
            
            // Add event listener for the manual location button
            manualLocationButton.addEventListener("click", function() {
                requestGeolocation();
            });
        });
    </script>
</body>
</html>