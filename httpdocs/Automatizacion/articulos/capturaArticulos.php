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
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);
if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$precio               = $decodedT[precio];
$referencia           = $decodedT[referencia];
$id_product           = $decodedT[id_product];
$id_product_attribute = $decodedT[id_product_attribute];
if ($id_product_attribute == 0) {
    $sql      = "UPDATE prstshp_product SET price = $precio WHERE id_product = {$id_product}";
    $sql_shop = "UPDATE prstshp_product_shop SET price = $precio WHERE id_product = {$id_product}";
} else {
    $sql      = "UPDATE prstshp_product_attribute SET price = $precio WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute}";
    $sql_shop = "UPDATE prstshp_product_attribute_shop SET price = $precio WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute}";
}
if ($conn->query($sql)) {
    if ($conn->query($sql_shop)) {
        echo "true";
    } else {
        capuraLogs::nuevo_log("capturaArticulos sql : {$sql}");
        capuraLogs::nuevo_log("capturaArticulos sql_shop : {$sql_shop}");
        echo "false";
    }
} else {
    if ($conn->query($sql_shop)) {
        echo "true";
    } else {
        capuraLogs::nuevo_log("capturaArticulos sql : {$sql}");
        capuraLogs::nuevo_log("capturaArticulos sql_shop : {$sql_shop}");
        echo "false";
    }
}
