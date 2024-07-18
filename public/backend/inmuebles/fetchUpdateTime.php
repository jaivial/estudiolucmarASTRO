<?php
// Output the response as JSON
header('Content-Type: application/json');
require_once '../cors_config.php';

require_once '../db_Connection/db_Connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

// Prepare and execute the query for 'inmuebles' table
$sql_inmuebles = "SELECT * FROM inmuebles WHERE id = ?";
if ($stmt_inmuebles = $conn->prepare($sql_inmuebles)) {
    $stmt_inmuebles->bind_param("i", $id);
    $stmt_inmuebles->execute();
    $result_inmuebles = $stmt_inmuebles->get_result();
    $inmueble_data = $result_inmuebles->fetch_assoc();
    $stmt_inmuebles->close();
} else {
    die("Error preparing inmuebles statement: " . $conn->error);
}

// Prepare and execute the query for 'comentarios' table
$sql_comentarios = "SELECT id, texto, date_time, comentario_id FROM comentarios WHERE comentario_id = ?";
if ($stmt_comentarios = $conn->prepare($sql_comentarios)) {
    $stmt_comentarios->bind_param("i", $id);
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

// Close the connection
$conn->close();

// Combine data into a single array
$data = array(
    'inmueble' => $inmueble_data,
    'comentarios' => $comentarios_data,
    'dataUpdateTime' => $dataUpdateTime
);


echo json_encode($data);