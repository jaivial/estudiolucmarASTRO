<?php
require_once '../cors_config.php';

// Call the function to handle CORS headers
handleCorsHeaders();

require_once '../db_Connection/db_Connection.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['responsable'])) {
    $responsable = $_GET['responsable'];

    // Initialize response arrays
    $inmuebles = [];
    $inmueblesconnoticia = [];
    $inmueblesconencargos = [];
    $totalInmuebles = 0;

    // Query for inmuebles
    $stmt = $conn->prepare("SELECT * FROM inmuebles WHERE responsable = ?");
    if ($stmt !== false) {
        $stmt->bind_param("s", $responsable);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $totalInmuebles++;

                    // Query for the latest comment date_time for this inmueble
                    $stmtComment = $conn->prepare("SELECT MAX(date_time) as latest_comment_time FROM comentarios WHERE comentario_id = ?");
                    if ($stmtComment !== false) {
                        $stmtComment->bind_param("i", $row['id']);
                        $stmtComment->execute();
                        $resultComment = $stmtComment->get_result();
                        $commentupdatetime = 'red';

                        if ($resultComment->num_rows > 0) {
                            $commentRow = $resultComment->fetch_assoc();
                            $latestCommentTime = $commentRow['latest_comment_time'];

                            if ($latestCommentTime) {
                                $currentDateTime = new DateTime();
                                $commentDateTime = new DateTime($latestCommentTime);
                                $interval = $currentDateTime->diff($commentDateTime);

                                if ($interval->days <= 30) {
                                    $commentupdatetime = 'green';
                                } elseif ($interval->days <= 90) {
                                    $commentupdatetime = 'yellow';
                                }
                            }
                        }
                        $stmtComment->close();
                    }

                    $inmueble = [
                        'id' => $row["id"],
                        'direccion' => $row["direccion"],
                        'tipo' => $row["tipo"],
                        'uso' => $row["uso"],
                        'superficie' => $row["superficie"],
                        'ano_construccion' => $row["ano_construccion"],
                        'categoria' => $row["categoria"],
                        'potencialAdquisicion' => $row["potencialAdquisicion"],
                        'noticiastate' => $row["noticiastate"],
                        'responsable' => $row["responsable"],
                        'commentupdatetime' => $commentupdatetime
                    ];

                    $inmuebles[] = $inmueble;
                }
            }
            $stmt->close();
        }
    }

    // Query for noticias with joined inmuebles
    $stmtNoticia = $conn->prepare("
        SELECT noticia.*, inmuebles.direccion 
        FROM noticia 
        JOIN inmuebles ON noticia.noticia_id = inmuebles.id 
        WHERE noticia.comercial_noticia = ?
    ");
    if ($stmtNoticia !== false) {
        $stmtNoticia->bind_param("s", $responsable);
        if ($stmtNoticia->execute()) {
            $resultNoticia = $stmtNoticia->get_result();
            if ($resultNoticia->num_rows > 0) {
                while ($noticiaRow = $resultNoticia->fetch_assoc()) {
                    $noticiaupdatetime = 'red';

                    if ($noticiaRow['noticia_fecha']) {
                        $currentDateTime = new DateTime();
                        $noticiaDateTime = new DateTime($noticiaRow['noticia_fecha']);
                        $interval = $currentDateTime->diff($noticiaDateTime);

                        if ($interval->days <= 30) {
                            $noticiaupdatetime = 'green';
                        } elseif ($interval->days <= 90) {
                            $noticiaupdatetime = 'yellow';
                        }
                    }

                    $inmueblesconnoticia[] = array_merge($noticiaRow, ['noticiaupdatetime' => $noticiaupdatetime]);
                }
            }
            $stmtNoticia->close();
        }
    }

    // Query for encargos with joined inmuebles
    $stmtEncargo = $conn->prepare("
        SELECT encargos.*, inmuebles.direccion 
        FROM encargos 
        JOIN inmuebles ON encargos.encargo_id = inmuebles.id 
        WHERE encargos.comercial_encargo = ?
    ");
    if ($stmtEncargo !== false) {
        $stmtEncargo->bind_param("s", $responsable);
        if ($stmtEncargo->execute()) {
            $resultEncargo = $stmtEncargo->get_result();
            if ($resultEncargo->num_rows > 0) {
                while ($encargoRow = $resultEncargo->fetch_assoc()) {
                    $encargoupdatetime = 'red';

                    if ($encargoRow['encargo_fecha']) {
                        $currentDateTime = new DateTime();
                        $encargoDateTime = new DateTime($encargoRow['encargo_fecha']);
                        $interval = $currentDateTime->diff($encargoDateTime);

                        if ($interval->days <= 30) {
                            $encargoupdatetime = 'green';
                        } elseif ($interval->days <= 90) {
                            $encargoupdatetime = 'yellow';
                        }
                    }

                    $inmueblesconencargos[] = array_merge($encargoRow, ['encargoupdatetime' => $encargoupdatetime]);
                }
            }
            $stmtEncargo->close();
        }
    }

    // Calculate percentage of noticiastate
    $percentageNoticiastate = ($totalInmuebles > 0) ? (count($inmueblesconnoticia) / $totalInmuebles) * 100 : 0;

    // Calculate percentage of encargostate
    $percentageEncargostate = ($totalInmuebles > 0) ? (count($inmueblesconencargos) / $totalInmuebles) * 100 : 0;

    $conn->close();

    // Return response
    echo json_encode(array(
        "success" => true,
        "data" => $inmuebles,
        "inmueblesconnoticia" => $inmueblesconnoticia,
        "inmueblesconencargos" => $inmueblesconencargos,
        "totalInmuebles" => $totalInmuebles,
        "percentageNoticiastate" => $percentageNoticiastate,
        "percentageEncargostate" => $percentageEncargostate
    ));
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request method or missing responsable parameter."));
}
