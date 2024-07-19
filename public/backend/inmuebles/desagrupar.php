<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (!isset($data['inmuebles'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$inmuebles = $data['inmuebles'];
$inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

// Select ChildEscalera and ChildEdificio columns
$selectSql = "SELECT ChildEscalera, ChildEdificio FROM inmuebles WHERE id IN ($inmueblesIds)";
$result = $conn->query($selectSql);

if ($result->num_rows > 0) {
    $allHaveChildEdificio = true;
    $allHaveChildEscalera = true;
    while ($row = $result->fetch_assoc()) {
        if ($row['ChildEdificio'] != 1) {
            $allHaveChildEdificio = false;
        }
        if ($row['ChildEscalera'] != 1) {
            $allHaveChildEscalera = false;
        }
    }

    // // Update the existing records in the inmuebles table
    // $updateSql = "UPDATE inmuebles SET ChildEdificio = NULL, AgrupacionID_Edificio = NULL, ChildEscalera = NULL, AgrupacionID_Escalera = NULL WHERE id IN ($inmueblesIds)";
    // if ($conn->query($updateSql) !== true) {
    //     echo json_encode(['status' => 'error', 'message' => $conn->error]);
    //     exit;
    // }

    // Process for ChildEdificio
    if ($allHaveChildEdificio) {
        // Get AgrupacionID_Edificio from just one of them
        $selectAgrupacionSql = "SELECT AgrupacionID_Edificio FROM inmuebles WHERE id IN ($inmueblesIds) LIMIT 1";
        $agrupacionResult = $conn->query($selectAgrupacionSql);
        $agrupacionRow = $agrupacionResult->fetch_assoc();
        $childAgrupacionID_Edificio = $agrupacionRow['AgrupacionID_Edificio'];

        // // Update the existing records in the inmuebles table
        $updateSql = "UPDATE inmuebles SET ChildEdificio = NULL, AgrupacionID_Edificio = NULL, ChildEscalera = NULL, AgrupacionID_Escalera = NULL WHERE id IN ($inmueblesIds)";
        if ($conn->query($updateSql) !== true) {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
            exit;
        }

        // Count the number of rows that still have ChildEdificio = 1 and AgrupacionID_Edificio = $childAgrupacionID_Edificio
        $countSqlEdificio = "SELECT COUNT(*) AS count FROM inmuebles WHERE ChildEdificio = 1 AND AgrupacionID_Edificio = $childAgrupacionID_Edificio";
        $countResultEdificio = $conn->query($countSqlEdificio);
        $countRowEdificio = $countResultEdificio->fetch_assoc();

        if ($countRowEdificio['count'] > 0) {
            echo json_encode(['status' => 'success', 'message' => 'no parent without childs']);
        } else {
            // Select from inmuebles where ParentEdificio = 1 and AgrupacionID_Edificio = $childAgrupacionID_Edificio
            $selectParentSqlEdificio = "SELECT id, direccion, Numero FROM inmuebles WHERE ParentEdificio = 1 AND AgrupacionID_Edificio = $childAgrupacionID_Edificio";
            $parentResultEdificio = $conn->query($selectParentSqlEdificio);

            if ($parentResultEdificio->num_rows > 0) {
                $parentDataEdificio = array();

                // Fetch the results and populate the array
                while ($row = $parentResultEdificio->fetch_assoc()) {
                    $parentDataEdificio[] = $row;
                }

                echo json_encode(['status' => 'failure', 'message' => 'parent without childs', 'data' => $parentDataEdificio, 'childAgrupacionID_Edificio' => $childAgrupacionID_Edificio]);
            } else {
                echo json_encode(['status' => 'failure', 'message' => 'No parent found']);
            }
        }
    }

    // Process for ChildEscalera
    if ($allHaveChildEscalera) {
        // Get AgrupacionID_Escalera from just one of them
        $selectAgrupacionSqlEscalera = "SELECT AgrupacionID_Escalera FROM inmuebles WHERE id IN ($inmueblesIds) LIMIT 1";
        $agrupacionResultEscalera = $conn->query($selectAgrupacionSqlEscalera);
        $agrupacionRowEscalera = $agrupacionResultEscalera->fetch_assoc();
        $childAgrupacionID_Escalera = $agrupacionRowEscalera['AgrupacionID_Escalera'];

        $updateSql = "UPDATE inmuebles SET ChildEdificio = NULL, AgrupacionID_Edificio = NULL, ChildEscalera = NULL, AgrupacionID_Escalera = NULL WHERE id IN ($inmueblesIds)";
        if ($conn->query($updateSql) !== true) {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
            exit;
        }

        // Count the number of rows that still have ChildEscalera = 1 and AgrupacionID_Escalera = $childAgrupacionID_Escalera
        $countSqlEscalera = "SELECT COUNT(*) AS count FROM inmuebles WHERE ChildEscalera = 1 AND AgrupacionID_Escalera = $childAgrupacionID_Escalera";
        $countResultEscalera = $conn->query($countSqlEscalera);
        $countRowEscalera = $countResultEscalera->fetch_assoc();

        if ($countRowEscalera['count'] > 0) {
            echo json_encode(['status' => 'success', 'message' => 'no parent without childs']);
        } else {
            // Select from inmuebles where ParentEscalera = 1 and AgrupacionID_Escalera = $childAgrupacionID_Escalera
            $selectParentSqlEscalera = "SELECT id, direccion, Numero FROM inmuebles WHERE ParentEscalera = 1 AND AgrupacionID_Escalera = $childAgrupacionID_Escalera";
            $parentResultEscalera = $conn->query($selectParentSqlEscalera);

            if ($parentResultEscalera->num_rows > 0) {
                $parentDataEscalera = array();

                // Fetch the results and populate the array
                while ($row = $parentResultEscalera->fetch_assoc()) {
                    $parentDataEscalera[] = $row;
                }
                echo json_encode(['status' => 'failure', 'message' => 'parent without childs', 'data' => $parentDataEscalera]);
            } else {
                echo json_encode(['status' => 'failure', 'message' => 'No parent found']);
            }
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No records found']);
}

// Close the database connection
$conn->close();