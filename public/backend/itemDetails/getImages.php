<?php
header('Content-Type: application/json');

// Database connection
require_once '../db_Connection/db_Connection.php'; // Adjust the path as needed

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the required GET parameter is set
    if (isset($_GET['inmueble_id'])) {
        $inmueble_id = $_GET['inmueble_id'];

        // Prepare the SQL query
        $sql = "SELECT image_data, image_type, id, inmueble_id FROM inmueble_images WHERE inmueble_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $inmueble_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $images = [];
            $imageData = [];
            while ($row = $result->fetch_assoc()) {
                // Check if image_data and image_type are present
                if (!empty($row['image_data']) && !empty($row['image_type'])) {
                    $imageData = base64_encode($row['image_data']);
                    $imageType = $row['image_type'];
                    $imageId = $row['id'];
                    $inmueble_id = $row['inmueble_id'];
                    $images[] = [
                        'data' => $imageData,
                        'type' => $imageType,
                        'id' => $imageId,
                        'inmueble_id' => $inmueble_id
                    ];
                } else {
                    // Handle missing image data
                    $images[] = [
                        'data' => '',
                        'type' => 'image/jpeg' // Default type
                    ];
                }
            }

            // Send JSON response
            echo json_encode(['status' => 'success', 'images' => $images]);

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