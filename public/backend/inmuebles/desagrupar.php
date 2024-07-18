<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if ($data['inmuebles']) {

    $inmuebles = $data['inmuebles'];

    $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

    // Update the existing records in the inmuebles table
    $updateSql = "UPDATE inmuebles SET ChildEdificio = NULL, AgrupacionID_Edificio = NULL, ChildEscalera = NULL, AgrupacionID_Escalera = NULL WHERE id IN ($inmueblesIds)";

    if ($conn->query($updateSql) === true) {
        echo json_encode(['status' => 'success', 'message' => 'Desagrupación exitosa']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}

// Close the database connection
$conn->close();