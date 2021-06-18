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

$Completeurl = "https://tes-ayt.sandbox.operations.dynamics.com";
//$Completeurl = "https://ayt.operations.dynamics.com";
include '../token.php';

set_time_limit(0);
$token     = new Token(); // Dynamic Token
$tokenTemp = $token->getToken("LIN", "prue"); // Dynamic Token
$token     = $tokenTemp[0]->Token; // Dynamic Token

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT po.id_order, po.orden_venta, pc.customerID ";
$sql .= "FROM {$db_index}orders po ";
$sql .= "INNER JOIN {$db_index}customer pc ON pc.id_customer = po.id_customer ";
$sql .= "WHERE po.orden_venta IS NOT NULL AND cot= 1 AND ov = 0 LIMIT 5";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $contador = 0;
    while ($row = $result->fetch_assoc()) {
        $POSTFIELDS = "{\n";
        $POSTFIELDS .= "\t\"quotationId\": \"{$row[orden_venta]}\",\n";
        $POSTFIELDS .= "\t\"_AccountNum\": \"{$row[customerID]}\",\n";
        $POSTFIELDS .= "\t\"dataAreaId\": \"lin\"\n";
        $POSTFIELDS .= "}";
        $myUrl = $Completeurl . "/api/services/STF_INAX/STF_Cotizacion/SetSalesQuotationToSalesOrder";
        $curl  = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $myUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $POSTFIELDS,
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer " . $token . "",
                "content-type: application/json"),
        ));
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        if ($err) {
            capuraLogs::nuevo_log("traerCotozaciones POSTFIELDS : {$POSTFIELDS}");
            capuraLogs::nuevo_log("traerCotozaciones myUrl : {$myUrl}");

        } else {
            $orventa    = json_decode($response);
            $sql_update = "UPDATE {$db_index}orders  SET orden_venta = '{$orventa}', ov = 1 WHERE id_order = {$row[id_order]}";
            capuraLogs::nuevo_log("traerCotozaciones sql_update : {$sql_update}");
            print_r("sql_update: {$sql_update}<br>");
            $conn->query($sql_update);

        }
        curl_close($curl);
    }
}
//oxxo, transferencia, webpay y paypal
