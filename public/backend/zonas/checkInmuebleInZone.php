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

// Function to check if bounding box is within any zone
function checkBoundingBoxInZones()
{
    global $conn;

    $sqlInmuebles = "SELECT id, coordinates FROM inmuebles";
    $resultInmuebles = $conn->query($sqlInmuebles);

    $sqlZones = "SELECT code_id, zone_name, latlngs FROM map_zones";
    $resultZones = $conn->query($sqlZones);

    if ($resultInmuebles->num_rows > 0 && $resultZones->num_rows > 0) {
        $zones = array();
        while ($row = $resultZones->fetch_assoc()) {
            $zones[] = array(
                'code_id' => $row['code_id'],
                'zone_name' => $row['zone_name'],
                'latlngs' => json_decode($row['latlngs'], true)
            );
        }

        $inmueblesInZones = array();
        while ($rowInmueble = $resultInmuebles->fetch_assoc()) {
            $coordinates = json_decode($rowInmueble['coordinates'], true);
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
                        'zone_name' => $zone['zone_name']
                    );
                }
            }
        }

        // Update inmuebles table with zone_name
        foreach ($inmueblesInZones as $inmuebleZone) {
            $inmueble_id = $inmuebleZone['inmueble_id'];
            $zone_name = $inmuebleZone['zone_name'];

            $sqlUpdate = "UPDATE inmuebles SET zona = '$zone_name' WHERE id = $inmueble_id";
            $conn->query($sqlUpdate);
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
