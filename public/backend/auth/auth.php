<?php
header("Access-Control-Allow-Origin: http://localhost:4321");
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once '../db_Connection/db_Connection.php';


// Check if request method is GET and email/password parameters are set
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $password = $_GET['password'];

    // SQL query to select user based on email and password
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $data['email'] = $row['email'];
        $data['name'] = $row['nombre'];
        $data['password'] = $row['password'];
        $data['last_name'] = $row['apellido'];
        $data['user_id'] = $row['user_id'];
        //Set each row as a cookie with an expiration time of 2 hours
        setcookie("userID", $row['user_id'], time() + 7200, path: '/');
        //Set row 'nombre' as a cookie with an expiration time of 2 hours
        setcookie("nombre", $row['nombre'], time() + 7200, path: '/');
        //Set row 'apellido' as a cookie with an expiration time of 2 hours
        setcookie("apellido", $row['apellido'], time() + 7200, path: '/');


        $response = array('success' => true, 'message' => 'user found', 'data' => $data);
        echo json_encode($response);
    } else {
        $response = array('success' => false, 'message' => 'user not found');
        echo json_encode($response);
    }
    $conn->close();
} else {
    header('Content-Type: application/json');
    $response = array('success' => false, 'error' => 'Invalid request');
    echo json_encode($response);
}