<?php
// Include the CORS configuration file
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Call the function to handle CORS headers
handleCorsHeaders();
// Include auth.php to use the authenticate function

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$response = [];

if (isset($_COOKIE['userID'])) {
    $userID = $_COOKIE['userID'];
    // Generate a unique session ID
    $unique_data = $userID . microtime(); // Combine data to ensure uniqueness
    $session_id = hash('sha256', $unique_data); // Create a SHA-256 hash of the unique data

    global $conn;
    $data = array();
    $data['hashID'] = $session_id;
    // Prepare and execute the SQL statement to update the hashLogin only if it is null
    $stmt = $conn->prepare("INSERT IGNORE INTO active_sessions (user_id, session_id) VALUES (?, ?)");
    $stmt->bind_param('ss', $userID, $session_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {

        $response = array('success' => true, 'message' => 'user session set', 'data' => $data);
        // Set cookie 'hashID' with $session_id for 2 hours with path '/'
        setcookie('hashID', $session_id, time() + 7200, '/');
        echo json_encode($response);
    } else {

        $response = array('success' => false, 'message' => 'user session setting went wrong', 'data' => $data);
        echo json_encode($response);
    }
} else {
    // Return failure JSON response
    $response['status'] = 'failure';
    $response['message'] = 'Invalid request method';
    // Output JSON response and exit
    echo json_encode($response);
}
