<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';


// Check if sample_image file was uploaded successfully
if (isset($_FILES['sample_image']) && $_FILES['sample_image']['error'] === 0) {
    // Temporary file name
    $tempName = $_FILES['sample_image']['tmp_name'];

    // Read binary data
    $imageData = file_get_contents($tempName);

    // Check if image data was read successfully
    if ($imageData === false) {
        echo json_encode(array("error" => "Error reading file."));
        exit();
    }
    session_start();
    $id = $_SESSION['user_id'];

    // Prepare SQL statement to update profile photo
    $stmt = mysqli_prepare($conn, "UPDATE users SET profile_photo = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(array("error" => "Error preparing update statement: " . mysqli_error($conn)));
        mysqli_close($conn);
        exit();
    }

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "ss", $imageData, $id);

    // Execute update statement
    if (mysqli_stmt_execute($stmt)) {
        // Update successful, now fetch the updated profile photo
        $stmt_select = mysqli_prepare($conn, "SELECT profile_photo FROM users WHERE id = ?");
        if (!$stmt_select) {
            echo json_encode(array("error" => "Error preparing select statement: " . mysqli_error($conn)));
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            exit();
        }

        // Bind parameters for select statement
        mysqli_stmt_bind_param($stmt_select, "s", $id);

        // Execute select statement
        if (mysqli_stmt_execute($stmt_select)) {
            // Bind result variables
            mysqli_stmt_bind_result($stmt_select, $profilePhoto);

            // Fetch the result
            if (mysqli_stmt_fetch($stmt_select)) {
                // Return profile photo as base64 encoded string
                echo json_encode(array(
                    "message" => "User photo updated successfully!",
                    "profile_photo" => base64_encode($profilePhoto)
                ));
            } else {
                echo json_encode(array("error" => "Error fetching profile photo."));
            }

            mysqli_stmt_close($stmt_select);
        } else {
            echo json_encode(array("error" => "Error executing select statement: " . mysqli_stmt_error($stmt_select)));
        }
    } else {
        echo json_encode(array("error" => "Error updating profile photo: " . mysqli_stmt_error($stmt)));
    }

    // Close prepared statement and database connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo json_encode(array("error" => "Error uploading file."));
}
