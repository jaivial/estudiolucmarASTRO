<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve parameters from the query string
$direccion = isset($_GET['direccion']) ? strtolower($_GET['direccion']) : '';
$zone = isset($_GET['zone']) ? strtolower($_GET['zone']) : '';
$responsable = isset($_GET['responsable']) ? strtolower($_GET['responsable']) : '';
$filterNoticia = isset($_GET['filterNoticia']) ? (int)$_GET['filterNoticia'] : 0;
$filterEncargo = isset($_GET['filterEncargo']) ? (int)$_GET['filterEncargo'] : 0;
$superficieMin = isset($_GET['superficieMin']) ? (int)$_GET['superficieMin'] : 0;
$superficieMax = isset($_GET['superficieMax']) ? (int)$_GET['superficieMax'] : 10000; // A high default value
$yearMin = isset($_GET['yearMin']) ? (int)$_GET['yearMin'] : 1900;
$yearMax = isset($_GET['yearMax']) ? (int)$_GET['yearMax'] : date('Y');
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$alphabeticalOrder = isset($_GET['alphabeticalOrder']) ? $_GET['alphabeticalOrder'] : 'none';

// Prepare SQL query to count total rows
$sql_total = "SELECT COUNT(*) AS total FROM inmuebles WHERE ChildEdificio IS NULL AND ChildEscalera IS NULL AND ParentEscalera IS NULL";

$conditions = [];
$params = [];
$types = ''; // Prepare a string to hold the types for binding parameters

if ($direccion !== '') {
    $conditions[] = "LOWER(direccion) LIKE ?";
    $params[] = '%' . $direccion . '%';
    $types .= 's';
}
if ($zone !== '') {
    $conditions[] = "LOWER(zona) LIKE ?";
    $params[] = '%' . $zone . '%';
    $types .= 's';
}
if ($responsable !== '') {
    $conditions[] = "LOWER(responsable) LIKE ?";
    $params[] = '%' . $responsable . '%';
    $types .= 's';
}
if ($filterNoticia) {
    $conditions[] = "noticiastate = ?";
    $params[] = 1;
    $types .= 'i';
}
if ($filterEncargo) {
    $conditions[] = "encargoState = ?";
    $params[] = 1;
    $types .= 'i';
}
if ($superficieMin || $superficieMax < 10000) {
    $conditions[] = "superficie BETWEEN ? AND ?";
    $params[] = $superficieMin;
    $params[] = $superficieMax;
    $types .= 'ii';
}
if ($yearMin || $yearMax < date('Y')) {
    $conditions[] = "ano_construccion BETWEEN ? AND ?";
    $params[] = $yearMin;
    $params[] = $yearMax;
    $types .= 'ii';
}

if (!empty($conditions)) {
    $sql_total .= " AND " . implode(' AND ', $conditions);
}

$stmt_total = $conn->prepare($sql_total);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
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

if (!empty($conditions)) {
    $sql_data .= " AND " . implode(' AND ', $conditions);
}

// Add alphabetical ordering if applicable
if ($alphabeticalOrder === 'asc') {
    $sql_data .= " ORDER BY direccion ASC";
} elseif ($alphabeticalOrder === 'desc') {
    $sql_data .= " ORDER BY direccion DESC";
}

// Append LIMIT for pagination
$sql_data .= " LIMIT ?, ?";

// Prepare parameter binding for SQL query
$types .= 'ii'; // Append types for LIMIT parameters
$params[] = $startIndex;
$params[] = $itemsPerPage;

$stmt_data = $conn->prepare($sql_data);
if ($stmt_data === false) {
    die("Error preparing data statement: " . $conn->error);
}

$stmt_data->bind_param($types, ...$params);
$stmt_data->execute();
$result_data = $stmt_data->get_result();

// Prepare paginated data array
$data = [];
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
$response = [
    "currentPage" => $currentPage,
    "itemsPerPage" => $itemsPerPage,
    "totalItems" => $totalRows,
    "totalPages" => $totalPages,
    "data" => $data
];

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$conn->close();