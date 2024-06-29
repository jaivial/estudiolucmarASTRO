<?php
// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow GET and POST requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow headers with Content-Type

// Set content type for JSON response
header('Content-Type: application/json');

// Include the database connection file
include './dbConnection.php';

// SQL query to select all from 'inmuebles' table
$sql = "SELECT * FROM inmuebles WHERE noticiastate = 1";
$result = $conn->query($sql);

$inmuebles = array();

if ($result->num_rows > 0) {
    // Fetch all data and store in the 'inmuebles' array
    while($row = $result->fetch_assoc()) {
        $inmuebles[] = $row;
    }
} else {
    echo json_encode(["error" => "No results found"]); // Return an error message if no results
}

// Close the database connection
$conn->close();

// Return the 'inmuebles' array as JSON
echo json_encode($inmuebles);
?>
