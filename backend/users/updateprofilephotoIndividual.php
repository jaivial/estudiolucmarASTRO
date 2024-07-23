<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';
if (isset($_FILES['sample_image_individual']) && $_FILES['sample_image_individual']['error'] === 0 && isset($_POST['id'])) {
    $tempName = $_FILES['sample_image_individual']['tmp_name'];
    $imageData = file_get_contents($tempName); // Read binary data
    $id = $_POST['id'];


    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, "UPDATE users SET profile_photo = ? WHERE id = ?");

    if (!$stmt) {
        echo json_encode(array("error" => "Error preparing statement: " . mysqli_error($conn)));
        mysqli_close($conn);
        exit();
    }

    // Bind parameters (avoid escaping binary data)
    mysqli_stmt_bind_param($stmt, "bs", $null, $id);
    mysqli_stmt_send_long_data($stmt, 0, $imageData);

    // Execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
        // Prepare the SQL statement to retrieve the updated profile photo
        $stmt_select = mysqli_prepare($conn, "SELECT profile_photo FROM users WHERE id = ?");

        if (!$stmt_select) {
            echo json_encode(array("error" => "Error preparing select statement: " . mysqli_error($conn)));
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            exit();
        }

        // Bind parameters
        mysqli_stmt_bind_param($stmt_select, "s", $id);

        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt_select)) {
            // Bind result variables
            mysqli_stmt_bind_result($stmt_select, $profilePhoto);

            // Fetch the result
            if (mysqli_stmt_fetch($stmt_select)) {
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

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo json_encode(array("error" => "Error uploading file."));
}
