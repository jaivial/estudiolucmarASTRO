<?php
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    // Get the comment ID from the query parameters
    $commentId = $_GET['id'];

    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM comentarios WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $commentId); // Assuming the comment ID is an integer

    // Execute the statement
    if ($stmt->execute()) {
        // Return JSON response indicating success
        echo json_encode(array("success" => true, "message" => "Comment deleted successfully"));
    } else {
        // Return JSON response indicating failure
        echo json_encode(array("success" => false, "message" => "Error deleting comment"));
    }

    // Close connections
    $stmt->close();
} else {
    // Return JSON response indicating missing or invalid parameters
    echo json_encode(array("success" => false, "message" => "Invalid request"));
}

$conn->close();