<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection file
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';


// Check if id parameter is provided
if (isset($_GET['id'])) {
    $orphanId = $_GET['id'];

    // Prepare and execute SQL statement to delete the orphan record
    $deleteSql = "DELETE FROM inmuebles WHERE id = ?";
    if ($stmt = $conn->prepare($deleteSql)) {
        $stmt->bind_param("i", $orphanId);

        if ($stmt->execute()) {
            // Check if any rows were affected (i.e., a record was deleted)
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Orphan deleted successfully']);
            } else {
                echo json_encode(['status' => 'failure', 'message' => 'No orphan found with the provided id']);
            }
        } else {
            echo json_encode(['status' => 'failure', 'message' => 'Error executing query']);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'failure', 'message' => 'Error preparing query']);
    }
} else {
    echo json_encode(['status' => 'failure', 'message' => 'No id parameter provided']);
}

// Close the database connection
$conn->close();