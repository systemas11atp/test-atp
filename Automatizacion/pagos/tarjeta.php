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
$conn      = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);

$id_customer = $decodedT[id_customer];
$totalCompra = $decodedT[total];

$valores = $decodedT[valor];
$total   = 0;
$limite  = 0;
$sql     = "SELECT total FROM historial_creditos WHERE id_customer = {$id_customer} ORDER BY fecha DESC LIMIT 1";
$result  = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total = $row[total];
    }
}
$sql    = "SELECT limite FROM clientes_credito WHERE id_customer = {$id_customer}";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $limite = $row[limite];
    }
}
$disponible = ($limite - ($total));
if ($disponible > $totalCompra) {
    $respuesta = array("muestra" => 1, "totalCompra" => number_format($totalCompra, 2, '.', ','), "limite" => number_format($limite, 2, '.', ','), "total" => number_format($total, 2, '.', ','), "formato" => number_format($disponible, 2, '.', ','));
    print_r(json_encode($respuesta));

} else {
    $respuesta = array("muestra" => 0, "totalCompra" => number_format($totalCompra, 2, '.', ','), "limite" => number_format($limite, 2, '.', ','), "total" => number_format($total, 2, '.', ','), "formato" => number_format($disponible, 2, '.', ','));
    print_r(json_encode($respuesta));
}
