<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// Get parameters from the URL
$id = $_GET['encargo_id'];
$tipoEncargo = $_GET['tipoEncargo'];
$comercial = $_GET['comercial'];
$clienteID = $_GET['cliente'];
$precio = $_GET['precio'];
$tipoComision = $_GET['tipoComision'];
$comision = $_GET['comision'];
$fecha = $_GET['fecha'];



// Begin transaction
$conn->begin_transaction();

try {
    // Prepare the SQL statement to insert into noticia
    $stmt_noticia = $conn->prepare("INSERT INTO encargos (encargo_id, encargo_fecha, comercial_encargo, tipo_encargo, comision_encargo, cliente_id, precio_1, tipo_comision_encargo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_noticia->bind_param("isssiiis", $id, $fecha, $comercial, $tipoEncargo, $comision, $clienteID, $precio, $tipoComision);

    // Execute the insert statement
    if (!$stmt_noticia->execute()) {
        throw new Exception($stmt_noticia->error);
    }

    // Prepare the SQL statement to update inmuebles
    $stmt_inmuebles = $conn->prepare("UPDATE inmuebles SET encargoState = true WHERE id = ?");
    $stmt_inmuebles->bind_param("i", $id);

    // Execute the update statement
    if (!$stmt_inmuebles->execute()) {
        throw new Exception($stmt_inmuebles->error);
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => 'Record added and noticiastate updated successfully']);
} catch (Exception $e) {
    // Rollback transaction if any statement fails
    $conn->rollback();
    echo json_encode(['error' => 'Transaction failed: ' . $e->getMessage()]);
}

// Close the statements and connection
$stmt_noticia->close();
$stmt_inmuebles->close();
$conn->close();
