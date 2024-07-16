<?php
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_COOKIE['userID'])) {

    $user_id = $_COOKIE['userID'];

    // Prepare and execute the SQL query
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch data and store in an array
    $userData = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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

            $userData[] = $user;
        }
    } else {
        $userData[] = ['error' => 'No user found with the provided credentials.', 'email' => $email];
    }

    // Close the database connection
    $stmt->close();
    $conn->close();

    // Output the user data as JSON
    echo json_encode($userData);
} else {
    // Return failure JSON response
    $response['status'] = 'failure';
    $response['message'] = 'Invalid request method';
    // Output JSON response and exit
    echo json_encode($response);
}