<?php

require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to delete a zone
function deleteZone($zoneCodeId)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM map_zones WHERE code_id = ?");
    $stmt->bind_param('s', $zoneCodeId);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Handle DELETE request to delete a zone
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $zoneCodeId = $_GET['zoneCodeId']; // Assuming ID is passed as query parameter

    $success = deleteZone($zoneCodeId);

    header('Content-Type: application/json');
    echo json_encode(array('success' => $success));
}
