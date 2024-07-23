<?php
// Include the CORS configuration file
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();
require_once '../db_Connection/db_Connection.php';
require 'active_sessions.php';



// Check if user is logged in
if (isset($_GET['hashID']) && isset($_GET['user_id'])) {
    $hashID = $_GET['hashID'];
    $user_id = $_GET['user_id'];

    // Check active session status (pseudo code)
    if (isActiveSession($user_id, $hashID)) {
        echo json_encode(['loggedIn' => true]);
    } else {

        echo json_encode(['loggedIn' => false, 'message' => 'User logged out from another device.']);
    }
} else {
    echo json_encode(['loggedIn' => false]);
}