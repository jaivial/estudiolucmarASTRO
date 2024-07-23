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

            // Fetch cookies
            $cookie_email = isset($_COOKIE['usuario']) ? $_COOKIE['usuario'] : null;
            $cookie_password = isset($_COOKIE['password']) ? $_COOKIE['password'] : null;
            $cookie_id = isset($_COOKIE['id']) ? $_COOKIE['id'] : null;

            // Check if the database values match the cookies
            if ($row["email"] == $cookie_email && $row["password"] == $cookie_password && $row["id"] == $cookie_id) {
                echo json_encode(array("success" => false, "message" => "User information matches the cookies."));
            } else {
                echo json_encode(array("success" => true, "message" => "User information does not match the cookies."));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "No user found with the provided ID."));
        }
        $stmt->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Execute failed: " . $stmt->error));
    }
    $conn->close();
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request method or missing ID parameter."));
}
