<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// Check if required GET parameters are set
if (!isset($_GET['id']) || !isset($_GET['tipoPVA']) || !isset($_GET['valoracion']) || !isset($_GET['valoraciontext']) || !isset($_GET['fecha']) || !isset($_GET['prioridad']) || !isset($_GET['comercial']) || !isset($_GET['fechaValoracion'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Get parameters from the URL
$id = $_GET['id'];
$tipoPVA = $_GET['tipoPVA'];
$valoracion = $_GET['valoracion'];
$valoracionText = $_GET['valoraciontext'];
$fecha = $_GET['fecha'];
$prioridad = $_GET['prioridad'];
$comercial = $_GET['comercial'];
$fechaValoracion = $_GET['fechaValoracion'];

// Handle empty valoracionText
if ($valoracionText == '') {
    $valoracionText = null; // Set to null if empty
}

// Validate and format fechaValoracion
// Assuming fechaValoracion should be in the format YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
if (empty($fechaValoracion)) {
    $fechaValoracion = null; // Set to null if empty
} else {
    $fechaValoracion = date('Y-m-d', strtotime($fechaValoracion)); // Convert to a valid date format if necessary
}

// Debugging: Output values and their types
// Uncomment the lines below if you want to see the actual values being processed
// var_dump($id, $tipoPVA, $valoracion, $valoracionText, $fecha, $prioridad, $comercial, $fechaValoracion);

$conn->begin_transaction();

try {
    // Prepare the SQL statement to insert into noticia
    $stmt_noticia = $conn->prepare("INSERT INTO noticia (noticia_id, tipo_PV, valoracion, valoracion_establecida, noticia_fecha, prioridad, comercial_noticia, valoracionDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt_noticia === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // Determine the parameter types
    // Assuming valoracion is a string and valoracionText can be null
    $stmt_noticia->bind_param("isssssss", $id, $tipoPVA, $valoracion, $valoracionText, $fecha, $prioridad, $comercial, $fechaValoracion);

    // Execute the insert statement
    if (!$stmt_noticia->execute()) {
        throw new Exception('Failed to insert noticia: ' . $stmt_noticia->error);
    }

    // Prepare the SQL statement to update inmuebles
    $stmt_inmuebles = $conn->prepare("UPDATE inmuebles SET noticiastate = true WHERE id = ?");
    if ($stmt_inmuebles === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt_inmuebles->bind_param("i", $id);

    // Execute the update statement
    if (!$stmt_inmuebles->execute()) {
        throw new Exception('Failed to update inmuebles: ' . $stmt_inmuebles->error);
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
if (isset($stmt_noticia)) $stmt_noticia->close();
if (isset($stmt_inmuebles)) $stmt_inmuebles->close();
$conn->close();