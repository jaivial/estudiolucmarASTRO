<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve parameters if needed (you might not need any parameters for this endpoint)

// Prepare SQL query to fetch agrupaciones data
$sql_agrupaciones = "SELECT * FROM Agrupaciones";
$result_agrupaciones = $conn->query($sql_agrupaciones);

$agrupaciones = array();
if ($result_agrupaciones->num_rows > 0) {
    while ($row = $result_agrupaciones->fetch_assoc()) {
        // Decode the inmueblesAgrupados JSON to get IDs
        $inmueblesAgrupados = json_decode($row['inmueblesAgrupados'], true);
        $row['inmueblesAgrupados'] = $inmueblesAgrupados;

        // Fetch associated inmuebles data
        $inmuebles = array();
        if (!empty($inmueblesAgrupados)) {
            $ids = implode(',', array_map('intval', $inmueblesAgrupados));
            $sql_inmuebles = "SELECT * FROM inmuebles WHERE id IN ($ids)";
            $result_inmuebles = $conn->query($sql_inmuebles);
            if ($result_inmuebles->num_rows > 0) {
                while ($inmueble = $result_inmuebles->fetch_assoc()) {
                    $inmuebles[] = $inmueble;
                }
            }
        }

        $row['inmuebles'] = $inmuebles;
        $agrupaciones[] = $row;
    }
}

// Return agrupaciones data with associated inmuebles
header('Content-Type: application/json');
echo json_encode($agrupaciones);

// Close the database connection
$conn->close();