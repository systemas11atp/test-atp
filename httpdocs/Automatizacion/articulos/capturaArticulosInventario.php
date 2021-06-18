<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];

include_once '/classes/Cookie.php';
include '/init.php';
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
date_default_timezone_set("America/Chihuahua");
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

$content = trim(file_get_contents("php://input"));

$decodedT = json_decode($content, true);

if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id_product           = $decodedT[id_product];
$id_product_attribute = $decodedT[id_product_attribute];
$stock                = $decodedT[stock];
$sku                  = $decodedT[sku];
$nombre               = $decodedT[nombre];

$texto = "sku : {$sku}, id_product : {$id_product}, id_product_attribute : {$id_product_attribute}, nombre : {$nombre}, stock : {$stock}";
//AND act_existencia < '{$fecha}'
$fecha = date("Y-m-d") . " 12:00:00";
$sql   = "UPDATE prstshp_stock_available  SET quantity = {$stock}, actualizado = '{$fecha}' WHERE reference = '{$sku}'";
capuraLogs::nuevo_log("---capturaArticulosInventario sql : {$sql}");
capuraLogs::nuevo_log("{$texto}");
if ($conn->query($sql)) {
    echo "true";
} else {
    echo "false";
}
