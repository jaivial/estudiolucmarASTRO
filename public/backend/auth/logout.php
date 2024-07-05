<?php
// Include the CORS configuration file
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require 'active_sessions.php';
session_start();

$user_id = $_SESSION['user_id'];
// Clear active session from tracking mechanism (pseudo code)
clearActiveSession($user_id, session_id());
// Destroy session to log out user
session_destroy();


echo json_encode(['success' => true]);