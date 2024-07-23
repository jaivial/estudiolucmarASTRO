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

// Function to check if bounding box or single point is within any zone
function checkBoundingBoxInZones()
{
    global $conn;

    $sqlInmuebles = "SELECT id, coordinates FROM inmuebles";
    $resultInmuebles = $conn->query($sqlInmuebles);

    $sqlZones = "SELECT code_id, zone_name, latlngs, zone_responsable FROM map_zones"; // Include zone_responsable
    $resultZones = $conn->query($sqlZones);

    if ($resultInmuebles->num_rows > 0 && $resultZones->num_rows > 0) {
        $zones = array();
        while ($row = $resultZones->fetch_assoc()) {
            $zones[] = array(
                'code_id' => $row['code_id'],
                'zone_name' => $row['zone_name'],
                'latlngs' => json_decode($row['latlngs'], true),
                'zone_responsable' => $row['zone_responsable'] // Include zone_responsable in the zones array
            );
        }

        $inmueblesInZones = array();
        $inmueblesIdsInZones = array(); // To keep track of inmueble IDs that are in zones

        while ($rowInmueble = $resultInmuebles->fetch_assoc()) {
            $coordinates = json_decode($rowInmueble['coordinates'], true);

            if (count($coordinates) == 2) {
                // Handle single point case
                $point = array('lat' => $coordinates[0], 'lng' => $coordinates[1]);

                foreach ($zones as $zone) {
                    $latlngs = $zone['latlngs'][0]; // Get the first polygon from latlngs
                    if (isPointInPolygon($point, $latlngs)) {
                        $inmueblesInZones[] = array(
                            'inmueble_id' => $rowInmueble['id'],
                            'zone_id' => $zone['code_id'],
                            'zone_name' => $zone['zone_name'],
                            'zone_responsable' => $zone['zone_responsable']
                        );
                        $inmueblesIdsInZones[] = $rowInmueble['id']; // Add to the list of IDs in zones
                        break; // No need to check other zones if already inside one
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
                    $latlngs = $zone['latlngs'][0]; // Get the first polygon from latlngs
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
                            'zone_responsable' => $zone['zone_responsable']
                        );
                        $inmueblesIdsInZones[] = $rowInmueble['id']; // Add to the list of IDs in zones
                        break; // No need to check other zones if already inside one
                    }
                }
            }
        }

        // Update inmuebles table with zone_name and responsable
        foreach ($inmueblesInZones as $inmuebleZone) {
            $inmueble_id = $inmuebleZone['inmueble_id'];
            $zone_name = $inmuebleZone['zone_name'];
            $zone_responsable = $inmuebleZone['zone_responsable'];

            $sqlUpdate = "UPDATE inmuebles SET zona = '$zone_name', responsable = '$zone_responsable' WHERE id = $inmueble_id";
            $conn->query($sqlUpdate);
        }

        // Update inmuebles table to set zona and responsable to null for those not in zones
        if (!empty($inmueblesIdsInZones)) {
            $inmueblesIdsInZonesStr = implode(',', $inmueblesIdsInZones);
            $sqlUpdateNull = "UPDATE inmuebles SET zona = NULL, responsable = NULL WHERE id NOT IN ($inmueblesIdsInZonesStr)";
        } else {
            $sqlUpdateNull = "UPDATE inmuebles SET zona = NULL, responsable = NULL";
        }
        $conn->query($sqlUpdateNull);

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