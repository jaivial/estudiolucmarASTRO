<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// Check if the 'id' parameter is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT * FROM noticias WHERE noticia_id = ?");

    // Bind the parameter
    $stmt->bind_param('i', $id);

    // Execute the query
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Fetch the results as an associative array
    $row = $result->fetch_assoc();

    // Return the result as JSON
    echo json_encode($row);
} else {
    // If 'id' parameter is not set, return an error message
    echo json_encode(['error' => 'ID parameter is missing']);
}

// Close the statement
$stmt->close();

// Close the connection
$conn->close();
