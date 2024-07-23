<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
if (!isset($_GET['inmuebles'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$inmuebles = $_GET['inmuebles'];
$inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

$itemsToDelete = [];
$parentData = [];
// Step 1: Select IDs where ParentEdificio = 1 OR ParentEscalera = 1
$query = "SELECT id, ParentEdificio, ParentEscalera, AgrupacionID_Edificio, AgrupacionID_Escalera 
          FROM inmuebles
          WHERE id IN ($inmueblesIds)";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['ParentEdificio'] == 1) {
            $parentData[] = $row['id'];
            $AgrupacionID_Edificio = $row['AgrupacionID_Edificio'];
            // Step 2: Select 'id', 'ParentEscalera' where ChildEdificio = 1 and AgrupacionID_Edificio = 1
            $query2 = "SELECT id, ParentEscalera, AgrupacionID_Escalera 
                       FROM inmuebles
                       WHERE ChildEdificio = 1 AND AgrupacionID_Edificio = $AgrupacionID_Edificio";
            $result2 = mysqli_query($conn, $query2);
            if ($result2) {
                while ($row2 = mysqli_fetch_assoc($result2)) {
                    $itemsToDelete[] = $row2['id'];
                    if ($row2['ParentEscalera'] == 1) {
                        $AgrupacionID_Escalera = $row2['AgrupacionID_Escalera'];
                        // Step 3: Select 'id' where ChildEscalera = 1 and AgrupacionID_Escalera = $AgrupacionID_Escalera
                        $query3 = "SELECT id 
                                   FROM inmuebles
                                   WHERE ChildEscalera = 1 AND AgrupacionID_Escalera = $AgrupacionID_Escalera";
                        $result3 = mysqli_query($conn, $query3);
                        if ($result3) {
                            while ($row3 = mysqli_fetch_assoc($result3)) {
                                $itemsToDelete[] = $row3['id'];
                            }
                        } else {
                            echo json_encode(['status' => 'failure']);
                            exit;
                        }
                    }
                }
            } else {
                echo json_encode(['status' => 'failure']);
                exit;
            }
        }
        if ($row['ParentEscalera'] == 1) {
            $AgrupacionID_Escalera = $row['AgrupacionID_Escalera'];
            $itemsToDelete[] = $row['id'];
            $parentData[] = $row['id'];
            // Step 4: Select 'id' where ChildEscalera = 1 and AgrupacionID_Escalera = $AgrupacionID_Escalera
            $query4 = "SELECT id 
                       FROM inmuebles
                       WHERE ChildEscalera = 1 AND AgrupacionID_Escalera = $AgrupacionID_Escalera";
            $result4 = mysqli_query($conn, $query4);
            if ($result4) {
                while ($row4 = mysqli_fetch_assoc($result4)) {
                    $itemsToDelete[] = $row4['id'];
                }
            } else {
                echo json_encode(['status' => 'failure']);
                exit;
            }
        }
    }
    $parentdata = $parentData;
    $data = $itemsToDelete;

    if (count($data) > 0) {
        echo json_encode(['status' => 'success', 'data' => $data, 'parentdata' => $parentdata]);
    } else {
        echo json_encode(['status' => 'failure', 'message' => 'El elemento no tiene hijos']);
    }
} else {
    echo json_encode(['status' => 'failure']);
}