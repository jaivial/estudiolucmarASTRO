<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// Get parameters from the URL
$id = $_GET['id'];
$tipoPVA = $_GET['tipoPVA'];
$valoracion = $_GET['valoracion'];
$valoracionText = $_GET['valoraciontext'];
$fecha = $_GET['fecha'];
$prioridad = $_GET['prioridad'];
$comercial = $_GET['comercial'];

if ($valoracionText == '') {
    $valoracionText = null;
}
// Begin transaction
$conn->begin_transaction();

try {
    // Prepare the SQL statement to insert into noticia
    $stmt_noticia = $conn->prepare("INSERT INTO noticia (noticia_id, tipo_PV, valoracion, valoracion_establecida, noticia_fecha, prioridad, comercial_noticia) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_noticia->bind_param("issssss", $id, $tipoPVA, $valoracion, $valoracionText, $fecha, $prioridad, $comercial);

    // Execute the insert statement
    if (!$stmt_noticia->execute()) {
        throw new Exception($stmt_noticia->error);
    }

    // Prepare the SQL statement to update inmuebles
    $stmt_inmuebles = $conn->prepare("UPDATE inmuebles SET noticiastate = true WHERE id = ?");
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
