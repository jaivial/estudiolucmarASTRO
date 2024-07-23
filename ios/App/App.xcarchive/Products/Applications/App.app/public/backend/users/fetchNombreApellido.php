<?php
require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to fetch users' names and surnames
function fetchUsersData()
{
    global $conn;

    $sql = "SELECT nombre, apellido FROM users";
    $result = $conn->query($sql);

    $users = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row['nombre'] . ' ' . $row['apellido'];
        }
    }

    return $users;
}

// Handle GET request to fetch users data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $usersData = fetchUsersData();
    header('Content-Type: application/json');
    echo json_encode($usersData);
}
