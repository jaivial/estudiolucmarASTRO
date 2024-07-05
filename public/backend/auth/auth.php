<?php
header("Access-Control-Allow-Origin: http://localhost:4321");
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);

function authenticate($email, $password)
{
    require_once '../db_Connection/db_Connection.php';
    global $conn;


    // Check if request method is GET and email/password parameters are set
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email']) && isset($_GET['password'])) {
        $email = $_GET['email'];
        $password = $_GET['password'];

        // SQL query to select user based on email and password
        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            // Set session cookie lifetime to 5 hours (5 * 60 * 60 seconds)
            $session_lifetime = 5 * 60 * 60;
            session_set_cookie_params($session_lifetime);
            session_start();
            if (
                !isset($_SESSION['user_id']) &&
                !isset($_SESSION['email']) &&
                !isset($_SESSION['name']) &&
                !isset($_SESSION['password']) &&
                !isset($_SESSION['last_name']) &&
                !isset($_SESSION['hashLogin'])
            ) {
                $data = array();
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['name'] = $row['nombre'];
                    $_SESSION['password'] = $row['password'];
                    $_SESSION['last_name'] = $row['apellido'];
                    $_SESSION['hashLogin'] = session_id();
                }
            }
            return true;
        } else {
            return false;
        }
        $conn->close();
    } else {
        header('Content-Type: application/json');
        $response = array('success' => false, 'error' => 'Invalid request');
        echo json_encode($response);
    }
}