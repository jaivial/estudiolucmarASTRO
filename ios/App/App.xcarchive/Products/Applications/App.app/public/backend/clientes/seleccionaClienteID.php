<?php
// Set content type to JSON with UTF-8 encoding
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

// Get cliente_id from query parameters
$id = $_GET['cliente_id'];

// SQL query to select data from clientes table based on ID
$sql = "SELECT * FROM clientes WHERE ID = ?";

// Prepare statement with parameter
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id); // 'i' for integer, assuming ID is numeric

// Execute statement
$stmt->execute();

// Get result set
$result = $stmt->get_result();

// Check if there are results
if ($result->num_rows > 0) {
    $clientes = [];

    // Process each row
    while ($row = $result->fetch_assoc()) {
        // Build full name if needed
        $nombrecompleto_cliente = $row['nombre_cliente'] . ' ' . $row['apellido_cliente'];

        // Store data in an associative array
        $clientes[] = [
            'id' => $row['id'],
            'rol_cliente' => $row['rol_cliente'],
            'nombre_cliente' => $row['nombre_cliente'],
            'apellido_cliente' => $row['apellido_cliente'],
            'nombrecompleto_cliente' => $nombrecompleto_cliente,
            'telefono_cliente' => $row['telefono_cliente'],
            'email_cliente' => $row['email_cliente']
        ];
    }

    // Return the data as JSON
    echo json_encode($clientes, JSON_UNESCAPED_UNICODE); // Ensure UTF-8 characters are preserved
} else {
    // Return an error message if no records found
    echo json_encode(['error' => 'No records found']);
}

// Close statement and database connection
$stmt->close();
$conn->close();