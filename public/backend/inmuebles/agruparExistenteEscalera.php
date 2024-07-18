<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (isset($data['type'], $data['inmuebles'], $data['existingGroup'])) {
    $type = $data['type'];
    $inmuebles = $data['inmuebles'];
    $existingGroup = $data['existingGroup'];
    // Array of inmueble <IDs></IDs>

    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers


    // Update the existing records in the inmuebles table
    $updateSql = "UPDATE inmuebles SET ChildEdificio = NULL, AgrupacionID_Edificio = NULL, ChildEscalera = 1, AgrupacionID_Escalera = $existingGroup, TipoAgrupacion = '$type' WHERE id IN ($inmueblesIds)";
    if ($conn->query($updateSql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}


// Close the database connection
$conn->close();