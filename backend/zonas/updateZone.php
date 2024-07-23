<?php

require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to update a zone
function updateZone($zone_id, $latlngs)
{
    global $conn;

    // Prepare SQL statement to update zone data
    $stmt = $conn->prepare("UPDATE map_zones SET latlngs = ? WHERE code_id = ?");
    $stmt->bind_param('ss', $latlngs, $zone_id);

    // Execute the statement
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Handle POST request to update a zone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming JSON payload is sent from React frontend
    $data = json_decode(file_get_contents("php://input"), true);

    // Extract data from JSON payload
    $zone_id = $data['code_id']; // Adjust 'id' to 'code_id' based on your React data structure
    $latlngs = json_encode($data['latlngs']); // Encode latlngs to JSON string

    // Call function to update zone
    $success = updateZone($zone_id, $latlngs);

    // Send JSON response back to React frontend
    header('Content-Type: application/json');
    echo json_encode(array('success' => $success));
}
