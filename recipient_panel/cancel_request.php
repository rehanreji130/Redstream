// cancel_request.php
<?php
// Your authentication and database connection code here

if (isset($_GET['id'])) {
    $request_id = $_GET['id'];
    // Your SQL to cancel the request
    $query = "UPDATE blood_requests SET request_status = 'cancelled' WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $request_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>