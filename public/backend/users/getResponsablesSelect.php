<?php

require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to fetch all zones
function getAllResponsables()
{
    global $conn;

    $sql = "SELECT nombre, apellido FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $responsables = array();
        while ($row = $result->fetch_assoc()) {
            $nombre = $row['nombre'];
            $apellido = $row['apellido'];
            $responsables[] = $nombre . ' ' . $apellido;
        }
        return $responsables;
    } else {
        return array(); // Return empty array if no zones found
    }
}

// Handle GET request to fetch all zones
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $zones = getAllResponsables();
    header('Content-Type: application/json');
    echo json_encode($zones);
}
