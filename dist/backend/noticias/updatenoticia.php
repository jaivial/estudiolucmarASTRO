<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if (
    $_SERVER["REQUEST_METHOD"] == "GET" &&
    isset($_GET['id']) &&
    isset($_GET['fecha']) &&
    isset($_GET['prioridad']) &&
    isset($_GET['tipoPVA']) &&
    isset($_GET['valoracion']) &&
    isset($_GET['valoraciontext']) &&
    isset($_GET['comercial']) &&
    isset($_GET['fechaValoracion'])
) {

    // Get the form data
    $id = $_GET['id'];
    $noticia_fecha = $_GET['fecha'];
    $prioridad = $_GET['prioridad'];
    $tipo_PV = $_GET['tipoPVA'];
    $valoracion = $_GET['valoracion']; // Convert 'Yes'/'No' to 1/0
    $valoracion_establecida = $_GET['valoraciontext'];
    $comercial = $_GET['comercial'];
    $fechaValoracion = $_GET['fechaValoracion']; // New parameter for valoración date

    // Convert empty strings to NULL
    $noticia_fecha = $noticia_fecha === '' ? NULL : $noticia_fecha;
    $prioridad = $prioridad === '' ? NULL : $prioridad;
    $tipo_PV = $tipo_PV === '' ? NULL : $tipo_PV;
    $valoracion_establecida = $valoracion_establecida === '' ? NULL : $valoracion_establecida;
    $comercial = $comercial === '' ? NULL : $comercial;
    $fechaValoracion = $fechaValoracion === '' ? NULL : $fechaValoracion;

    // Validate ID
    if (!is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "ID must be a valid integer"));
        exit;
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE noticia SET noticia_fecha = ?, prioridad = ?, tipo_PV = ?, valoracion = ?, valoracion_establecida = ?, comercial_noticia = ?, valoracionDate = ? WHERE noticia_id = ?");
    if ($stmt === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        exit;
    }

    // Bind parameters
    $stmt->bind_param("sssisssi", $noticia_fecha, $prioridad, $tipo_PV, $valoracion, $valoracion_establecida, $comercial, $fechaValoracion, $id);

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