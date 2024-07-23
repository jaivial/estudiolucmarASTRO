<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

$response = array("success" => false, "message" => "");

try {
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['nombre'], $_GET['apellido'], $_GET['email'], $_GET['password'], $_GET['admin'])) {
        $nombre = $_GET['nombre'];
        $apellido = $_GET['apellido'];
        $email = $_GET['email'];
        $password = $_GET['password'];
        $admin = $_GET['admin'];

        // Ensure the database connection is successful
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (nombre, apellido, email, password, admin) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssi", $nombre, $apellido, $email, $password, $admin);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response["success"] = true;
                $response["message"] = "Registro actualizado correctamente";
            } else {
                $response["message"] = "No records updated";
            }
        } else {
            throw new Exception("Error actualizando registro: " . $stmt->error);
        }

        $stmt->close();
    } else {
        $response["message"] = "Solicitud inválida";
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
