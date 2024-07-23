<?php
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && isset($_GET['tipo']) && isset($_GET['uso']) && isset($_GET['superficie']) && isset($_GET['ano_construccion'])) {
    // Get the form data
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];
    $uso = $_GET['uso'];
    $superficie = $_GET['superficie'];
    $ano_construccion = $_GET['ano_construccion'];

    // Validate ID
    if (!is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "ID must be a valid integer"));
        exit;
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE inmuebles SET tipo = ?, uso = ?, superficie = ?, ano_construccion = ? WHERE id = ?");
    if ($stmt === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        exit;
    }

    $stmt->bind_param("sssii", $tipo, $uso, $superficie, $ano_construccion, $id);

    // Execute the statement
    if ($stmt->execute()) {
        // Return JSON response indicating success
        echo json_encode(array("success" => true, "message" => "Registro actualizado correctamente"));
    } else {
        // Return JSON response indicating failure
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Error actualizando registro: " . $stmt->error));
    }

    // Close connections
    $stmt->close();
} else {
    // Return JSON response indicating missing or invalid parameters
    http_response_code(400); // Bad Request
    echo json_encode(array("success" => false, "message" => "Solicitud inválida"));
}

$conn->close();