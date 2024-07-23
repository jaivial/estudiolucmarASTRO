<?php
// Include the CORS configuration file
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();
require_once '../db_Connection/db_Connection.php';
require 'active_sessions.php';

if (isset($_COOKIE['userID'])) {
    $userID = $_COOKIE['userID'];
    if (getActiveSessions($userID)) {
        echo json_encode(['success' => true, 'message' => 'User logged in from another device.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No active sessions found.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Cookie not found.']);
}
