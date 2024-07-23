<?php

require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to create a new zone
function createZone($zone_name, $color, $zone_responsable, $latlngs, $code_id)
{
    global $conn;

    $stmt = $conn->prepare("INSERT INTO map_zones (zone_name, color, zone_responsable, latlngs, code_id) VALUES (?,?, ?, ?, ?)");
    $stmt->bind_param('sssss', $zone_name, $color, $zone_responsable, $latlngs, $code_id);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Handle POST request to create a new zone
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming JSON payload is sent from React frontend
    $data = json_decode(file_get_contents("php://input"), true);
    $code_id = $data['code_id'];
    $zone_name = $data['zone_name'];
    $color = $data['color'];
    $zone_responsable = $data['zone_responsable'];
    $latlngs = json_encode($data['latlngs']); // Encode latlngs to JSON string

    $success = createZone($zone_name, $color, $zone_responsable, $latlngs, $code_id);

    header('Content-Type: application/json');
    echo json_encode(array('success' => $success));
}
