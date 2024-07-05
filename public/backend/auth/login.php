<?php
// Include the CORS configuration file
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();
// Include auth.php to use the authenticate function
require 'auth.php';
require 'active_sessions.php';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Check if user is already logged in somewhere else
if (isset($_SESSION['user_id'])) {
    // Invalidate previous session (optional)
    session_destroy(); // Destroy current session
    session_start(); // Start a new session
}

// Initialize the response array
$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $contra = $_GET['password'];
    $user = authenticate($email, $contra);

    if ($user) {
        // Return success JSON response
        $response['email'] = $_SESSION['email'];
        $response['name'] = $_SESSION['name'];
        $response['password'] = $_SESSION['password'];
        $response['last_name'] = $_SESSION['last_name'];
        $response['user_id'] = $_SESSION['user_id'];
        $user_id = $_SESSION['user_id'];
        $_SESSION['loggedIn'] = true;
        // Track active session in database or storage (pseudo code)
        if (getActiveSessions($user_id)) {
            echo json_encode(['success' => false, 'error' => 'User already logged in from another device']);
        } else if (!getActiveSessions($user_id)) {
            trackActiveSession($user_id, session_id());
            echo json_encode(['success' => true]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
} else {
    // Return failure JSON response
    $response['status'] = 'failure';
    $response['message'] = 'Invalid request method';
    // Output JSON response and exit
    echo json_encode($response);
}