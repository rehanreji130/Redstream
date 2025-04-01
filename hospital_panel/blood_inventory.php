<?php
session_start();

// Check if the user is logged in as a hospital staff member
if (!isset($_SESSION['hospital_id'])) {     
    header("Location: hospital_login.php");
    exit(); 
}  

// Include database connection
include('../includes/db_connection.php');

$hospital_id = (int)$_SESSION['hospital_id'];

/**
 * Blood Inventory Handler Class
 * Works with existing database schema
 */
class BloodInventoryHandler {
    private $conn;
    private $hospital_id;
    private $valid_blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    
    public function __construct($conn, $hospital_id) {
        $this->conn = $conn;
        $this->hospital_id = $hospital_id;
    }
    
    /**
     * Validate blood type
     */
    public function isValidBloodType($blood_type) {
        return in_array($blood_type, $this->valid_blood_types);
    }
    
    /**
     * Get active donors for the hospital
     */
    public function getActiveDonors($blood_type = null) {
        $query = "SELECT donor_id, donor_name, donor_blood_type, blood_units, last_donation_date 
                  FROM donors 
                  WHERE hospital_id = ? " . 
                  ($blood_type ? "AND donor_blood_type = ?" : "") . "
                  ORDER BY donor_name";
        
        $stmt = mysqli_prepare($this->conn, $query);
        if ($blood_type) {
            mysqli_stmt_bind_param($stmt, "is", $this->hospital_id, $blood_type);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $this->hospital_id);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $donors = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $donors[] = $row;
        }
        
        return $donors;
    }
    
    /**
     * Add blood to specific donor
     */
    public function addBloodToDonor($donor_id, $blood_units) {
        // Validate inputs
        if (!is_numeric($blood_units) || $blood_units <= 0 || $blood_units > 100) {
            return ['success' => false, 'message' => 'Blood units must be between 0.1 and 100'];
        }
        
        // Check if donor exists and belongs to this hospital
        $query = "SELECT donor_id FROM donors WHERE donor_id = ? AND hospital_id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $donor_id, $this->hospital_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 0) {
            return ['success' => false, 'message' => 'Donor not found or not associated with your hospital'];
        }
        
        // Update donor's blood units
        $update_query = "UPDATE donors 
                         SET blood_units = blood_units + ?, 
                             last_donation_date = CURDATE() 
                         WHERE donor_id = ?";
        $update_stmt = mysqli_prepare($this->conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "di", $blood_units, $donor_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            return ['success' => true, 'message' => "{$blood_units} units of blood added to donor successfully"];
        } else {
            return ['success' => false, 'message' => 'Failed to update donor blood units: ' . mysqli_error($this->conn)];
        }
    }
    
    /**
     * Use blood from specific donor
     */
    public function useBloodFromDonor($donor_id, $blood_units) {
        // Validate inputs
        if (!is_numeric($blood_units) || $blood_units <= 0) {
            return ['success' => false, 'message' => 'Invalid blood units value'];
        }
        
        // Check if donor has enough blood available
        $check_query = "SELECT donor_name, donor_blood_type, blood_units FROM donors 
                        WHERE donor_id = ? AND hospital_id = ?";
        $check_stmt = mysqli_prepare($this->conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "ii", $donor_id, $this->hospital_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) == 0) {
            return ['success' => false, 'message' => 'Donor not found or not associated with your hospital'];
        }
        
        $donor = mysqli_fetch_assoc($check_result);
        if ($donor['blood_units'] < $blood_units) {
            return ['success' => false, 'message' => "Insufficient blood units available. Donor only has {$donor['blood_units']} units"];
        }
        
        // Update donor's blood units
        $update_query = "UPDATE donors SET blood_units = blood_units - ? WHERE donor_id = ?";
        $update_stmt = mysqli_prepare($this->conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "di", $blood_units, $donor_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            return [
                'success' => true, 
                'message' => "{$blood_units} units of {$donor['donor_blood_type']} blood used from donor {$donor['donor_name']}"
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to update donor blood units: ' . mysqli_error($this->conn)];
        }
    }
    
    /**
     * Get inventory by blood type
     */
    public function getInventory() {
        $query = "SELECT donor_blood_type, 
                         SUM(blood_units) as total_units, 
                         COUNT(*) as donor_count,
                         MAX(last_donation_date) as latest_donation
                  FROM donors 
                  WHERE hospital_id = ? AND blood_units > 0
                  GROUP BY donor_blood_type
                  ORDER BY donor_blood_type";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $this->hospital_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $inventory = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $inventory[] = $row;
        }
        
        return ['success' => true, 'inventory' => $inventory];
    }
    
    /**
     * Get detailed inventory for a specific blood type
     */
    public function getBloodTypeDetails($blood_type) {
        if (!$this->isValidBloodType($blood_type)) {
            return ['success' => false, 'message' => 'Invalid blood type'];
        }
        
        $query = "SELECT donor_id, donor_name, blood_units, last_donation_date 
                  FROM donors 
                  WHERE hospital_id = ? AND donor_blood_type = ? AND blood_units > 0
                  ORDER BY last_donation_date ASC";
        
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $this->hospital_id, $blood_type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $details = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $details[] = $row;
        }
        
        return ['success' => true, 'details' => $details];
    }
}

// Create inventory handler instance
$inventoryHandler = new BloodInventoryHandler($conn, $hospital_id);

// Handle form submissions
$message = null;
$message_type = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Security validation failed. Please try again.";
        $message_type = "error";
    } else {
        // Add blood to specific donor
        if (isset($_POST['add_blood_to_donor'])) {
            $donor_id = filter_input(INPUT_POST, 'donor_id', FILTER_VALIDATE_INT);
            $blood_units = filter_input(INPUT_POST, 'blood_units', FILTER_VALIDATE_FLOAT);
            
            $result = $inventoryHandler->addBloodToDonor($donor_id, $blood_units);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
        }
        
        // Use blood from specific donor
        if (isset($_POST['use_blood_from_donor'])) {
            $donor_id = filter_input(INPUT_POST, 'donor_id', FILTER_VALIDATE_INT);
            $blood_units = filter_input(INPUT_POST, 'blood_units', FILTER_VALIDATE_FLOAT);
            
            $result = $inventoryHandler->useBloodFromDonor($donor_id, $blood_units);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'error';
        }
    }
}

// Generate CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get current blood inventory
$inventory_result = $inventoryHandler->getInventory();
$blood_inventory = $inventory_result['inventory'];

// Get inventory details if a specific blood type was requested
$blood_type_detail = isset($_GET['blood_type']) ? 
    filter_input(INPUT_GET, 'blood_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

$details = [];
$donors = [];
if ($blood_type_detail) {
    $details_result = $inventoryHandler->getBloodTypeDetails($blood_type_detail);
    if ($details_result['success']) {
        $details = $details_result['details'];
    }
    
    // Get donors for the selected blood type
    $donors = $inventoryHandler->getActiveDonors($blood_type_detail);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bloodred: {
                            light: '#f8d7da',
                            DEFAULT: '#dc3545',
                            dark: '#b02a37'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .blood-drop {
            position: relative;
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #dc3545;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            margin-right: 6px;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-red-500 to-red-700 px-6 py-4">
                    <h1 class="text-3xl font-bold text-white text-center flex items-center justify-center">
                        <i class="fas fa-tint animate-pulse-slow mr-3 text-4xl"></i>
                        Blood Inventory Management
                    </h1>
                </div>
                
                <!-- Display Messages -->
                <?php if (isset($message)): ?>
                    <div class="<?php echo $message_type === 'success' 
                        ? 'bg-green-100 border-l-4 border-green-500 text-green-700' 
                        : 'bg-red-100 border-l-4 border-red-500 text-red-700'; ?> 
                        p-4 mx-6 my-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Two-column layout for add/use blood -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Add Blood Form -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-green-500 transform transition hover:scale-102 duration-300">
                    <form method="POST" action="" id="addBloodForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <h2 class="text-2xl font-bold text-green-600 mb-6 flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Add Blood to Donor
                        </h2>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Select Donor:</label>
                            <div class="relative">
                                <select name="donor_id" required class="appearance-none w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Select Donor</option>
                                    <?php if (!empty($donors)): ?>
                                        <?php foreach($donors as $donor): ?>
                                            <option value="<?php echo $donor['donor_id']; ?>">
                                                <?php echo htmlspecialchars($donor['donor_name']); ?> (<?php echo $donor['donor_blood_type']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Blood Units:</label>
                            <div class="relative">
                                <input type="number" step="0.5" name="blood_units" required 
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                    min="0.5" max="100" placeholder="Enter amount">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <span class="text-gray-500 font-semibold">units</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_blood_to_donor" 
                            class="w-full bg-green-500 text-white p-3 rounded-lg hover:bg-green-600 transition flex items-center justify-center font-bold shadow-md">
                            <i class="fas fa-plus mr-2"></i> Add to Donor
                        </button>
                    </form>
                </div>
                
                <!-- Use Blood Form -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-red-500 transform transition hover:scale-102 duration-300">
                    <form method="POST" action="" id="useBloodForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <h2 class="text-2xl font-bold text-red-600 mb-6 flex items-center">
                            <i class="fas fa-minus-circle mr-2"></i> Use Blood from Donor
                        </h2>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Select Donor:</label>
                            <div class="relative">
                                <select name="donor_id" required class="appearance-none w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                    <option value="">Select Donor</option>
                                    <?php if (!empty($donors)): ?>
                                        <?php foreach($donors as $donor): ?>
                                            <?php if ($donor['blood_units'] > 0): ?>
                                                <option value="<?php echo $donor['donor_id']; ?>">
                                                    <?php echo htmlspecialchars($donor['donor_name']); ?> (<?php echo $donor['donor_blood_type']; ?> - <?php echo $donor['blood_units']; ?> units)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Blood Units:</label>
                            <div class="relative">
                                <input type="number" step="0.5" name="blood_units" required 
                                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent" 
                                    min="0.5" max="100" placeholder="Enter amount">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <span class="text-gray-500 font-semibold">units</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="use_blood_from_donor" 
                            class="w-full bg-red-500 text-white p-3 rounded-lg hover:bg-red-600 transition flex items-center justify-center font-bold shadow-md">
                            <i class="fas fa-minus mr-2"></i> Use from Donor
                        </button>
                    </form>
                </div>
            </div>

            <!-- Blood Inventory Overview -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 px-6 py-4">
                    <h2 class="text-2xl font-bold text-white flex items-center justify-center">
                        <i class="fas fa-chart-bar mr-2"></i> Blood Inventory Overview
                    </h2>
                </div>
                
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-4 text-left text-gray-700 font-semibold">Blood Type</th>
                                    <th class="p-4 text-left text-gray-700 font-semibold">Total Units</th>
                                    <th class="p-4 text-left text-gray-700 font-semibold">Donor Count</th>
                                    <th class="p-4 text-left text-gray-700 font-semibold">Latest Donation</th>
                                    <th class="p-4 text-center text-gray-700 font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($blood_inventory)): ?>
                                    <tr>
                                        <td colspan="5" class="p-4 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center py-8">
                                                <i class="fas fa-tint-slash text-gray-400 text-5xl mb-4"></i>
                                                <p class="text-lg">No blood inventory available</p>
                                                <p class="text-sm text-gray-400 mt-2">Add blood to get started</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($blood_inventory as $inventory): ?>
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-medium
                                                        <?php 
                                                        $bloodTypeColors = [
                                                            'A+' => 'bg-red-100 text-red-800', 
                                                            'A-' => 'bg-blue-100 text-blue-800', 
                                                            'B+' => 'bg-green-100 text-green-800', 
                                                            'B-' => 'bg-yellow-100 text-yellow-800',
                                                            'AB+' => 'bg-purple-100 text-purple-800', 
                                                            'AB-' => 'bg-pink-100 text-pink-800', 
                                                            'O+' => 'bg-indigo-100 text-indigo-800', 
                                                            'O-' => 'bg-gray-100 text-gray-800'
                                                        ];
                                                        echo $bloodTypeColors[$inventory['donor_blood_type']] ?? 'bg-gray-100 text-gray-800';
                                                        ?>">
                                                        <div class="blood-drop"></div>
                                                        <?php echo htmlspecialchars($inventory['donor_blood_type']); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="text-lg font-medium text-gray-900">
                                                        <?php echo number_format($inventory['total_units'], 1); ?>
                                                    </span>
                                                    <span class="text-sm text-gray-500 ml-1">units</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo min(100, $inventory['total_units'] * 2); ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-users text-gray-400 mr-2"></i>
                                                    <span><?php echo intval($inventory['donor_count']); ?></span>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                                                    <?php echo !empty($inventory['latest_donation']) ? date('Y-m-d', strtotime($inventory['latest_donation'])) : 'N/A'; ?>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap text-center">
                                                <a href="?blood_type=<?php echo urlencode($inventory['donor_blood_type']); ?>" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-info-circle mr-2"></i> Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Blood Type Details (when a specific type is selected) -->
            <?php if (!empty($details) && $blood_type_detail): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-700 px-6 py-4 flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-microscope mr-2"></i> 
                            <?php echo htmlspecialchars($blood_type_detail); ?> Blood Details
                        </h2>
                        <a href="?" class="bg-white text-purple-700 px-4 py-2 rounded-lg hover:bg-purple-100 transition flex items-center text-sm font-medium">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Overview
                        </a>
                    </div>
                    
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-4 text-left text-gray-700 font-semibold">Donor ID</th>
                                        <th class="p-4 text-left text-gray-700 font-semibold">Donor Name</th>
                                        <th class="p-4 text-left text-gray-700 font-semibold">Blood Units</th>
                                        <th class="p-4 text-left text-gray-700 font-semibold">Donation Date</th>
                                        <th class="p-4 text-left text-gray-700 font-semibold">Age</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($details as $detail): ?>
                                        <?php 
                                        $donation_date = !empty($detail['last_donation_date']) ? new DateTime($detail['last_donation_date']) : null;
                                        $today = new DateTime();
                                        $days_old = $donation_date ? $today->diff($donation_date)->days : 'N/A';
                                        $expiry_warning = is_numeric($days_old) && $days_old > 35;
                                        $display_age = ($days_old === 0) ? 'Today' : $days_old . ' days';
                                        ?>
                                        <tr class="hover:bg-gray-50 transition duration-150 <?php echo $expiry_warning ? 'bg-red-50' : ''; ?>">
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-fingerprint text-gray-400 mr-2"></i>
                                                    <?php echo $detail['donor_id']; ?>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <?php echo htmlspecialchars($detail['donor_name']); ?>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="font-medium"><?php echo number_format($detail['blood_units'], 1); ?></span>
                                                    <span class="text-sm text-gray-500 ml-1">units</span>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                                                    <?php echo !empty($detail['last_donation_date']) ? date('Y-m-d', strtotime($detail['last_donation_date'])) : 'N/A'; ?>
                                                </div>
                                            </td>
                                            <td class="p-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="<?php echo $expiry_warning ? 'text-red-600 font-bold' : 'text-gray-600'; ?>">
                                                        <?php echo $display_age; ?>
                                                        <?php if ($expiry_warning): ?>
                                                            <i class="fas fa-exclamation-triangle text-red-500 ml-2" title="Blood units approaching expiry"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="text-center text-gray-500 mt-8">
                <p>&copy; <?php echo date('Y'); ?> Hospital Blood Bank Management System</p>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const addBloodForm = document.getElementById('addBloodForm');
            const useBloodForm = document.getElementById('useBloodForm');
            
            if (addBloodForm) {
                addBloodForm.addEventListener('submit', function(e) {
                    const bloodUnits = parseFloat(this.elements['blood_units'].value);
                    if (isNaN(bloodUnits) || bloodUnits <= 0 || bloodUnits > 100) {
                        e.preventDefault();
                        alert('Blood units must be between 0.1 and 100');
                    }
                });
            }
            
            if (useBloodForm) {
                useBloodForm.addEventListener('submit', function(e) {
                    const bloodUnits = parseFloat(this.elements['blood_units'].value);
                    if (isNaN(bloodUnits) || bloodUnits <= 0) {
                        e.preventDefault();
                        alert('Blood units must be greater than 0');
                    }
                });
            }
        });
    </script>
</body>
</html>