<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {

    // Get the form data
    $id = $_GET['id'];

    // Validate ID
    if (!is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "ID must be a valid integer"));
        exit;
    }

    // Initialize a response array
    $response = array("success" => true, "messages" => array());

    // Delete from noticia where id is equal to ?
    $stmt_delete = $conn->prepare("DELETE FROM noticia WHERE noticia_id = ?");
    if ($stmt_delete === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare for DELETE failed: " . $conn->error));
        exit;
    }

    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        $response["messages"][] = "Record deleted successfully";
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Execute for DELETE failed: " . $stmt_delete->error));
        exit;
    }

    $stmt_delete->close();

    // Update noticiastate to 0 where noticia_id is equal to id
    $stmt_update = $conn->prepare("UPDATE inmuebles SET noticiastate = 0 WHERE id = ?");
    if ($stmt_update === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare for UPDATE failed: " . $conn->error));
        exit;
    }

    $stmt_update->bind_param("i", $id);

    if ($stmt_update->execute()) {
        $response["messages"][] = "Record updated successfully";
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Execute for UPDATE failed: " . $stmt_update->error));
        exit;
    }

    $stmt_update->close();

    // Update encargoState to 0 where encargo_id is equal to id
    $stmt_update = $conn->prepare("UPDATE inmuebles SET encargoState = 0 WHERE id = ?");
    if ($stmt_update === false) {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Prepare for UPDATE failed: " . $conn->error));
        exit;
    }

    $stmt_update->bind_param("i", $id);

    if ($stmt_update->execute()) {
        $response["messages"][] = "Record updated successfully";
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Execute for UPDATE failed: " . $stmt_update->error));
        exit;
    }

    $stmt_update->close();

    // Send the final response
    echo json_encode($response);
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("success" => false, "message" => "Invalid request"));
}

$conn->close();
