<?php
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
session_start(); // Start the session

require './auth.php';  // Include the authentication script

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email']) && isset($_GET['password'])) {
    $email = $_GET['email'];
    $contra = $_GET['password'];


    $user = authenticate($email, $contra);
    if ($user) {
        // Store user data in session variables
        $_SESSION['usuario'] = $email;
        $_SESSION['password'] = $contra;
        $_SESSION['admin'] = ($user['admin'] === '1') ? '1' : '0';
        $_SESSION['id'] = $user['id'];

        // Return success JSON response
        $response['status'] = 'success';
        $response['message'] = 'Login successful';
    } else {
        // Return failure JSON response
        $response['status'] = 'failure';
        $response['message'] = 'Invalid username or password';
    }

    // Output JSON response
  
    echo json_encode($response);
}
?>
