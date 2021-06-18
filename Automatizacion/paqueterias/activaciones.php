<?php

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

include_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/classes/Cookie.php';
include '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/init.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);
if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}
$id_carrier = $decodedT[id_carrier];
$id_address = $decodedT[id_address];
/*
1 - recoger en tienda
2 - Envio a domicilio
3 - Paquete express
 */
$id_referencia = 0;
$sql           = "SELECT id_reference FROM {$db_index}carrier WHERE id_carrier = {$id_carrier}";
$result        = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_referencia = $row[id_reference];
    }
}
$pasa = false;
if ($id_referencia == 1) {
    $sql = "SELECT cpg.municipio, cpg.estado, cpg.ciudad FROM codigos_postales_generales cpg ";
    $sql .= "INNER JOIN prstshp_address pa on pa.postcode = cpg.codigo_postal AND pa.colony = cpg.asentamiento ";
    $sql .= "WHERE pa.id_address = {$id_address}";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        while ($row = $result->fetch_assoc()) {
            if ($row[municipio] == 'Chihuahua' and $row[estado] == 'Chihuahua' and $row[ciudad] == 'Chihuahua') {
                $pasa = true;
            }
        }
    }
    $respuesta = [
        "id_referencia" => $id_referencia,
        "id_carrier"    => $id_carrier,
        "id_address"    => $id_address,
        "activo"        => $pasa,
    ];
    echo (json_encode($respuesta));
    die();
} else if ($id_referencia == 2) {
    $sql = "SELECT cpg.municipio, cpg.estado, cpg.ciudad FROM codigos_postales_generales cpg ";
    $sql .= "INNER JOIN prstshp_address pa on pa.postcode = cpg.codigo_postal AND pa.colony = cpg.asentamiento ";
    $sql .= "WHERE pa.id_address = {$id_address}";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        while ($row = $result->fetch_assoc()) {
            if ($row[municipio] == 'Chihuahua' and $row[estado] == 'Chihuahua' and $row[ciudad] == 'Chihuahua') {
                $pasa = true;
            }
        }
    }
    $respuesta = [
        "id_referencia" => $id_referencia,
        "id_carrier"    => $id_carrier,
        "id_address"    => $id_address,
        "activo"        => $pasa,
    ];
    echo (json_encode($respuesta));
    die();
} else if ($id_referencia == 3) {
    $respuesta = [
        "id_referencia" => $id_referencia,
        "id_carrier"    => $id_carrier,
        "id_address"    => $id_address,
        "activo"        => true,
    ];
    echo (json_encode($respuesta));
    die();
}
