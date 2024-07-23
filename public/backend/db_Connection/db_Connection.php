<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Define global database connection variables
$host = "127.0.0.1";
$port = "3308";
$user = "root";
$password = "";
$database = "u212050690_estudiolucmar";



$conn = new mysqli($host, $user, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
