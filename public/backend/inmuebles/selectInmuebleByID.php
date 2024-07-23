<?php
// Output the response as JSON
header('Content-Type: application/json');
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// Prepare and execute the query for 'inmuebles' table
$sql_inmuebles = "SELECT direccion, numero FROM inmuebles WHERE id = ?";
if ($stmt_inmuebles = $conn->prepare($sql_inmuebles)) {
    $stmt_inmuebles->bind_param("i", $id);
    $stmt_inmuebles->execute();
    $result_inmuebles = $stmt_inmuebles->get_result();
    $inmueble_data = $result_inmuebles->fetch_assoc();
    $stmt_inmuebles->close();
} else {
    die("Error preparing inmuebles statement: " . $conn->error);
}

// Close the connection
$conn->close();

// Combine data into a single array
$data = array(
    'inmueble' => $inmueble_data,
);


echo json_encode($data);