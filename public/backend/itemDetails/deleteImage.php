<?php
header('Content-Type: application/json');

// Database connection
require_once '../db_Connection/db_Connection.php'; // Adjust the path as needed

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required POST parameters are set
    if (isset($_POST['inmueble_id']) && isset($_POST['image_id'])) {
        $inmueble_id = $_POST['inmueble_id'];
        $image_id = $_POST['image_id'];

        // Validate and sanitize input
        $inmueble_id = intval($inmueble_id);
        $image_id = intval($image_id);

        // Prepare the SQL query to delete the image
        $sql = "DELETE FROM inmueble_images WHERE inmueble_id = ? AND id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $inmueble_id, $image_id);
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    // Image successfully deleted
                    echo json_encode(['status' => 'success', 'message' => 'Image deleted successfully.']);
                } else {
                    // No rows affected, likely no image found for given parameters
                    echo json_encode(['status' => 'error', 'message' => 'No image found to delete.']);
                }
            } else {
                // Error executing the statement
                echo json_encode(['status' => 'error', 'message' => 'Error executing the SQL statement.']);
            }

            $stmt->close();
        } else {
            // Error preparing the SQL statement
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