<?php
require_once '../cors_config.php';


// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

// Retrieve parameters from the query string
$direccion = isset($_GET['direccion']) ? strtolower($_GET['direccion']) : '';
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Prepare SQL query to count total rows
if ($direccion == '') {
    $sql_total = "SELECT COUNT(*) AS total FROM inmuebles WHERE encargoState = 1";
} else {
    $sql_total = "SELECT COUNT(*) AS total FROM inmuebles WHERE encargoState = 1 AND LOWER(direccion) LIKE '%" . $direccion . "%'";
}
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$totalRows = $row_total['total'];

// Calculate total number of pages
$totalPages = ceil($totalRows / $itemsPerPage);

// Calculate the starting index of items for the current page
$startIndex = ($currentPage - 1) * $itemsPerPage;

// Prepare SQL query to fetch paginated data
if ($direccion == '') {
    $sql_data = "SELECT * FROM inmuebles WHERE encargoState = 1 LIMIT $startIndex, $itemsPerPage";
} else {
    $sql_data = "SELECT * FROM inmuebles WHERE encargoState = 1 AND LOWER(direccion) LIKE '%" . $direccion . "%' LIMIT $startIndex, $itemsPerPage";
}
$result_data = $conn->query($sql_data);

// Prepare paginated data array
$data = array();
if ($result_data->num_rows > 0) {
    while ($row = $result_data->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return paginated data along with pagination information
$response = array(
    "currentPage" => $currentPage,
    "itemsPerPage" => $itemsPerPage,
    "totalItems" => $totalRows,
    "totalPages" => $totalPages,
    "data" => $data
);

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$conn->close();
