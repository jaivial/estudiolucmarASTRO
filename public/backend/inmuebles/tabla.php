<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve parameters from the query string
$direccion = isset($_GET['direccion']) ? strtolower($_GET['direccion']) : '';
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Prepare SQL query to count total rows
$sql_total = "SELECT COUNT(*) AS total FROM inmuebles WHERE ChildEdificio IS NULL AND ChildEscalera IS NULL AND ParentEscalera IS NULL";
if ($direccion != '') {
    $sql_total .= " AND LOWER(direccion) LIKE ?";
}

$stmt_total = $conn->prepare($sql_total);
if ($direccion != '') {
    $like_direccion = '%' . $direccion . '%';
    $stmt_total->bind_param('s', $like_direccion);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
$totalRows = $row_total['total'];

// Calculate total number of pages
$totalPages = ceil($totalRows / $itemsPerPage);

// Calculate the starting index of items for the current page
$startIndex = ($currentPage - 1) * $itemsPerPage;

// Prepare SQL query to fetch paginated data
$sql_data = "SELECT * FROM inmuebles WHERE ChildEdificio IS NULL AND ChildEscalera IS NULL AND ParentEscalera IS NULL";
if ($direccion != '') {
    $sql_data .= " AND LOWER(direccion) LIKE ?";
}
$sql_data .= " LIMIT ?, ?";

$stmt_data = $conn->prepare($sql_data);
if ($direccion != '') {
    $stmt_data->bind_param('sii', $like_direccion, $startIndex, $itemsPerPage);
} else {
    $stmt_data->bind_param('ii', $startIndex, $itemsPerPage);
}
$stmt_data->execute();
$result_data = $stmt_data->get_result();

// Prepare paginated data array
$data = array();
while ($row = $result_data->fetch_assoc()) {
    // Retrieve comments for the current inmueble
    $sql_comentarios = "SELECT date_time FROM comentarios WHERE comentario_id = ?";
    if ($stmt_comentarios = $conn->prepare($sql_comentarios)) {
        $stmt_comentarios->bind_param("i", $row['id']);
        $stmt_comentarios->execute();
        $result_comentarios = $stmt_comentarios->get_result();
        $comentarios_data = $result_comentarios->fetch_all(MYSQLI_ASSOC);
        $stmt_comentarios->close();
    } else {
        die("Error preparing comentarios statement: " . $conn->error);
    }

    // Calculate dataUpdateTime based on the date_time of the comments
    $dataUpdateTime = 'red'; // Default to 'red' if there are no comments
    if (!empty($comentarios_data)) {
        $most_recent_comment = $comentarios_data[0]['date_time'];
        foreach ($comentarios_data as $comment) {
            if ($comment['date_time'] > $most_recent_comment) {
                $most_recent_comment = $comment['date_time'];
            }
        }

        $most_recent_datetime = new DateTime($most_recent_comment);
        $current_datetime = new DateTime();
        $interval = $current_datetime->diff($most_recent_datetime);
        $days_passed = $interval->days;

        if ($days_passed > 90) {
            $dataUpdateTime = 'red';
        } elseif ($days_passed > 30) {
            $dataUpdateTime = 'yellow';
        } else {
            $dataUpdateTime = 'green';
        }
    }

    // Add dataUpdateTime and comments to the inmueble data
    $row['dataUpdateTime'] = $dataUpdateTime;
    $data[] = $row;
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