<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (isset($data['type'], $data['name'], $data['inmuebles'], $data['grupo'])) {
    $type = $data['type'];
    $name = $data['name'];
    $inmuebles = $data['inmuebles'];
    $AgrupacionID_Edificio = $data['grupo'];

    // Validate AgrupacionID_Edificio


    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $name = $conn->real_escape_string($name);
    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

    $insertInmueblesSql = "INSERT INTO inmuebles (direccion, TipoAgrupacion, ParentEscalera, ChildEdificio, AgrupacionID_Edificio)
        VALUES ('$name', '$type', 1, 1, $AgrupacionID_Edificio)";
    if ($conn->query($insertInmueblesSql) === TRUE) {
        $agrupacionId = $conn->insert_id;

        $updateInsertedSql = "UPDATE inmuebles SET AgrupacionID_Escalera = $agrupacionId WHERE id = $agrupacionId";
        if ($conn->query($updateInsertedSql) === TRUE) {
            // Update the existing records in the inmuebles table
            $updateSql = "UPDATE inmuebles SET ChildEdificio = NULL, ChildEscalera = 1, AgrupacionID_Edificio = NULL, AgrupacionID_Escalera = $agrupacionId, TipoAgrupacion = '$type' WHERE id IN ($inmueblesIds)";
            if ($conn->query($updateSql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}

// Close the database connection
$conn->close();