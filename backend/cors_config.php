<?php
// Set allowed origins
$allowedOrigins = [
    'http://localhost:4321',
    'https://estudiolucmar.com',
    'http://localhost:8000',
];

// Function to handle CORS headers
function handleCorsHeaders()
{


    // Check if the request's origin is in the list of allowed origins
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    global $allowedOrigins;

    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    // Handle OPTIONS request (preflight)
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit;
    }

    // Set content type
    header('Content-Type: application/json');
}
