<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);



if (isset($_COOKIE['userID']) && $_COOKIE['hashID']) {
    $userID = $_COOKIE['userID'];
    $hashID = $_COOKIE['hashID'];
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM active_sessions WHERE user_id = ? AND session_id = ?");
    $stmt->bind_param('ss', $userID, $hashID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $response['success'] = true;
        echo json_encode($response);
        exit();
    } else {
        $response['success'] = false;
        echo json_encode($response);
        exit();
    }
} else {
    $response = array('status' => 'failure', 'message' => 'Cookies not found.');
    echo json_encode($response);
    exit();
}
