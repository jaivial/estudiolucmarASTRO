<?php
// Output the response as JSON
header('Content-Type: application/json');

// Database connection
require_once '../db_Connection/db_Connection.php';
require_once '../cors_config.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required POST parameters are set
    if (isset($_FILES['images']) && isset($_POST['inmueble_id'])) {
        $inmueble_id = $_POST['inmueble_id'];
        $images = $_FILES['images'];

        // Check if files were uploaded without errors
        if ($images['error'][0] === UPLOAD_ERR_OK) {
            // Process each uploaded file
            $upload_errors = [];
            $uploaded_images_count = 0;

            foreach ($images['tmp_name'] as $index => $tmp_name) {
                // Check for upload errors
                if ($images['error'][$index] !== UPLOAD_ERR_OK) {
                    $upload_errors[] = "Error uploading file at index $index.";
                    continue;
                }

                $imageData = file_get_contents($tmp_name);
                $imageType = $images['type'][$index];

                // Insert each image into the database
                $sql = "INSERT INTO inmueble_images (inmueble_id, image_data, image_type) VALUES (?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    // Use 's' for binary data, and ensure imageData is properly encoded
                    $stmt->bind_param("iss", $inmueble_id, $imageData, $imageType);
                    if ($stmt->execute()) {
                        $uploaded_images_count++;
                    } else {
                        $upload_errors[] = "Error inserting image at index $index into the database.";
                    }
                    $stmt->close();
                } else {
                    $upload_errors[] = "Error preparing the SQL statement for image at index $index.";
                }
            }

            if (empty($upload_errors)) {
                echo json_encode(['status' => 'success', 'message' => "$uploaded_images_count images uploaded and saved successfully."]);
            } else {
                echo json_encode(['status' => 'error', 'message' => implode(' ', $upload_errors)]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error uploading files.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Required parameters not set.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Close the connection
$conn->close();