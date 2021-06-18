<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
include_once dirname(__FILE__) . '/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

include_once '/classes/Cookie.php';
include '/init.php';
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
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

//$addressID =  $decodedT[addressID];
$id_codigo_postal = $decodedT[id_codigo_postal];
$county_id        = $decodedT[county_id];
capuraLogs::nuevo_log("actualizaCountyId id_codigo_postal : {$id_codigo_postal}");
capuraLogs::nuevo_log("actualizaCountyId county_id : {$county_id}");

$sql = "UPDATE codigos_postales_generales SET CountyId = '{$county_id}' WHERE id_codigo_postal = {$id_codigo_postal}";
print_r("sql : {$sql}<br>");
if ($conn->query($sql)) {
    return "true";
} else {
    return "false";
}
