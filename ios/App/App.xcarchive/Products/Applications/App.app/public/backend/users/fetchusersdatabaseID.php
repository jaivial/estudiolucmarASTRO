<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($stmt === false) {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        $conn->close();
        exit;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $user = [
                'id' => $row["id"],
                'email' => $row["email"],
                'name' => $row["nombre"],
                'apellido' => $row["apellido"],
                'password' => $row["password"],
                'admin' => $row["admin"]
            ];

            if ($row["profile_photo"] !== null) {
                $user['profile_photo'] = base64_encode($row["profile_photo"]);
            } else {
                $user['profile_photo'] = null;
            }

            $userData = $user;
            $stmt->close();
            $conn->close();
            echo json_encode(array("success" => true, "data" => $userData));
        } else {
            $stmt->close();
            $conn->close();
            echo json_encode(array("success" => false, "message" => "No user found with the provided ID."));
        }
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode(array("success" => false, "message" => "Execute failed: " . $stmt->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request method or missing ID parameter."));
}
