<?php

// Database connection parameters
$host = "srv1353.hstgr.io";
$port = "3306";
$user = "u212050690_Jaivial";
$password = "Jva_Mvc_5171";
$database = "u212050690_Astrodatabase";

// Create connection
$conn = new mysqli($host, $user, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}
?>