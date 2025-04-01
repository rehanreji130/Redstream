<?php
session_start();
include('../includes/db_connection.php'); // Database connection

// Check if recipient is logged in
if (!isset($_SESSION['recipient_id'])) {
    header("Location: recipient_login.php");
    exit();
}

$recipient_id = $_SESSION['recipient_id'];
$message = "";
$search_history = [];

// Pagination settings
$results_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// Get total number of searches for pagination
$count_query = "SELECT COUNT(*) as total FROM search_history WHERE recipient_id = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $recipient_id);
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_searches = $total_row['total'];
$total_pages = ceil($total_searches / $results_per_page);
$count_stmt->close();

// Define the query to get search history with recipient and hospital details
$query = "SELECT sh.search_id, sh.blood_type, sh.search_date, sh.latitude, sh.longitude, sh.urgency,
         r.recipient_latitude, r.recipient_longitude,
         h.hospital_id, h.hospital_name, 
         br.request_status
         FROM search_history sh
         JOIN recipients r ON sh.recipient_id = r.recipient_id
         LEFT JOIN blood_requests br ON sh.recipient_id = br.recipient_id AND sh.blood_type = br.blood_type
         LEFT JOIN hospitals h ON br.hospital_id = h.hospital_id
         WHERE sh.recipient_id = ?
         ORDER BY sh.search_date DESC
         LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $recipient_id, $results_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $search_history[] = $row;
    }
} else {
    $message = "No search history found.";
}

$stmt->close();

// Delete single search history if requested
if (isset($_POST['delete_history']) && isset($_POST['search_id'])) {
    $search_id = (int)$_POST['search_id'];
    
    // Verify ownership before deletion
    $verify_query = "SELECT recipient_id FROM search_history WHERE search_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("i", $search_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $verify_row = $verify_result->fetch_assoc();
        
        // Only allow deletion if the search history belongs to this recipient
        if ($verify_row['recipient_id'] == $recipient_id) {
            $delete_query = "DELETE FROM search_history WHERE search_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $search_id);
            
            if ($delete_stmt->execute()) {
                $message = "Search record deleted successfully.";
                // Redirect to refresh the page
                header("Location: search_history.php");
                exit();
            } else {
                $message = "Error deleting search record.";
            }
            
            $delete_stmt->close();
        } else {
            $message = "You do not have permission to delete this record.";
        }
    }
    
    $verify_stmt->close();
}

// Clear all search history if requested
if (isset($_POST['clear_all_history'])) {
    $clear_query = "DELETE FROM search_history WHERE recipient_id = ?";
    $clear_stmt = $conn->prepare($clear_query);
    $clear_stmt->bind_param("i", $recipient_id);
    
    if ($clear_stmt->execute()) {
        $message = "All search history cleared successfully.";
        // Redirect to refresh the page
        header("Location: search_history.php");
        exit();
    } else {
        $message = "Error clearing search history.";
    }
    
    $clear_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search History - Blood Availability System</title>
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

        /* Search History Section */
        .history-section {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .history-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 10px;
        }

        .history-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin: 1.5rem 0;
            text-align: center;
            font-weight: 500;
        }

        .error-message {
            background-color: #ffebee;
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .success-message {
            background-color: #e8f5e9;
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        /* Table Styling */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .history-table thead {
            background-color: #f5f5f5;
        }

        .history-table th {
            text-align: left;
            padding: 1rem;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #ddd;
        }

        .history-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .history-table tr:hover td {
            background-color: #f9f9f9;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-fulfilled {
            background-color: #e8f5e9;
            color: var(--success);
        }

        .status-pending {
            background-color: #fff8e1;
            color: #ff8f00;
        }

        .status-cancelled {
            background-color: #ffebee;
            color: var(--error);
        }

        /* Blood Type Badge */
        .blood-type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* Actions */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #777;
            transition: var(--transition);
            padding: 0.5rem;
            border-radius: 4px;
        }

        .repeat-btn:hover {
            color: var(--accent);
            background-color: rgba(41, 121, 255, 0.1);
        }

        .delete-btn:hover {
            color: var(--error);
            background-color: rgba(229, 57, 53, 0.1);
        }

        /* Table Actions */
        .table-actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }

        .clear-all-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            background-color: var(--error);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            gap: 0.5rem;
        }

        .clear-all-btn:hover {
            background-color: #c62828;
        }

        /* Modal Dialog */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            max-width: 400px;
            margin: 15% auto;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .modal-title {
            font-size: 1.5rem;
            color: var(--error);
            margin-bottom: 1rem;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }

        .cancel-btn {
            background-color: #f5f5f5;
            color: #555;
        }

        .cancel-btn:hover {
            background-color: #e0e0e0;
        }

        .confirm-btn {
            background-color: var(--error);
            color: white;
        }

        .confirm-btn:hover {
            background-color: #c62828;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: var(--border-radius);
            color: #555;
            text-decoration: none;
           transition: var(--transition);
        }

        .pagination-btn:hover {
            background-color: #f5f5f5;
        }

        .pagination-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 0;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #888;
            margin-bottom: 1.5rem;
        }

        .empty-state-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .empty-state-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsiveness */
        @media (max-width: 768px) {
            .user-nav {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                width: 100%;
                justify-content: space-around;
            }

            .history-table {
                display: block;
                overflow-x: auto;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .table-actions {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .header-container h1 {
                font-size: 1.8rem;
            }

            .history-section {
                padding: 1.5rem;
            }

            .history-table th, 
            .history-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
            
            .status-badge,
            .blood-type-badge {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
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

        /* Back Button Styles */
        .back-button-container {
            text-align: center;
            margin-top: 2rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: #f5f5f5;
            color: #555;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .back-button:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        .back-button i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <h1><i class="fas fa-history"></i> Search History</h1>
            <p>View and manage your blood search records</p>
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
                <a href="recipient_search_blood.php" class="nav-link"><i class="fas fa-search"></i> Search Blood</a>
                <a href="recipient_logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <section class="history-section">
            <h2><i class="fas fa-history"></i> Your Search History</h2>
            
            <?php if (!empty($message)) { ?>
                <div class="message <?php echo strpos($message, 'Error') !== false || strpos($message, 'No search') !== false ? 'error-message' : 'success-message'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php } ?>

            <?php if (empty($search_history)) { ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>No Search History Found</h3>
                    <p>You haven't performed any blood searches yet. Start by searching for available blood near you.</p>
                    <a href="blood_search.php" class="empty-state-btn">
                        <i class="fas fa-search"></i> Search Blood Now
                    </a>
                </div>
            <?php } else { ?>
                <!-- Table Actions -->
                <div class="table-actions">
                    <button class="clear-all-btn" onclick="openClearAllModal()">
                        <i class="fas fa-trash-alt"></i> Clear All History
                    </button>
                </div>
                
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Blood Type</th>
                            <th>Hospital</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_history as $record) { ?>
                            <tr>
                                <td>
                                    <?php echo date('M d, Y g:i A', strtotime($record['search_date'])); ?>
                                </td>
                                <td>
                                    <span class="blood-type-badge">
                                        <?php echo htmlspecialchars($record['blood_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $record['hospital_id'] ? htmlspecialchars($record['hospital_name']) : 'No hospital selected'; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn delete-btn" 
                                                onclick="openDeleteModal(<?php echo (int)$record['search_id']; ?>)" 
                                                title="Delete Record">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <div class="pagination">
                        <?php if ($current_page > 1) { ?>
                            <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php } else { ?>
                            <span class="pagination-btn disabled">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        <?php } ?>
                        
                        <?php 
                        // Display pages
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $start_page + 4);
                        
                        for ($i = $start_page; $i <= $end_page; $i++) { 
                        ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="pagination-btn <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php } ?>
                        
                        <?php if ($current_page < $total_pages) { ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php } else { ?>
                            <span class="pagination-btn disabled">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </section>

        <!-- Back to Dashboard Button -->
        <div class="back-button-container">
            <a href="recipient_search_blood.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Delete Record
            </div>
            <p>Are you sure you want to delete this search record? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button id="cancelDelete" class="modal-btn cancel-btn">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="search_id" id="deleteHistoryId" value="">
                    <input type="hidden" name="delete_history" value="1">
                    <button type="submit" class="modal-btn confirm-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Clear All Confirmation Modal -->
    <div id="clearAllModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Clear All History
            </div>
            <p>Are you sure you want to delete your entire search history? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button id="cancelClearAll" class="modal-btn cancel-btn">Cancel</button>
                <form method="POST" id="clearAllForm">
                    <input type="hidden" name="clear_all_history" value="1">
                    <button type="submit" class="modal-btn confirm-btn">Clear All</button>
                </form>
            </div>
        </div>
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

    <script>
        // Modal Dialog Functions
        const deleteModal = document.getElementById('deleteModal');
        const clearAllModal = document.getElementById('clearAllModal');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const cancelClearAllBtn = document.getElementById('cancelClearAll');
        const deleteHistoryIdInput = document.getElementById('deleteHistoryId');
        
        function openDeleteModal(searchId) {
            deleteHistoryIdInput.value = searchId;
            deleteModal.style.display = 'block';
        }
        
        function openClearAllModal() {
            clearAllModal.style.display = 'block';
        }
        
        // Close the delete modal when Cancel is clicked
        cancelDeleteBtn.onclick = function() {
            deleteModal.style.display = 'none';
        }
        // Close the clear all modal when Cancel is clicked
        cancelClearAllBtn.onclick = function() {
            clearAllModal.style.display = 'none';
        }
        
        // Close the modals when clicking outside of them
        window.onclick = function(event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
            if (event.target == clearAllModal) {
                clearAllModal.style.display = 'none';
            }
        }
        
        // For accessibility - close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (deleteModal.style.display === 'block') {
                    deleteModal.style.display = 'none';
                }
                if (clearAllModal.style.display === 'block') {
                    clearAllModal.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>