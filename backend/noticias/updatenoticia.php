<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if (
    $_SERVER["REQUEST_METHOD"] == "GET" &&
    isset($_GET['id']) &&
    isset($_GET['noticia_fecha']) &&
    isset($_GET['prioridad']) &&
    isset($_GET['tipo_PV']) &&
    isset($_GET['valoracion']) &&
    isset($_GET['valoracion_establecida']) &&
    isset($_GET['comercial'])
) {

    // Get the form data
    $id = $_GET['id'];
    $noticia_fecha = $_GET['noticia_fecha'];
    $prioridad = $_GET['prioridad'];
    $tipo_PV = $_GET['tipo_PV'];
    $valoracion = $_GET['valoracion'];
    $valoracion_establecida = $_GET['valoracion_establecida'];
    $comercial = $_GET['comercial'];

    // Validate ID
    if (!is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "ID must be a valid integer"));
        exit;
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE noticia SET noticia_fecha = ?, prioridad = ?, tipo_PV = ?, valoracion = ?, valoracion_establecida = ?, comercial_noticia = ? WHERE noticia_id = ?");
    if ($stmt === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        exit;
    }

    $stmt->bind_param("sssissi", $noticia_fecha, $prioridad, $tipo_PV, $valoracion, $valoracion_establecida, $comercial, $id);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(array("success" => true, "message" => "Registro actualizado correctamente"));
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Error actualizando registro: " . $stmt->error));
    }

    // Close connections
    $stmt->close();
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("success" => false, "message" => "Solicitud inválida"));
}

$conn->close();
