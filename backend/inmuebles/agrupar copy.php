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
    $inmuebles = $data['inmuebles'];
    $existingGroup = isset($data['existingGroup']) ? intval($data['existingGroup']) : null;

    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $name = $conn->real_escape_string($name);
    $number = $conn->real_escape_string($number);
    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

    if ($existingGroup !== null) {
        // Case 3: Assign to an existing group without inserting a new row
        $updateSql = "UPDATE inmuebles
                      SET AgrupacionID_Edificio = CASE WHEN '$type' = 'Edificio' THEN $existingGroup ELSE AgrupacionID_Edificio END,
                          AgrupacionID_Escalera = CASE WHEN '$type' = 'Escalera' THEN $existingGroup ELSE AgrupacionID_Escalera END
                      WHERE id IN ($inmueblesIds)";

        if ($conn->query($updateSql) === TRUE) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    } else {
        // Check if the inmuebles have been grouped before
        $checkSql = "SELECT AgrupacionChild, AgrupacionID_Edificio FROM inmuebles WHERE id IN ($inmueblesIds)";
        $result = $conn->query($checkSql);
        $alreadyGrouped = false;
        $existingAgrupacionID_Edificio = null;

        while ($row = $result->fetch_assoc()) {
            if ($row['AgrupacionChild'] == 1 && !is_null($row['AgrupacionID_Edificio'])) {
                $alreadyGrouped = true;
                $existingAgrupacionID_Edificio = $row['AgrupacionID_Edificio'];
                break;
            }
        }

        if ($alreadyGrouped) {
            // Case 2: If already grouped before
            $insertInmueblesSql = "INSERT INTO inmuebles (direccion, numero, TipoAgrupacion, AgrupacionParent, AgrupacionID_Edificio, AgrupacionID_Escalera) 
                                   VALUES ('$name', '$number', '$type', 1, $existingAgrupacionID_Edificio, NULL)";

            if ($conn->query($insertInmueblesSql) === TRUE) {
                $agrupacionId = $conn->insert_id;

                // Update the existing records in the inmuebles table
                $updateSql = "UPDATE inmuebles
                              SET AgrupacionChild = 1, AgrupacionID = $agrupacionId, 
                                  TipoAgrupacion = '$type', AgrupacionID_Escalera = $agrupacionId
                              WHERE id IN ($inmueblesIds)";

                if ($conn->query($updateSql) === TRUE) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => $conn->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            // Case 1: If not grouped before
            $insertInmueblesSql = "INSERT INTO inmuebles (direccion, numero, TipoAgrupacion, AgrupacionParent, AgrupacionID_Edificio) 
                                   VALUES ('$name', '$number', '$type', 1, NULL)";
            if ($conn->query($insertInmueblesSql) === TRUE) {
                $agrupacionId = $conn->insert_id; // Get the ID of the newly inserted row

                // Update the existing records in the inmuebles table
                $updateSql = "UPDATE inmuebles
                              SET AgrupacionChild = 1, AgrupacionID = $agrupacionId,
                                  TipoAgrupacion = '$type', AgrupacionID_Edificio = $agrupacionId
                              WHERE id IN ($inmueblesIds)";

                if ($conn->query($updateSql) === TRUE) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => $conn->error]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}

// Close the database connection
$conn->close();