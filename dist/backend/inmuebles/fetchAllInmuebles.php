<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

global $conn;

function calculateDataUpdateTime($inmuebleId)
{
    global $conn;
    $sql_comentarios = "SELECT date_time FROM comentarios WHERE comentario_id = ?";
    $dataUpdateTime = 'red'; // Default to 'red' if there are no comments

    if ($stmt_comentarios = $conn->prepare($sql_comentarios)) {
        $stmt_comentarios->bind_param("i", $inmuebleId);
        $stmt_comentarios->execute();
        $result_comentarios = $stmt_comentarios->get_result();
        $most_recent_comment = '0000-00-00 00:00:00'; // Initialize with an old default date

        while ($comment = $result_comentarios->fetch_assoc()) {
            if ($comment['date_time'] > $most_recent_comment) {
                $most_recent_comment = $comment['date_time'];
            }
        }

        $stmt_comentarios->close();

        if ($most_recent_comment !== '0000-00-00 00:00:00') {
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
    } else {
        die("Error preparing comentarios statement: " . $conn->error);
    }

    return $dataUpdateTime;
}

function fetchProperties($conn, $agrupacionType)
{
    $sql = "SELECT * FROM inmuebles WHERE $agrupacionType = '1'";
    $result = $conn->query($sql);
    if (!$result) {
        die("Error executing query: " . $conn->error);
    }
    $properties = array();
    while ($row = $result->fetch_assoc()) {
        $row['dataUpdateTime'] = calculateDataUpdateTime($row['id']);
        $properties[] = $row;
    }
    return $properties;
}



$childsEdificio = fetchProperties($conn, 'ChildEdificio');
$parentsEdificio = fetchProperties($conn, 'ParentEdificio', 'Edificio');
$childsEscalera = fetchProperties($conn, 'ChildEscalera');
$parentsEscalera = fetchProperties($conn, 'ParentEscalera', 'Escalera');

$response = array("childsedificio" => $childsEdificio, "parentsedificio" => $parentsEdificio, "childsescalera" => $childsEscalera, "parentsescalera" => $parentsEscalera);

// Output the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$conn->close();