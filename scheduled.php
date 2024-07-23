<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Define global database connection variables
$host = "localhost";
$port = "3306";
$user = "u212050690_estudiolucmar";
$password = "estudioLucmar_4321";
$database = "u212050690_estudiolucmar";



$conn = new mysqli($host, $user, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



// Prepare SQL query to delete old sessions
$sql = "DELETE FROM active_sessions WHERE date_time < DATE_SUB(NOW(), INTERVAL 5 HOUR);";


// Bind parameter
if ($stmt = $conn->prepare($sql)) {

    // Execute statement
    if ($stmt->execute()) {
        echo "Deleted old sessions successfully.";
    } else {
        echo "Error executing delete statement: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
} else {
    echo "Error preparing delete statement: " . $mysqli->error;
}

$conn->close();


echo 'hello';
