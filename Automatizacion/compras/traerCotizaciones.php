<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
$selectBDD   = selectBDD();
$dbname      = $selectBDD[dbname];
$username    = $selectBDD[username];
$password    = $selectBDD[password];
$db_index    = _DB_PREFIX_;
$Completeurl = "https://tes-ayt.sandbox.operations.dynamics.com";
//$Completeurl = "https://ayt.operations.dynamics.com";
include '../token.php';

set_time_limit(0);
$token     = new Token(); // Dynamic Token
$tokenTemp = $token->getToken("ATP", "prue"); // Dynamic Token
$token     = $tokenTemp[0]->Token; // Dynamic Token
$db_index  = "prstshp_";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql    = "SELECT * FROM lista_ordenes_credito WHERE cotizacion = 1 AND orden_venta = 0 LIMIT 2";
$sql    = "SELECT * FROM lista_ordenes_referencia WHERE cotizacion = 1 AND orden_venta = 0 LIMIT 2";
$sql    = "SELECT * FROM lista_ordenes_tarjeta WHERE cotizacion = 1 AND orden_venta = 0 LIMIT 2";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $contador = 0;
    while ($row = $result->fetch_assoc()) {
        $cots = explode("_", $row[cot]);
        foreach ($cots as $cot) {
            # code...
            $POSTFIELDS = "{\n";
            $POSTFIELDS .= "\t\"quotationId\": \"{$cot}\",\n";
            $POSTFIELDS .= "\t\"_AccountNum\": \"{$row[customerID]}\",\n";
            $POSTFIELDS .= "\t\"dataAreaId\": \"atp\"\n";
            $POSTFIELDS .= "}";
            $myUrl = $Completeurl . "/api/services/STF_INAX/STF_Cotizacion/SetSalesQuotationToSalesOrder";
            print_r("POSTFIELDS ::: {$POSTFIELDS}<br><br>");
            print_r("myUrl ::: {$myUrl}<br><br>");
        }
        /*
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => $myUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $POSTFIELDS,
    CURLOPT_HTTPHEADER => array(
    "authorization: Bearer " . $token."",
    "content-type: application/json"),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    if($err){
    capuraLogs::nuevo_log("traerCotozaciones POSTFIELDS : {$POSTFIELDS}");
    capuraLogs::nuevo_log("traerCotozaciones myUrl : {$myUrl}");

    }else{
    $orventa=json_decode($response);
    $sql_update = "UPDATE lista_ordenes_credito SET ov = '{$orventa}', orden_venta = 1 WHERE lista_ordenes_credito = {$row[lista_ordenes_credito]}";
    capuraLogs::nuevo_log("traerCotozaciones sql_update : {$sql_update}");
    print_r("sql_update: {$sql_update}<br>");
    $conn->query($sql_update);

    }
    curl_close($curl);
     */
    }
}
