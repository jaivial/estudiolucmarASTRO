<?php
require_once '../cors_config.php';
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && isset($_GET['comentario']) && isset($_GET['tipo']) && isset($_GET['user_id'])) {
    $comment = $_GET['comentario'];
    $commentId = $_GET['id'];
    $commentType = $_GET['tipo'];
    $phoneNumber = isset($_GET['telefono']) ? $_GET['telefono'] : '';
    $date = date("Y-m-d H:i:s");

    // Retrieve user_id, fecha, and hora
    $userId = $_GET['user_id'];
    $taskDate = isset($_GET['fecha']) ? $_GET['fecha'] : null;
    $taskTime = isset($_GET['hora']) ? $_GET['hora'] : null;

    // Prepare and bind for inserting comment
    $stmt = $conn->prepare("INSERT INTO comentarios (comentario_id, date_time, texto, TipoComentario, telefono) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die(json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error)));
    }

    $stmt->bind_param("issss", $commentId, $date, $comment, $commentType, $phoneNumber);

    // Execute the statement
    if ($stmt->execute()) {
        // If comment type is 'Cita', add task with separate task_date and task_time
        if ($commentType === 'Cita' && $taskDate && $taskTime) {
            $stmt = $conn->prepare("INSERT INTO tasks (task_date, task_time, task, completed, user_id) VALUES (?, ?, ?, 0, ?)");
            if ($stmt === false) {
                die(json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error)));
            }
            $task = "Cita: " . $comment;
            $stmt->bind_param("sssi", $taskDate, $taskTime, $task, $userId);
            if (!$stmt->execute()) {
                die(json_encode(array("success" => false, "message" => "Error inserting task: " . $stmt->error)));
            }
        }
        echo json_encode(array("success" => true, "message" => "Comentario añadido con éxito"));
    } else {
        echo json_encode(array("success" => false, "message" => "Error insertando comentario: " . $stmt->error));
    }

    $stmt->close();
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request"));
}

$conn->close();