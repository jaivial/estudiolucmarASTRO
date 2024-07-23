<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && 
    isset($_GET['id']) && 
    isset($_GET['encargo_fecha']) && 
    isset($_GET['comercial_encargo']) && 
    isset($_GET['tipo_encargo']) && 
    isset($_GET['comision_encargo']) &&
    isset($_GET['cliente_id']) && 
    isset($_GET['precio_1']) &&
    isset($_GET['tipo_comision_encargo'])) {

    // Get the form data
    $id = $_GET['id']; 
    $encargo_fecha = $_GET['encargo_fecha'];
    $comercial_encargo = $_GET['comercial_encargo'];
    $tipo_encargo = $_GET['tipo_encargo'];
    $comision_encargo = $_GET['comision_encargo'];
    $cliente_id = $_GET['cliente_id'];
    $precio_1 = $_GET['precio_1'];
    $tipo_comision_encargo = $_GET['tipo_comision_encargo'];

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
?>