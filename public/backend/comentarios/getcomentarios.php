<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $inmuebleId = $_GET['id'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM comentarios WHERE comentario_id = ?");
    if ($stmt === false) {
        die(json_encode(array("success" => false, "message" => "Prepare failed: " . $conn->error)));
    }

    $stmt->bind_param("i", $inmuebleId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $comments = array();
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        echo json_encode(array("success" => true, "comments" => $comments));
    } else {
        echo json_encode(array("success" => false, "message" => "Error fetching comments"));
    }

    $stmt->close();
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request"));
}

$conn->close();