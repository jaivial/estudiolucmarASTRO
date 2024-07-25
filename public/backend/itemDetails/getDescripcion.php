<?php
header('Content-Type: application/json');

// Database connection
require_once '../db_Connection/db_Connection.php'; // Adjust the path as needed

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the required parameter is set
    if (isset($_GET['id'])) {
        $inmueble_id = $_GET['id'];

        // Prepare the SQL query to fetch descripcion
        $sql = "SELECT descripcion FROM inmuebles WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $inmueble_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($descripcion);
            $stmt->fetch();

            // Check if the description was found
            if ($stmt->num_rows > 0) {
                echo json_encode(['status' => 'success', 'descripcion' => $descripcion]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No description found for the given id.']);
            }

            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error preparing the SQL statement.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Required parameter not set.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close the connection
$conn->close();