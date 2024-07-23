<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['nombreCompletoAntiguo'], $_GET['nombreCompletoNuevo'])) {
    $nombreCompletoAntiguo = $_GET['nombreCompletoAntiguo'];
    $nombreCompletoNuevo = $_GET['nombreCompletoNuevo'];

    // Update comercial_noticia in table noticia
    $stmt_noticia = $conn->prepare("UPDATE noticia SET comercial_noticia = ? WHERE comercial_noticia = ?");
    if ($stmt_noticia === false) {
        echo json_encode(array("success" => false, "message" => "Prepare failed for noticia: " . $conn->error));
        exit;
    }

    $stmt_noticia->bind_param("ss", $nombreCompletoNuevo, $nombreCompletoAntiguo);

    if ($stmt_noticia->execute()) {
        if ($stmt_noticia->affected_rows > 0) {
            $noticia_message = "Registro actualizado correctamente en tabla noticia";
        } else {
            $noticia_message = "No records updated in tabla noticia";
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Error actualizando registro en tabla noticia: " . $stmt_noticia->error));
        $stmt_noticia->close();
        $conn->close();
        exit;
    }

    $stmt_noticia->close();

    // Update responsable in table inmuebles
    $stmt_inmuebles = $conn->prepare("UPDATE inmuebles SET responsable = ? WHERE responsable = ?");
    if ($stmt_inmuebles === false) {
        echo json_encode(array("success" => false, "message" => "Prepare failed for inmuebles: " . $conn->error));
        exit;
    }

    $stmt_inmuebles->bind_param("ss", $nombreCompletoNuevo, $nombreCompletoAntiguo);

    if ($stmt_inmuebles->execute()) {
        if ($stmt_inmuebles->affected_rows > 0) {
            $inmuebles_message = "Registro actualizado correctamente en tabla inmuebles";
        } else {
            $inmuebles_message = "No records updated in tabla inmuebles";
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Error actualizando registro en tabla inmuebles: " . $stmt_inmuebles->error));
        $stmt_inmuebles->close();
        $conn->close();
        exit;
    }

    $stmt_inmuebles->close();

    // Update comercial_encargo in table encargos
    $stmt_encargos = $conn->prepare("UPDATE encargos SET comercial_encargo = ? WHERE comercial_encargo = ?");
    if ($stmt_encargos === false) {
        echo json_encode(array("success" => false, "message" => "Prepare failed for encargos: " . $conn->error));
        exit;
    }

    $stmt_encargos->bind_param("ss", $nombreCompletoNuevo, $nombreCompletoAntiguo);

    if ($stmt_encargos->execute()) {
        if ($stmt_encargos->affected_rows > 0) {
            $encargos_message = "Registro actualizado correctamente en tabla encargos";
        } else {
            $encargos_message = "No records updated in tabla encargos";
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Error actualizando registro en tabla encargos: " . $stmt_encargos->error));
        $stmt_encargos->close();
        $conn->close();
        exit;
    }

    $stmt_encargos->close();

    echo json_encode(array("success" => true, "message" => array("noticia" => $noticia_message, "inmuebles" => $inmuebles_message, "encargos" => $encargos_message)));
} else {
    echo json_encode(array("success" => false, "message" => "Solicitud inválida"));
}

$conn->close();
