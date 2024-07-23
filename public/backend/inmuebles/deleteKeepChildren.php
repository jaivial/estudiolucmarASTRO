<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection file
require_once '../cors_config.php';
require_once '../db_Connection/db_Connection.php';

// Check if inmuebles parameter is provided
if (!isset($_GET['inmuebles'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$inmuebles = $_GET['inmuebles'];
$parentdata = $_GET['parentdata'];
$parentIds = implode(',', array_map('intval', $parentdata)); // Convert array to comma-separated list of integers
$inmueblesIds = implode(',', array_map('intval', $inmuebles)); // Convert array to comma-separated list of integers

// Function to update ChildEdificio records
function updateChildEdificio($conn, $inmueblesIds)
{
    $checkEdificioSql = "SELECT id FROM inmuebles WHERE id IN ($inmueblesIds) AND ChildEdificio = 1";
    $result = $conn->query($checkEdificioSql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $updateEdificioSql = "UPDATE inmuebles SET ChildEdificio = NULL, AgrupacionID_Edificio = NULL, TipoAgrupacion = NULL WHERE id = $row[id] AND ChildEdificio = 1 AND ParentEscalera IS NULL";
            if (!$conn->query($updateEdificioSql)) {
                throw new Exception('Error updating ChildEdificio records');
            }
        }
    }
}

// Function to update ChildEscalera records
function updateChildEscalera($conn, $inmueblesIds)
{
    $checkEscaleraSql = "SELECT id FROM inmuebles WHERE id IN ($inmueblesIds) AND ChildEscalera = 1";
    $result = $conn->query($checkEscaleraSql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $updateEscaleraSql = "UPDATE inmuebles SET ChildEscalera = NULL, AgrupacionID_Escalera = NULL, TipoAgrupacion = NULL WHERE id = $row[id] AND ChildEscalera = 1";
            if (!$conn->query($updateEscaleraSql)) {
                throw new Exception('Error updating ChildEscalera records');
            }
        }
    }
}

// Function to delete ParentEscalera records
function deleteParentEscalera($conn, $inmueblesIds)
{
    $checkParentEscalera = "SELECT id FROM inmuebles WHERE id IN ($inmueblesIds) AND ParentEscalera = 1";
    $result = $conn->query($checkParentEscalera);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $deleteSql = "DELETE FROM inmuebles WHERE id = $row[id] AND ParentEscalera = 1";
            if (!$conn->query($deleteSql)) {
                throw new Exception('Error deleting records with ParentEscalera');
            }
        }
    }
}

// Function to delete ParentEdificio records
function deleteParentEdificio($conn, $parentdata)
{
    $checkParentEdificio = "SELECT id FROM inmuebles WHERE id IN ($parentdata) AND ParentEdificio = 1";
    $result = $conn->query($checkParentEdificio);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $deleteSql = "DELETE FROM inmuebles WHERE id = $row[id] AND ParentEdificio = 1";
            if (!$conn->query($deleteSql)) {
                throw new Exception('Error deleting records with ParentEdificio');
            }
        }
    }
}

// Begin transaction
$conn->begin_transaction();

try {
    // Execute each function
    updateChildEdificio($conn, $inmueblesIds);
    updateChildEscalera($conn, $inmueblesIds);
    deleteParentEscalera($conn, $inmueblesIds);
    deleteParentEdificio($conn, $parentIds);

    // Commit transaction
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Operations completed successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close the database connection
$conn->close();