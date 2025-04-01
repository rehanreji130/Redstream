<?php session_start();  // Check if the user is logged in as an admin 
if (!isset($_SESSION['admin_id'])) {     
    header("Location: main_index.php"); // Redirect to homepage if not logged in as admin     
    exit(); 
}  

// Include the database connection 
include('../includes/db_connection.php'); // Assuming you have this file to connect to the database  

// Query to get all donors 
$query = "SELECT * FROM donors"; // Adjust this query to your actual donors table 
$result = mysqli_query($conn, $query);  

// Handle donor deletion 
if (isset($_GET['delete'])) {     
    $donor_id = $_GET['delete'];     
    $delete_query = "DELETE FROM donors WHERE donor_id = $donor_id"; // Adjust this query to your actual donor ID column     
    mysqli_query($conn, $delete_query);     
    header("Location: admin_manage_donors.php"); // Refresh the page after deletion     
    exit(); 
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donors | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base Variables */
:root {
    --primary-color: #e63946;
    --primary-dark: #b71c1c;
    --primary-light: #ff6b6b;
    --secondary-color: #f1faee;
    --dark-color: #1d3557;
    --light-color: #a8dadc;
    --accent-color: #457b9d;
    --gray-light: #f5f5f5;
    --gray-medium: #e0e0e0;
    --gray-dark: #757575;
    --success-color: #2e7d32;
    --success-bg: #e8f5e9;
    --error-color: #c62828;
    --error-bg: #ffebee;
    --font-primary: 'Poppins', sans-serif;
    --border-radius-sm: 4px;
    --border-radius-md: 8px;
    --border-radius-lg: 12px;
    --box-shadow-light: 0 4px 6px rgba(0, 0, 0, 0.1);
    --box-shadow-medium: 0 10px 30px rgba(0, 0, 0, 0.08);
    --box-shadow-heavy: 0 15px 35px rgba(0, 0, 0, 0.15);
    --transition-speed: 0.3s;
}

/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--font-primary);
    line-height: 1.6;
    color: #333;
    background-color: #f9f9f9;
    background-image: linear-gradient(135deg, #f5f7fa 0%, #e4efe9 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header Styles */
.main-header {
    background: linear-gradient(135deg, var(--primary-color), #d90429);
    color: white;
    padding: 2.5rem 0;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.main-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,64L48,80C96,96,192,128,288,149.3C384,171,480,181,576,165.3C672,149,768,107,864,112C960,117,1056,171,1152,186.7C1248,203,1344,181,1392,170.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: 100% 50%;
}

.header-container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.header-container h1 {
    color: #ffffff;
    margin-bottom: 0.5rem;
    font-size: 2.75rem;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.header-container p {
    font-size: 1.1rem;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    max-width: 600px;
    margin: 0 auto;
}

/* Navigation Styles */
.main-nav {
    background: var(--dark-color);
    padding: 1rem 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.main-nav ul {
    display: flex;
    justify-content: center;
    list-style: none;
    gap: 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.main-nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    position: relative;
    padding: 0.5rem 0;
    transition: color var(--transition-speed);
}

.main-nav ul li a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--primary-color);
    transition: width var(--transition-speed);
}

.main-nav ul li a:hover {
    color: var(--primary-light);
}

.main-nav ul li a:hover::after {
    width: 100%;
}

/* Content Section Styles */
.manage-donors-section {
    max-width: 1200px;
    width: 95%;
    margin: 2.5rem auto;
    background: white;
    padding: 2.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow-medium);
    transition: transform var(--transition-speed);
}

.manage-donors-section:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-heavy);
}

.manage-donors-section h2 {
    color: var(--dark-color);
    margin-bottom: 1.5rem;
    font-size: 1.75rem;
    position: relative;
    padding-bottom: 0.5rem;
}

.manage-donors-section h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--primary-color);
}

/* Table Styles */
.donor-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--border-radius-md);
    overflow: hidden;
    box-shadow: var(--box-shadow-light);
    margin-bottom: 2rem;
}

.donor-table thead {
    background: linear-gradient(to right, var(--secondary-color), #e9f5f5);
}

.donor-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--dark-color);
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
}

.donor-table td {
    padding: 1rem;
    text-align: left;
    vertical-align: middle;
    border-bottom: 1px solid var(--gray-medium);
}

.donor-table tbody tr {
    transition: all var(--transition-speed);
}

.donor-table tbody tr:hover {
    background: rgba(69, 123, 157, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.donor-table tbody tr:last-child td {
    border-bottom: none;
}

/* Blood Type Badge Styles */
.blood-type {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: var(--border-radius-sm);
    background: var(--primary-color);
    color: white;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-align: center;
    min-width: 55px;
    box-shadow: 0 2px 4px rgba(255, 0, 21, 0.3);
}

/* Action Link Styles */
.donor-table td a {
    display: inline-block;
    text-decoration: none;
    padding: 0.4rem 0.8rem;
    border-radius: var(--border-radius-sm);
    transition: all var(--transition-speed);
    font-weight: 600;
    font-size: 0.85rem;
    margin: 0 0.2rem;
}

.donor-table td a:first-of-type {
    background: var(--accent-color);
    color: white;
}

.donor-table td a:last-of-type {
    background: var(--primary-color);
    color: white;
}

.donor-table td a:first-of-type:hover {
    background: #3a6b89;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

.donor-table td a:last-of-type:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

/* Add New Donor Button */
.add-donor-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: var(--accent-color);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius-md);
    font-family: var(--font-primary);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none;
    transition: all var(--transition-speed);
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(69, 123, 157, 0.3);
}

.add-donor-btn:hover {
    background: #3a6b89;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(69, 123, 157, 0.4);
}

.add-donor-btn:active {
    transform: translateY(0);
}

/* Search Functionality */
.search-container {
    margin-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    position: relative;
    min-width: 250px;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid var(--gray-medium);
    border-radius: var(--border-radius-md);
    font-family: var(--font-primary);
    font-size: 1rem;
    transition: all var(--transition-speed);
}

.search-box input:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
}

.search-box::before {
    content: '\f002'; /* fa-search */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-dark);
}

.filter-dropdown {
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-medium);
    border-radius: var(--border-radius-md);
    font-family: var(--font-primary);
    background-color: white;
    min-width: 150px;
    transition: all var(--transition-speed);
}

.filter-dropdown:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
}

/* Message Styles */
.message {
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    border-radius: var(--border-radius-md);
    text-align: center;
    position: relative;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.success-message {
    background: var(--success-bg);
    color: var(--success-color);
    border-left: 5px solid var(--success-color);
}

.error-message {
    background: var(--error-bg);
    color: var(--error-color);
    border-left: 5px solid var(--error-color);
}

.message::before {
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    margin-right: 10px;
    font-size: 1.1rem;
}

.success-message::before {
    content: '\f058'; /* fa-check-circle */
}

.error-message::before {
    content: '\f06a'; /* fa-exclamation-circle */
}

/* Footer Styles */
.main-footer {
    background-color: var(--dark-color);
    color: white;
    padding: 2rem 0;
    text-align: center;
    margin-top: auto;
    position: relative;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.footer-container a {
    color: var(--light-color);
    text-decoration: none;
    transition: color var(--transition-speed);
    font-weight: 600;
}

.footer-container a:hover {
    color: white;
    text-decoration: underline;
}

/* Stats Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    box-shadow: var(--box-shadow-light);
    text-align: center;
    transition: all var(--transition-speed);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-medium);
}

.stat-icon {
   font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--accent-color);
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--gray-dark);
    font-size: 0.9rem;
}

/* Empty Table State */
.empty-table-message {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--gray-dark);
    font-style: italic;
}

/* Pagination Styles */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination a, 
.pagination span {
    padding: 0.5rem 1rem;
    border: 1px solid var(--gray-medium);
    text-decoration: none;
    color: var(--dark-color);
    border-radius: var(--border-radius-sm);
    transition: all var(--transition-speed);
    font-weight: 600;
    min-width: 40px;
    text-align: center;
}

.pagination a:hover {
    background: var(--secondary-color);
    border-color: var(--accent-color);
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.pagination .current {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    box-shadow: 0 2px 5px rgba(230, 57, 70, 0.3);
}

/* Table Sort Indicators */
.sortable {
    cursor: pointer;
    position: relative;
}

.sortable::after {
    content: '\f0dc'; /* fa-sort */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    margin-left: 0.5rem;
    opacity: 0.5;
}

.sort-asc::after {
    content: '\f0de'; /* fa-sort-up */
    opacity: 1;
}

.sort-desc::after {
    content: '\f0dd'; /* fa-sort-down */
    opacity: 1;
}

/* Modal Styles for Confirmation */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease-out;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    max-width: 500px;
    width: 90%;
    box-shadow: var(--box-shadow-heavy);
    text-align: center;
    animation: scaleIn 0.3s ease-out;
}

@keyframes scaleIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.modal-content h3 {
    color: var(--dark-color);
    margin-bottom: 1rem;
}

.modal-content p {
    margin-bottom: 1.5rem;
    color: var(--gray-dark);
}

.modal-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.modal-cancel-btn, .modal-confirm-btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius-md);
    font-family: var(--font-primary);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-speed);
}

.modal-cancel-btn {
    background: var(--gray-light);
    color: var(--gray-dark);
    border: 1px solid var(--gray-medium);
}

.modal-confirm-btn {
    background: var(--primary-color);
    color: white;
    border: none;
}

.modal-cancel-btn:hover {
    background: var(--gray-medium);
}

.modal-confirm-btn:hover {
    background: var(--primary-dark);
}

/* Loading Indicator */
.loading {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(69, 123, 157, 0.2);
    border-left-color: var(--accent-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Donor Details */
.donor-details {
    margin-bottom: 2rem;
    background: var(--secondary-color);
    border-radius: var(--border-radius-md);
    padding: 1.5rem;
    border-left: 5px solid var(--accent-color);
}

.donor-details h3 {
    color: var(--accent-color);
    margin-bottom: 1rem;
}

.donor-details dl {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.donor-details dt {
    font-weight: 600;
    color: var(--dark-color);
}

.donor-details dd {
    margin-left: 0;
    margin-bottom: 0.5rem;
}

/* Print Styles */
@media print {
    .main-header,
    .main-nav,
    .main-footer,
    .pagination,
    .search-container,
    .add-donor-btn {
        display: none;
    }
    
    body,
    .manage-donors-section {
        background: white;
        box-shadow: none;
    }
    
    .manage-donors-section {
        width: 100%;
        padding: 0;
        margin: 0;
    }
    
    .donor-table {
        width: 100%;
        box-shadow: none;
    }
    
    .donor-table th,
    .donor-table td {
        border: 1px solid #ddd;
    }
    
    /* Hide action buttons when printing */
    .donor-table td:last-child {
        display: none;
    }
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .header-container h1 {
        font-size: 2.5rem;
    }
    .manage-donors-section {
        padding: 2rem;
    }
}

@media (max-width: 768px) {
    .header-container h1 {
        font-size: 2.25rem;
    }
    
    .header-container p {
        font-size: 1rem;
    }
    
    .main-nav ul {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
    
    .manage-donors-section {
        padding: 1.5rem;
        width: 90%;
    }
    
    .donor-table {
        font-size: 0.9rem;
    }
    
    .donor-table th, 
    .donor-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .donor-table td a {
        padding: 0.3rem 0.6rem;
        font-size: 0.8rem;
        display: block;
        margin: 0.3rem 0;
    }
}

@media (max-width: 480px) {
    .header-container h1 {
        font-size: 2rem;
    }
    
    .manage-donors-section h2 {
        font-size: 1.5rem;
    }
    
    .donor-table {
        font-size: 0.85rem;
    }
    
    .donor-table th, 
    .donor-table td {
        padding: 0.6rem 0.4rem;
    }
    
    .blood-type {
        padding: 0.3rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .pagination a, 
    .pagination span {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
}

/* Animations for Table Rows */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.donor-table tbody tr {
    animation: fadeInUp 0.3s ease-out;
    animation-fill-mode: both;
}

.donor-table tbody tr:nth-child(1) { animation-delay: 0.05s; }
.donor-table tbody tr:nth-child(2) { animation-delay: 0.1s; }
.donor-table tbody tr:nth-child(3) { animation-delay: 0.15s; }
.donor-table tbody tr:nth-child(4) { animation-delay: 0.2s; }
.donor-table tbody tr:nth-child(5) { animation-delay: 0.25s; }
.donor-table tbody tr:nth-child(6) { animation-delay: 0.3s; }
.donor-table tbody tr:nth-child(7) { animation-delay: 0.35s; }
.donor-table tbody tr:nth-child(8) { animation-delay: 0.4s; }
.donor-table tbody tr:nth-child(9) { animation-delay: 0.45s; }
.donor-table tbody tr:nth-child(10) { animation-delay: 0.5s; }

/* No Records Found State */
.no-records {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    text-align: center;
}

.no-records-icon {
    font-size: 3rem;
    color: var(--gray-medium);
    margin-bottom: 1rem;
}

.no-records-text {
    color: var(--gray-dark);
    margin-bottom: 1.5rem;
}

/* Accessibility Enhancements */
:focus {
    outline: 2px solid var(--accent-color);
    outline-offset: 2px;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

/* Export Button Styles */
.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--dark-color);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: var(--border-radius-md);
    font-family: var(--font-primary);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: none;
    transition: all var(--transition-speed);
    margin-left: auto;
}

.export-btn:hover {
    background: #2c4a7c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(29, 53, 87, 0.3);
}

/* Top Action Bar */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Back Button */
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--accent-color);
    text-decoration: none;
    font-weight: 600;
    transition: all var(--transition-speed);
}

.back-btn:hover {
    color: var(--dark-color);
    transform: translateX(-3px);
}

.back-btn::before {
    content: '\f104'; /* fa-angle-left */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
}
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Manage Donors</h1>
            <p>View, edit, or delete donors from the platform</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="manage-donors-section">
        <h2>Donor List</h2>

        <!-- Donor Table -->
        <table class="donor-table">
            <thead>
                <tr>
                    <th>Donor ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Blood Type</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['donor_id']; ?></td>
                        <td><?php echo $row['donor_name']; ?></td>
                        <td><?php echo $row['donor_email']; ?></td>
                        
                        <td>
                        <span class="blood-type">
                            <?php echo $row['donor_blood_type']; ?>
                            </span>
                        </td>
                        <td><?php echo $row['donor_phone']; ?></td>
                        <td>
                            <a href="admin_edit_donor.php?donor_id=<?php echo $row['donor_id']; ?>">Edit</a> | 
                            <a href="admin_manage_donors.php?delete=<?php echo $row['donor_id']; ?>" onclick="return confirm('Are you sure you want to delete this donor?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability Website</p>
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a></p>
        </div>
    </footer>
</body>
</html>