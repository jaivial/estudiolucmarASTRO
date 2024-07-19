<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection file
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Check if inmuebles parameter is provided
if (isset($_GET['inmuebles'])) {
    $inmuebles = $_GET['inmuebles'];

    // Check if $inmuebles is an array and not empty
    if (is_array($inmuebles) && !empty($inmuebles)) {
        $inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

        // Prepare and execute SQL statement to delete the records
        $deleteSql = "DELETE FROM inmuebles WHERE id IN ($inmueblesIds)";
        if ($stmt = $conn->prepare($deleteSql)) {
            if ($stmt->execute()) {
                // Check if any rows were affected (i.e., records were deleted)
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Records deleted successfully']);
                } else {
                    echo json_encode(['status' => 'failure', 'message' => 'No records found with the provided ids']);
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
        echo json_encode(['status' => 'failure', 'message' => 'Invalid inmuebles parameter']);
    }
} else {
    echo json_encode(['status' => 'failure', 'message' => 'No inmuebles parameter provided']);
}

// Close the database connection
$conn->close();