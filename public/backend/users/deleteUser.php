<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && isset($_GET['nombreCompletoAntiguo'])) {
    $id = $_GET['id'];
    $nombreCompletoAntiguo = $_GET['nombreCompletoAntiguo'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update 'inmuebles' table
        $stmt = $conn->prepare("UPDATE inmuebles SET responsable = NULL WHERE responsable = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed (inmuebles): " . $conn->error);
        }

        $stmt->bind_param("s", $nombreCompletoAntiguo);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (inmuebles): " . $stmt->error);
        }
        $stmt->close();

        // Update 'noticia' table
        $stmt = $conn->prepare("UPDATE noticia SET comercial_noticia = NULL WHERE comercial_noticia = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed (noticia): " . $conn->error);
        }

        $stmt->bind_param("s", $nombreCompletoAntiguo);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (noticia): " . $stmt->error);
        }
        $stmt->close();

        // Update 'encargos' table
        $stmt = $conn->prepare("UPDATE encargos SET comercial_encargo = NULL WHERE comercial_encargo = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed (encargos): " . $conn->error);
        }

        $stmt->bind_param("s", $nombreCompletoAntiguo);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (encargos): " . $stmt->error);
        }
        $stmt->close();

        // Delete from 'users' table
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed (users): " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed (users): " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            echo json_encode(array("success" => true, "message" => "User deleted successfully."));
        } else {
            echo json_encode(array("success" => false, "message" => "No user found with the provided ID."));
        }

        $stmt->close();

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo json_encode(array("success" => false, "message" => $e->getMessage()));
    }

    $conn->close();
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request method or missing parameters."));
}
