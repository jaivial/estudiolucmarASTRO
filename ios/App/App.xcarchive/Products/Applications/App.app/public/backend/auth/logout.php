<?php
// Include the CORS configuration file
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';


if (isset($_COOKIE['userID']) && isset($_COOKIE['hashID'])) {
    $userID = $_COOKIE['userID'];
    $hashID = $_COOKIE['hashID'];


    global $conn;
    $stmt = $conn->prepare("DELETE FROM active_sessions WHERE user_id = ? AND session_id = ?");
    $stmt->bind_param('ss', $userID, $hashID);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        setcookie('hashID', '', time() - 3600, '/');
        setcookie('userID', '', time() - 3600, '/');
        setcookie('nombre', '', time() - 3600, '/');
        setcookie('apellido', '', time() - 3600, '/');
        $response = array('success' => true, 'message' => 'user session cleared');
        echo json_encode($response);
    } else {
        $response = array('success' => false, 'message' => 'user session not found');
        echo json_encode($response);
    }
} else {
    $response = array('success' => false, 'message' => 'Cookies not found.');
}
