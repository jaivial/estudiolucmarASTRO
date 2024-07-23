<?php
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Retrieve GET parameters
$direccion = isset($_GET['direccion']) ? $conn->real_escape_string($_GET['direccion']) : null;
$tipo = isset($_GET['tipo']) ? $conn->real_escape_string($_GET['tipo']) : null;
$uso = isset($_GET['uso']) ? $conn->real_escape_string($_GET['uso']) : null;
$anoConstruccion = isset($_GET['anoConstruccion']) ? intval($_GET['anoConstruccion']) : null;
$superficie = isset($_GET['superficie']) ? floatval($_GET['superficie']) : null;
$categoriaOcupacion = isset($_GET['categoriaOcupacion']) ? $conn->real_escape_string($_GET['categoriaOcupacion']) : null;
$coordinates = isset($_GET['coordinates']) ? $_GET['coordinates'] : null;

if ($direccion && $tipo && $uso && $anoConstruccion && $superficie && $categoriaOcupacion && $coordinates && is_array($coordinates) && count($coordinates) === 2) {
    $latitude = $conn->real_escape_string($coordinates[0]);
    $longitude = $conn->real_escape_string($coordinates[1]);
    $coordinatesJson = json_encode([$latitude, $longitude]);

    // Insert the new inmueble
    $insertInmuebleSql = "INSERT INTO inmuebles (direccion, tipo, uso, ano_construccion, superficie, categoria, coordinates)
                          VALUES ('$direccion', '$tipo', '$uso', $anoConstruccion, $superficie, '$categoriaOcupacion', '$coordinatesJson')";
    if ($conn->query($insertInmuebleSql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
}

// Close the database connection
$conn->close();