<?php
header('Content-Type: application/json');

// Database connection
require_once '../db_Connection/db_Connection.php'; // Adjust the path as needed

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the required parameters are set
    if (isset($_GET['id']) && isset($_GET['descripcion'])) {
        $inmueble_id = $_GET['id'];
        $descripcion = $_GET['descripcion'];


        // Prepare the SQL query
        $sql = "UPDATE inmuebles SET descripcion = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $descripcion, $inmueble_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Description updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error executing the query.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error preparing the SQL statement.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Required parameters not set.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close the connection
$conn->close();