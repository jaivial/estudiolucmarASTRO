<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$response = array();
if (isset($_SESSION['user_id']) && isset($_SESSION['email']) && isset($_SESSION['name']) && isset($_SESSION['password']) && isset($_SESSION['last_name']) && isset($_SESSION['hashLogin'])) {
    $response['status'] = 'success';
} else {
    $response['status'] = 'failure';
}

echo json_encode($response);