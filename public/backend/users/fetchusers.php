<?php
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

session_start();
$email = $_SESSION['email'];
$password = $_SESSION['password'];




// Prepare and execute the SQL query
$sql = "SELECT * FROM users WHERE email = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
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
header('Content-Type: application/json');
echo json_encode($userData);