<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (isset($data['type'], $data['inmuebles'], $data['existingGroup'])) {
    $type = $data['type'];
    $name = $data['name'];
    $number = isset($data['number']) ? $data['number'] : '';
    $inmuebles = $data['inmuebles'];
    $existingGroup = $data['existingGroup'];
    // Array of inmueble <IDs></IDs>

    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $name = $conn->real_escape_string($name);
    $number = $conn->real_escape_string($number);
    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers


    // Update the existing records in the inmuebles table
    $updateSql = "UPDATE inmuebles SET ChildEdificio = 1, AgrupacionID_Edificio = $existingGroup, TipoAgrupacion = '$type' WHERE id IN ($inmueblesIds)";
    if ($conn->query($updateSql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}


// Close the database connection
$conn->close();