<?php
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && isset($_GET['categoria']) && isset($_GET['potencialAdquisicion'])) {
    // Get the form data
    $id = $_GET['id'];
    $categoria = $_GET['categoria'];
    $potencialAdquisicion = $_GET['potencialAdquisicion'];
    $responsable = $_GET['responsable'];
    // Validate ID
    if (!is_numeric($id)) {
        echo json_encode(array("success" => false, "message" => "ID must be a valid integer"));
        exit;
    }

    if ($categoria == "Undefined") {
        $categoria = null;
    }

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE inmuebles SET categoria = ?, potencialAdquisicion = ?, responsable = ? WHERE id = ?");
    if ($stmt === false) {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        exit;
    }

    $stmt->bind_param("sisi", $categoria, $potencialAdquisicion, $responsable, $id);

    // Execute the statement
    if ($stmt->execute()) {
        // Return JSON response indicating success
        echo json_encode(array("success" => true, "message" => "Registro actualizado correctamente"));
    } else {
        // Return JSON response indicating failure
        echo json_encode(array("success" => false, "message" => "Error actualizando registro: " . $stmt->error));
    }

    // Close connections
    $stmt->close();
} else {
    // Return JSON response indicating missing or invalid parameters
    echo json_encode(array("success" => false, "message" => "Solicitud inválida"));
}

$conn->close();