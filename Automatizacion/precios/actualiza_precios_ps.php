<?php
include '../token.php';
date_default_timezone_set("America/Chihuahua");
set_time_limit(0);
$token = new Token();

$tokenTemp = $token->getToken("ATP", "prod");
$token     = $tokenTemp[0]->Token;

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

$servername = "localhost";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    print_r("PASS 1<br>");
}

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
} else {
    print_r("PASS 2<br>");
}

$POSTFIELDS = "{}";
$fecha      = date("Y-m-d");
$url        = "https://ayt.operations.dynamics.com/Data/ExchangeRates?%24top=1&%24orderby=StartDate%20desc&%24filter=RateTypeName%20eq%20'ATP'";
print_r("url ::: {$url}<br>");

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => "",
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "GET",
    CURLOPT_POSTFIELDS     => $POSTFIELDS,
    CURLOPT_HTTPHEADER     => array(
        "authorization: Bearer {$token}",
        "content-type: application/json",
    ),
));

$responseP = curl_exec($curl);
$err       = curl_error($curl);
$fechaHoy  = date("Y-m-d") . "T12:00:00Z";
curl_close($curl);
$tc = 100;
if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $response = json_decode($responseP);
    foreach ($response as $resp) {
        if (is_array($resp)) {
            foreach ($resp as $value) {
                $tc          = $value->Rate;
                $fechaCambio = $value->StartDate;
                print_r("tc :: {$tc}<br>");

            }
        }
    }
}

$sql = "UPDATE ExchangeRates SET active = 0 WHERE Date < '$fechaCambio'";
if ($conn_precios->query($sql)) {
    print_r("+++++++ sql ::: {$sql}<br>");
} else {
    print_r("------- sql ::: {$sql}<br>");
}
$sql = "INSERT INTO ExchangeRates VALUES (null,{$tc},'$fechaCambio',1)";
if ($conn_precios->query($sql)) {
    print_r("+++++++ sql ::: {$sql}<br>");
} else {
    print_r("------- sql ::: {$sql}<br>");
}
if ($fechaCambio == $fechaHoy) {
    $sql = "SELECT apd.* FROM actualiza_precios_dyn  apd ";
    $sql .= "INNER JOIN actualiza_precios_ps app ON app.referencia = apd.referencia AND app.actualizado < apd.actualizado ";
    $sql .= "WHERE apd.actualizado = '{$fecha}'  ";
    $sql .= "ORDER BY apd.referencia LIMIT 500 ";
    $result = $conn_precios->query($sql);
    print_r("{$result->num_rows}<br>");
    print_r("{$sql}<br>");
    $fecha_str = date("Y-m-d H:i:s");
    print_r("comenzo ::: {$fecha_str}<br>");
    $hechelepue = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_apd       = $row[id_apd];
            $referencia   = $row[referencia];
            $moneda       = $row[moneda];
            $mxn          = $row[mxn];
            $usd          = $row[usd];
            $unidad       = "";
            $precio_final = $mxn;
            if ($moneda == "USD") {
                $precio_final = $usd * $tc;
            }
            $sql_update = "UPDATE actualiza_precios_ps SET precio_final = {$precio_final}, actualizado = '{$fecha}', mxn = {$mxn}, usd = {$usd}  WHERE referencia  = '{$referencia}' ";
            if ($conn_precios->query($sql_update)) {
                print_r("+++++++ sql_update ::: {$sql_update} ({$moneda})<br>");
            } else {
                print_r("------- sql_update ::: {$sql_update} ({$moneda})<br>");
            }
        }
    }
    print_r("{$fecha_str} ::: Comenzo<br>");
    $fecha_str = date("Y-m-d H:i:s");
    print_r("{$fecha_str} ::: Fin<br>");
} else {
    print_r("fechaCambio ::{$fechaCambio}<br>");
    print_r("fechaHoy ::{$fechaHoy}<br>");
}
exit();
