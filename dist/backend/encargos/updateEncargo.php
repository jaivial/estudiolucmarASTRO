<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if (
    $_SERVER["REQUEST_METHOD"] == "GET" &&
    isset($_GET['encargo_id']) &&
    isset($_GET['fecha']) &&
    isset($_GET['comercial']) &&
    isset($_GET['tipoEncargo']) &&
    isset($_GET['comision']) &&
    isset($_GET['cliente']) &&
    isset($_GET['precio']) &&
    isset($_GET['tipoComision'])
) {

    // Get the form data
    $id = $_GET['encargo_id'];
    $encargo_fecha = $_GET['fecha'];
    $comercial_encargo = $_GET['comercial'];
    $tipo_encargo = $_GET['tipoEncargo'];
    $comision_encargo = $_GET['comision'];
    $cliente_id = $_GET['cliente'];
    $precio_1 = $_GET['precio'];
    $tipo_comision_encargo = $_GET['tipoComision'];

    // Validate ID
    if (!is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "ID must be a valid integer"));
        exit;
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE encargos SET encargo_fecha = ?, comercial_encargo = ?, tipo_encargo = ?, comision_encargo = ?, cliente_id = ?, precio_1 = ?, tipo_comision_encargo = ? WHERE encargo_id = ?");
    if ($stmt === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        exit;
    }

    $stmt->bind_param("sssiiisi", $encargo_fecha, $comercial_encargo, $tipo_encargo, $comision_encargo, $cliente_id, $precio_1, $tipo_comision_encargo, $id);

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