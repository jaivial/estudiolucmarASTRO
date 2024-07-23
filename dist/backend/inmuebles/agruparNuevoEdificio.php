<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (isset($data['type'], $data['name'], $data['inmuebles'])) {
    $type = $data['type'];
    $name = $data['name'];
    $inmuebles = $data['inmuebles']; // Array of inmueble IDs

    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $name = $conn->real_escape_string($name);
    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

    // Insert new record into inmuebles
    $insertInmueblesSql = "INSERT INTO inmuebles (direccion, TipoAgrupacion, ParentEdificio, AgrupacionID_Edificio) 
                           VALUES ('$name', '$type', 1, NULL)";
    if ($conn->query($insertInmueblesSql) === TRUE) {
        $agrupacionId = $conn->insert_id;

        // Get the coordinates of the first inmueble ID
        $firstInmuebleId = $inmuebles[0];
        $selectCoordinatesSql = "SELECT coordinates FROM inmuebles WHERE id = $firstInmuebleId";
        $result = $conn->query($selectCoordinatesSql);

        if ($result && $result->num_rows > 0) {
            $coordinatesRow = $result->fetch_assoc();
            $coordinates = $coordinatesRow['coordinates'];

            // Update the newly inserted record
            $updateInsertedSql = "UPDATE inmuebles SET AgrupacionID_Edificio = $agrupacionId, coordinates = '$coordinates' WHERE id = $agrupacionId";
            if ($conn->query($updateInsertedSql) === TRUE) {
                // Update the existing records in the inmuebles table
                $updateSql = "UPDATE inmuebles SET ChildEdificio = 1, AgrupacionID_Edificio = $agrupacionId, TipoAgrupacion = '$type' WHERE id IN ($inmueblesIds)";
                if ($conn->query($updateSql) === TRUE) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => $conn->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No coordinates found for the first inmueble ID']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
}

// Close the database connection
$conn->close();