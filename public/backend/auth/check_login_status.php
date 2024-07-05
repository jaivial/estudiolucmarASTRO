<?php
// Include the CORS configuration file
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();
require_once '../db_Connection/db_Connection.php';
require 'active_sessions.php';

session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sessionID = session_id();
    // Check active session status (pseudo code)
    if (isActiveSession($user_id, $sessionID)) {
        echo json_encode(['loggedIn' => true]);
    } else {
        // If session is not active, log out user
        clearActiveSession($user_id, $sessionID);
        session_destroy();
        echo json_encode(['loggedIn' => false, 'message' => 'User logged out from another device.']);
    }
} else {
    echo json_encode(['loggedIn' => false]);
}
