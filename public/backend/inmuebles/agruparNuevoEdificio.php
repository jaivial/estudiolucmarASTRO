<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (isset($data['type'], $data['name'], $data['number'], $data['inmuebles'])) {
    $type = $data['type'];
    $name = $data['name'];
    $number = isset($data['number']) ? $data['number'] : '';
    $inmuebles = $data['inmuebles']; // Array of inmueble <IDs></IDs>

    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $name = $conn->real_escape_string($name);
    $number = $conn->real_escape_string($number);
    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers


    $insertInmueblesSql = "INSERT INTO inmuebles (direccion, numero, TipoAgrupacion, ParentEdificio, AgrupacionID_Edificio) 
        VALUES ('$name', '$number', '$type', 1, NULL)";
    if ($conn->query($insertInmueblesSql) === TRUE) {
        $agrupacionId = $conn->insert_id;

        $updateInsertedSql = "UPDATE inmuebles SET AgrupacionID_Edificio = $agrupacionId WHERE id = $agrupacionId";
        if ($conn->query($updateInsertedSql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }

        // Update the existing records in the inmuebles table
        $updateSql = "UPDATE inmuebles SET ChildEdificio = 1, AgrupacionID_Edificio = $agrupacionId, TipoAgrupacion = '$type' WHERE id IN ($inmueblesIds)";
        if ($conn->query($updateSql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    }
}

// Close the database connection
$conn->close();