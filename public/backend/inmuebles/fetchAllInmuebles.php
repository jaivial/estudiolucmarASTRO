<?php
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

global $conn;

$sql = "SELECT * FROM inmuebles where AgrupacionChild = '1'";
$result = $conn->query($sql);
$childs = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $childs[] = $row;
    }
}

$sql = "SELECT * FROM inmuebles where AgrupacionParent = '1'";
$result = $conn->query($sql);
$parents = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $parents[] = $row;
    }
}

$response = array("childs" => $childs, "parents" => $parents);

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);


// Close the database connection
$conn->close();