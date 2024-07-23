<?php

require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to fetch all zones
function getAllZones()
{
    global $conn;

    $sql = "SELECT * FROM map_zones";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $zones = array();
        while ($row = $result->fetch_assoc()) {
            $zones[] = array(
                'id' => $row['id'],
                'zone_name' => $row['zone_name'],
                'color' => $row['color'],
                'zone_responsable' => $row['zone_responsable'],
                'latlngs' => json_decode($row['latlngs'], true),
                'code_id' => $row['code_id']
            );
        }
        return $zones;
    } else {
        return array(); // Return empty array if no zones found
    }
}

// Handle GET request to fetch all zones
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $zones = getAllZones();
    header('Content-Type: application/json');
    echo json_encode($zones);
}
