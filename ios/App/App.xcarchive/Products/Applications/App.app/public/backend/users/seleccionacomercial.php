<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// SQL query to select nombre and apellido from users table
$sql = "SELECT nombre, apellido FROM users";
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $comerciales = [];

    // Process each row
    while ($row = $result->fetch_assoc()) {
        $nombrecompleto = $row['nombre'] . ' ' . $row['apellido'];
        $comerciales[] = [
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'nombrecompleto' => $nombrecompleto
        ];
    }

    // Return the data as JSON
    echo json_encode($comerciales);
} else {
    echo json_encode(['error' => 'No records found']);
}

// Close the connection
$conn->close();
