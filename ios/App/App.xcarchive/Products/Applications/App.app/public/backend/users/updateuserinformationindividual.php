<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['nombre'], $_GET['apellido'], $_GET['email'], $_GET['password'], $_GET['admin'], $_GET['id'])) {
    $nombre = $_GET['nombre'];
    $apellido = $_GET['apellido'];
    $email = $_GET['email'];
    $password = $_GET['password'];
    $admin = $_GET['admin'];
    $id = $_GET['id'];


    // Prepare and bind
    $stmt = $conn->prepare("UPDATE users SET nombre = ?, apellido = ?, email = ?, password = ?, admin = ? WHERE id = ?");
    if ($stmt === false) {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error));
        exit;
    }

    $stmt->bind_param("ssssii", $nombre, $apellido, $email, $password, $admin, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(array("success" => true, "message" => "Registro actualizado correctamente"));
        } else {
            echo json_encode(array("success" => false, "message" => "No records updated"));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Error actualizando registro: " . $stmt->error));
    }

    $stmt->close();
} else {
    echo json_encode(array("success" => false, "message" => "Solicitud inválida"));
}

$conn->close();
