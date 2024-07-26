<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// Initialize response array
$response = ['status' => 'failure', 'noticia' => []];

// Check if the 'id' parameter is set
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT * FROM noticia WHERE noticia_id = ?");

    // Bind the parameter
    $stmt->bind_param('i', $id);

    // Execute the query
    if ($stmt->execute()) {
        // Get the result set
        $result = $stmt->get_result();

        // Fetch the results as an associative array
        if ($row = $result->fetch_assoc()) {
            // Construct the noticia array with each column
            $response['status'] = 'success';
            $response['noticia'] = [
                'id' => $row['id'],
                'noticia_id' => $row['noticia_id'],
                'noticia_fecha' => $row['noticia_fecha'],
                'tipo_PV' => $row['tipo_PV'],
                'valoracion' => $row['valoracion'],
                'valoracionDate' => $row['valoracionDate'],
                'valoracion_establecida' => $row['valoracion_establecida'],
                'prioridad' => $row['prioridad'],
                'comercial_noticia' => $row['comercial_noticia'],
            ];
        } else {
            // Set the response array with failure status and no data
            $response['status'] = 'failure';
            $response['noticia'] = ['error' => 'No record found'];
        }
    } else {
        // Set the response array with failure status and query error
        $response['status'] = 'failure';
        $response['noticia'] = ['error' => 'Query execution failed'];
    }

    // Close the statement
    $stmt->close();
} else {
    // If 'id' parameter is not set, return an error message
    $response['status'] = 'failure';
    $response['noticia'] = ['error' => 'ID parameter is missing'];
}

// Close the connection
$conn->close();

// Return the result as JSON
echo json_encode($response);