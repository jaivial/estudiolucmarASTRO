<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (isset($data['tipo'], $data['existingGroup'], $data['selectedItems']) && is_array($data['selectedItems'])) {
    $tipo = $data['tipo'];
    $existingGroup = $data['existingGroup'];
    $selectedItems = $data['selectedItems'];

    // Sanitize inputs
    $tipo = $conn->real_escape_string($tipo);
    $existingGroup = intval($existingGroup);
    $selectedItemsIds = implode(',', array_map('intval', $selectedItems)); // Convert array to comma-separated list of integers

    // Update the existing records in the inmuebles table
    $updateSql = "UPDATE inmuebles
                  SET AgrupacionChild = '1', AgrupacionID = $existingGroup
                  WHERE id IN ($selectedItemsIds)";

    if ($conn->query($updateSql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}

// Close the database connection
$conn->close();