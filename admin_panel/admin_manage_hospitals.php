<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: main_index.php");
    exit();
}

// Include the database connection
include('../includes/db_connection.php');

// Sanitize input for delete operation
if (isset($_GET['delete'])) {
    $hospital_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    
    if ($hospital_id) {
        // Use prepared statement for deletion
        $delete_stmt = $conn->prepare("DELETE FROM hospitals WHERE hospital_id = ?");
        $delete_stmt->bind_param("i", $hospital_id);
        
        try {
            $delete_stmt->execute();
            
            // Check if any rows were actually deleted
            if ($delete_stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Hospital successfully deleted.";
            } else {
                $_SESSION['error_message'] = "Hospital not found or could not be deleted.";
            }
            
            $delete_stmt->close();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error deleting hospital: " . $e->getMessage();
        }
        
        header("Location: admin_manage_hospitals.php");
        exit();
    }
}

// Fetch hospitals with pagination
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$results_per_page = 10;
$offset = ($page - 1) * $results_per_page;

// Count total hospitals
$total_hospitals_query = "SELECT COUNT(*) AS total FROM hospitals";
$total_result = $conn->query($total_hospitals_query);
$total_hospitals = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_hospitals / $results_per_page);

// Fetch hospitals for current page
$query = "SELECT hospital_id, hospital_name, hospital_address, hospital_phone 
          FROM hospitals 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $results_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
       :root {
    --primary-color: #e63946;
    --primary-dark: #b71c1c;
    --primary-light: #ffcdd2;
    --secondary-color: #f1faee;
    --dark-color: #1d3557;
    --light-color: #a8dadc;
    --accent-color: #457b9d;
    --accent-dark: #2c5282;
    --gray-light: #f8f9fa;
    --gray-medium: #e0e0e0;
    --gray-dark: #757575;
    --success-color: #4caf50;
    --success-light: #e8f5e9;
    --error-color: #f44336;
    --error-light: #ffebee;
    --transition-speed: 0.3s;
    --border-radius: 8px;
    --box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    color: #334155;
    background-color: #f8fafc;
    background-image: linear-gradient(135deg, #f5f7fa 0%, #e4efe9 100%);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.main-header {
    background: linear-gradient(135deg, var(--primary-color), #d90429);
    color: white;
    padding: 2rem 0;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    position: relative;
    overflow: hidden;
}

.main-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 60%);
    pointer-events: none;
}

.header-container {
    position: relative;
    z-index: 1;
}

.header-container h1 {
    color: #ffffff;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    animation: fadeInDown 0.8s ease-out;
}

.header-container p {
    font-weight: 300;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
    opacity: 0.9;
    animation: fadeIn 1s ease-out;
}

.main-nav {
    background-color: var(--dark-color);
    padding: 0.75rem 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.main-nav ul {
    display: flex;
    justify-content: center;
    list-style: none;
    gap: 2rem;
    padding: 0;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

.main-nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    transition: all var(--transition-speed);
    display: inline-block;
    position: relative;
}

.main-nav ul li a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 3px;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    background-color: var(--primary-color);
    transition: width var(--transition-speed);
}

.main-nav ul li a:hover {
    color: var(--primary-light);
}

.main-nav ul li a:hover::after {
    width: 70%;
}

.main-nav ul li a i {
    margin-right: 0.5rem;
}

.manage-hospitals-section {
    max-width: 1200px;
    width: 95%;
    margin: 2rem auto;
    background: white;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    animation: fadeIn 0.6s ease-out;
}

.manage-hospitals-section h2 {
    color: var(--dark-color);
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    position: relative;
    padding-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.manage-hospitals-section h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 80px;
    height: 3px;
    background-color: var(--primary-color);
    border-radius: 10px;
}

.hospital-table-container {
    overflow-x: auto;
    margin-top: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.hospital-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.hospital-table thead {
    background: var(--secondary-color);
}

.hospital-table th, 
.hospital-table td {
    padding: 1.2rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-medium);
}

.hospital-table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    color: var(--dark-color);
}

.hospital-table tbody tr {
    transition: all var(--transition-speed);
}

.hospital-table tbody tr:nth-child(even) {
    background-color: var(--gray-light);
}

.hospital-table tbody tr:hover {
    background: rgba(69, 123, 157, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.hospital-table .actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.actions a {
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    font-size: 0.9rem;
    transition: all var(--transition-speed);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.actions .edit-btn {
    background: var(--accent-color);
    color: white;
}

.actions .edit-btn:hover {
    background: var(--accent-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.actions .delete-btn {
    background: var(--primary-color);
    color: white;
}

.actions .delete-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.pagination {
    display: flex;
    justify-content: center;
    margin: 2rem 0 0.5rem;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination a, 
.pagination span {
    padding: 0.6rem 1.2rem;
    border: 1px solid var(--gray-medium);
    text-decoration: none;
    color: var(--dark-color);
    border-radius: var(--border-radius);
    transition: all var(--transition-speed);
    font-weight: 500;
    min-width: 2.5rem;
    text-align: center;
}

.pagination a:hover {
    background: var(--accent-color);
    color: white;
    border-color: var(--accent-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.pagination .current {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.message {
    padding: 1.2rem;
    margin-bottom: 1.5rem;
    border-radius: var(--border-radius);
    text-align: center;
    font-weight: 500;
    position: relative;
    animation: fadeIn 0.6s ease-out;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.message::before {
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    font-size: 1.2rem;
}

.success-message {
    background: var(--success-light);
    color: var(--success-color);
    border-left: 4px solid var(--success-color);
}

.success-message::before {
    content: '\f058'; /* check-circle icon */
}

.error-message {
    background: var(--error-light);
    color: var(--error-color);
    border-left: 4px solid var(--error-color);
}

.error-message::before {
    content: '\f057'; /* times-circle icon */
}

.main-footer {
    background-color: var(--dark-color);
    color: white;
    padding: 1.8rem 0;
    text-align: center;
    margin-top: auto;
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
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

.footer-container a {
    color: var(--light-color);
    text-decoration: none;
    transition: color var(--transition-speed);
    padding: 0 0.5rem;
    display: inline-block;
}

.footer-container a:hover {
    color: white;
    text-decoration: underline;
}

/* Add hospital button */
.add-hospital-btn {
    background-color: var(--success-color);
    color: white;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    transition: all var(--transition-speed);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.add-hospital-btn:hover {
    background-color: #3b9c3f;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--gray-light);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: var(--gray-dark);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--accent-color);
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

/* Responsive Media Queries */
@media (max-width: 992px) {
    .manage-hospitals-section {
        padding: 2rem;
    }
    
    .hospital-table th, 
    .hospital-table td {
        padding: 1rem 0.75rem;
    }
}

@media (max-width: 768px) {
    .main-nav ul {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
    }
    
    .main-nav ul li a {
        display: block;
        width: 100%;
        text-align: center;
    }
    
    .main-nav ul li a::after {
        bottom: 0;
    }
    
    .hospital-table {
        font-size: 0.9rem;
    }
    
    .hospital-table th, 
    .hospital-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .header-container h1 {
        font-size: 2rem;
    }
    
    .actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .actions a {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .manage-hospitals-section {
        width: 100%;
        padding: 1.5rem 1rem;
        border-radius: 0;
    }
    
    .pagination a, 
    .pagination span {
        padding: 0.5rem 0.8rem;
    }
}

/* Utility classes */
.text-center {
    text-align: center;
}

.mt-2 {
    margin-top: 2rem;
}

.mb-2 {
    margin-bottom: 2rem;
}

.d-flex {
    display: flex;
}

.align-center {
    align-items: center;
}

.justify-between {
    justify-content: space-between;
}

.gap-1 {
    gap: 1rem;
}

/* Form Styling */
.form-container {
    max-width: 800px;
    margin: 2rem auto;
    background: white;
    padding: 2.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.form-container h2 {
    color: var(--dark-color);
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    position: relative;
    padding-bottom: 0.75rem;
}

.form-container h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 80px;
    height: 3px;
    background-color: var(--primary-color);
    border-radius: 10px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-color);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    border: 1px solid var(--gray-medium);
    border-radius: var(--border-radius);
    transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
    font-family: 'Poppins', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
}

.form-control::placeholder {
    color: var(--gray-dark);
    opacity: 0.7;
}

.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23757575' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.form-check-input {
    width: 18px;
    height: 18px;
    accent-color: var(--accent-color);
    cursor: pointer;
}

.form-check-label {
    margin-bottom: 0;
    cursor: pointer;
}

.form-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all var(--transition-speed);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    border: none;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
    background-color: var(--accent-color);
    color: white;
}

.btn-secondary:hover {
    background-color: var(--accent-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-outline {
    background-color: transparent;
    color: var(--dark-color);
    border: 1px solid var(--gray-medium);
}

.btn-outline:hover {
    background-color: var(--gray-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.btn-danger {
    background-color: var(--error-color);
    color: white;
}

.btn-danger:hover {
    background-color: #d32f2f;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-success {
    background-color: var(--success-color);
    color: white;
}

.btn-success:hover {
    background-color: #3b9c3f;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Dashboard cards and stats */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: transform var(--transition-speed);
    border-left: 4px solid var(--accent-color);
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card-primary {
    border-left-color: var(--primary-color);
}

.stat-card-success {
    border-left-color: var(--success-color);
}

.stat-card-accent {
    border-left-color: var(--accent-color);
}

.stat-card-dark {
    border-left-color: var(--dark-color);
}

.stat-card h3 {
    font-size: 0.9rem;
    color: var(--gray-dark);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stat-card .stat-desc {
    color: var(--gray-dark);
    font-size: 0.9rem;
}

.stat-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2.5rem;
    opacity: 0.15;
    color: var(--dark-color);
}

/* Hospital details card */
.hospital-detail-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 2rem;
    overflow: hidden;
}

.hospital-card-header {
    background: var(--secondary-color);
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-medium);
}

.hospital-card-header h2 {
    color: var(--dark-color);
    margin: 0;
    font-size: 1.5rem;
}

.hospital-card-body {
    padding: 1.5rem;
}

.hospital-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.hospital-info-item {
    margin-bottom: 1rem;
}

.hospital-info-item h4 {
    font-size: 0.9rem;
    color: var(--gray-dark);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.hospital-info-item p {
    font-size: 1.1rem;
    color: var(--dark-color);
}

/* Modal styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    pointer-events: none;
    transition: opacity var(--transition-speed);
}

.modal-backdrop.show {
    opacity: 1;
    pointer-events: auto;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-20px);
    transition: transform var(--transition-speed);
}

.modal-backdrop.show .modal-content {
    transform: translateY(0);
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-medium);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.25rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-dark);
    transition: color var(--transition-speed);
}

.modal-close:hover {
    color: var(--primary-color);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--gray-medium);
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Loading spinner */
.spinner {
    width: 40px;
    height: 40px;
    margin: 2rem auto;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top-color: var(--accent-color);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Media queries for responsive design */
@media (max-width: 576px) {
    .form-buttons {
        flex-direction: column;
    }
    
    .form-buttons .btn {
        width: 100%;
    }
    
    .modal-content {
        width: 95%;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
}

/* Print styles */
@media print {
    body {
        background: white;
    }
    
    .main-header,
    .main-nav,
    .main-footer,
    .actions,
    .add-hospital-btn,
    .pagination {
        display: none;
    }
    
    .manage-hospitals-section,
    .form-container,
    .hospital-detail-card {
        box-shadow: none;
        margin: 1rem 0;
        padding: 0;
    }
    
    .hospital-table th,
    .hospital-table td {
        padding: 0.5rem;
    }
}
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1>Manage Hospitals</h1>
            <p>View, edit, or delete hospitals from the platform</p>
        </div>
    </header>

    <nav class="main-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="manage-hospitals-section">
        <?php 
        // Display success or error messages
        if (isset($_SESSION['success_message'])) {
            echo "<div class='message success-message'>" . 
                 htmlspecialchars($_SESSION['success_message']) . 
                 "</div>";
            unset($_SESSION['success_message']);
        }
        
        if (isset($_SESSION['error_message'])) {
            echo "<div class='message error-message'>" . 
                 htmlspecialchars($_SESSION['error_message']) . 
                 "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <h2>Hospital List (<?php echo $total_hospitals; ?> Total)</h2>

        <!-- Hospital Table -->
        <table class="hospital-table">
            <thead>
                <tr>
                    <th>Hospital ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['hospital_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['hospital_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['hospital_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['hospital_phone']); ?></td>
                        <td class="actions">
                            <a href="admin_edit_hospital.php?hospital_id=<?php echo $row['hospital_id']; ?>" 
                               class="edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="admin_manage_hospitals.php?delete=<?php echo $row['hospital_id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this hospital?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            // Previous page link
            if ($page > 1) {
                echo "<a href='admin_manage_hospitals.php?page=" . ($page - 1) . "'>&laquo; Previous</a>";
            }
            
            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo "<span class='current'>$i</span>";
                } else {
                    echo "<a href='admin_manage_hospitals.php?page=$i'>$i</a>";
                }
            }
            
            // Next page link
            if ($page < $total_pages) {
                echo "<a href='admin_manage_hospitals.php?page=" . ($page + 1) . "'>Next &raquo;</a>";
            }
            ?>
        </div>
    </section>

    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; 2025 Blood Availability Website</p>
            <p><a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a></p>
        </div>
    </footer>

    <script>
        // Optional: Add some client-side enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Confirm dialog for delete action
            const deleteLinks = document.querySelectorAll('.delete-btn');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>