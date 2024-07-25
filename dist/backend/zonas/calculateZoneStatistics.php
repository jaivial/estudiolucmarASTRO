<?php
require_once '../cors_config.php'; // Include CORS configuration if needed
require_once '../db_Connection/db_Connection.php'; // Include database connection

// Function to check if a point is inside a polygon
function isPointInPolygon($point, $polygon)
{
    $inside = false;
    $x = $point['lng'];
    $y = $point['lat'];
    $numPoints = count($polygon);
    $j = $numPoints - 1;

    for ($i = 0; $i < $numPoints; $j = $i++) {
        $xi = $polygon[$i]['lng'];
        $yi = $polygon[$i]['lat'];
        $xj = $polygon[$j]['lng'];
        $yj = $polygon[$j]['lat'];

        $intersect = (($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
        if ($intersect) {
            $inside = !$inside;
        }
    }

    return $inside;
}
function checkBoundingBoxInZones()
{
    global $conn;

    $sqlInmuebles = "SELECT id, coordinates, noticiastate, encargoState, categoria FROM inmuebles";
    $resultInmuebles = $conn->query($sqlInmuebles);

    $sqlZones = "SELECT code_id, zone_name, latlngs, zone_responsable FROM map_zones";
    $resultZones = $conn->query($sqlZones);

    if ($resultInmuebles->num_rows > 0 && $resultZones->num_rows > 0) {
        $zones = array();
        while ($row = $resultZones->fetch_assoc()) {
            $latlngs = $row['latlngs'];

            // Check if latlngs is not null and valid JSON
            if ($latlngs !== null) {
                $latlngs = json_decode($latlngs, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Error decoding JSON for latlngs: " . json_last_error_msg());
                    continue; // Skip this zone if JSON decoding fails
                }

                // Check if latlngs is an array and has at least one polygon
                if (is_array($latlngs) && !empty($latlngs)) {
                    $zones[] = array(
                        'code_id' => $row['code_id'],
                        'zone_name' => $row['zone_name'],
                        'latlngs' => $latlngs,
                        'zone_responsable' => $row['zone_responsable']
                    );
                } else {
                    error_log("Invalid format for latlngs: " . print_r($latlngs, true));
                }
            } else {
                error_log("latlngs is null for zone: " . $row['code_id']);
            }
        }

        $inmueblesInZones = array();

        while ($rowInmueble = $resultInmuebles->fetch_assoc()) {
            $coordinates = $rowInmueble['coordinates'];

            // Check if coordinates is not null and valid JSON
            if ($coordinates !== null) {
                $coordinates = json_decode($coordinates, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Error decoding JSON for coordinates: " . json_last_error_msg());
                    continue; // Skip this inmueble if JSON decoding fails
                }

                // Ensure coordinates is an array and has the expected format
                if (is_array($coordinates) && (count($coordinates) == 2 || count($coordinates) == 4)) {
                    if (count($coordinates) == 2) {
                        // Handle single point case
                        $point = array('lat' => $coordinates[0], 'lng' => $coordinates[1]);

                        foreach ($zones as $zone) {
                            $latlngs = $zone['latlngs'][0]; // Assume latlngs[0] is a polygon
                            if (isPointInPolygon($point, $latlngs)) {
                                $inmueblesInZones[] = array(
                                    'inmueble_id' => $rowInmueble['id'],
                                    'zone_id' => $zone['code_id'],
                                    'zone_name' => $zone['zone_name'],
                                    'zone_responsable' => $zone['zone_responsable'],
                                    'noticiastate' => $rowInmueble['noticiastate'],
                                    'encargoState' => $rowInmueble['encargoState'],
                                    'categoria' => $rowInmueble['categoria']
                                );
                                break;
                            }
                        }
                    } else {
                        // Handle bounding box case
                        $boundingBox = array(
                            array('lat' => $coordinates[0], 'lng' => $coordinates[2]), // top-left
                            array('lat' => $coordinates[0], 'lng' => $coordinates[3]), // top-right
                            array('lat' => $coordinates[1], 'lng' => $coordinates[2]), // bottom-left
                            array('lat' => $coordinates[1], 'lng' => $coordinates[3])  // bottom-right
                        );

                        foreach ($zones as $zone) {
                            $latlngs = $zone['latlngs'][0]; // Assume latlngs[0] is a polygon
                            $inside = false;
                            foreach ($boundingBox as $point) {
                                if (isPointInPolygon($point, $latlngs)) {
                                    $inside = true;
                                    break;
                                }
                            }
                            if ($inside) {
                                $inmueblesInZones[] = array(
                                    'inmueble_id' => $rowInmueble['id'],
                                    'zone_id' => $zone['code_id'],
                                    'zone_name' => $zone['zone_name'],
                                    'zone_responsable' => $zone['zone_responsable'],
                                    'noticiastate' => $rowInmueble['noticiastate'],
                                    'encargoState' => $rowInmueble['encargoState'],
                                    'categoria' => $rowInmueble['categoria']
                                );
                                break;
                            }
                        }
                    }
                } else {
                    error_log("Unexpected coordinates format: " . print_r($coordinates, true));
                }
            } else {
                error_log("Coordinates are null for inmueble: " . $rowInmueble['id']);
            }
        }

        return $inmueblesInZones;
    } else {
        return array(); // Return empty array if no zones or inmuebles found
    }
}



// Handle GET request to check bounding box in zones
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = checkBoundingBoxInZones();
    header('Content-Type: application/json');
    echo json_encode($result);
}