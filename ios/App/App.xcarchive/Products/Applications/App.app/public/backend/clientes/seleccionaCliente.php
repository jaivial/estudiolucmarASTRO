<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// SQL query to select nombre and apellido from users table
$sql = "SELECT * FROM clientes";
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $clientes = [];

    // Process each row
    while ($row = $result->fetch_assoc()) {
        $nombrecompleto_cliente = $row['nombre_cliente'] . ' ' . $row['apellido_cliente'];
        $clientes[] = [
            'id' => $row['id'],
            'rol_cliente' => $row['rol_cliente'],
            'nombre_cliente' => $row['nombre_cliente'],
            'apellido_cliente' => $row['apellido_cliente'],
            'nombrecompleto_cliente' => $nombrecompleto_cliente,
            'telefono_cliente' => $row['telefono_cliente'],
            'email_cliente' => $row['email_cliente'],
            'rol_cliente' => $row['rol_cliente'],
        ];
    }

    // Return the data as JSON
    echo json_encode($clientes);
} else {
    echo json_encode(['error' => 'No records found']);
}

// Close the connection
$conn->close();
