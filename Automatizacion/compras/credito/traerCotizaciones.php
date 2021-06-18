<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;

$Completeurl = "https://tes-ayt.sandbox.operations.dynamics.com";
//$Completeurl = "https://ayt.operations.dynamics.com";
include '../../token.php';

set_time_limit(0);
$token     = new Token(); // Dynamic Token
$tokenTemp = $token->getToken("ATP", "prue"); // Dynamic Token
$token     = $tokenTemp[0]->Token; // Dynamic Token

$sql          = "SELECT * FROM lista_ordenes_credito WHERE cotizacion = 1 AND orden_venta = 0 LIMIT 5";
$cotizaciones = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
print_r($sql);
print_r("<br><br><br>");
print_r($cotizaciones);
print_r("<br><br><br>");
foreach ($cotizaciones as $cotizacion) {
    $cots = explode("_", $cotizacion['cot']);
    foreach ($cots as $cot) {
        $carrier    = $cotizacion[carrier];
        $POSTFIELDS = "{\n";
        $POSTFIELDS .= "\t\"quotationId\": \"{$cot}\",\n";
        $POSTFIELDS .= "\t\"_AccountNum\": \"{$cotizacion['customerID']}\",\n";
        $POSTFIELDS .= "\t\"dataAreaId\": \"atp\"\n";
        $POSTFIELDS .= "}";
        $myUrl = $Completeurl . "/api/services/STF_INAX/STF_Cotizacion/SetSalesQuotationToSalesOrder";
        print_r("myUrl :: {$myUrl}<br>");
        print_r("POSTFIELDS :: {$POSTFIELDS}<br>");
        $curl = curl_init();
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
            capuraLogs::nuevo_log("traerCotozaciones _tarjeta POSTFIELDS : {$POSTFIELDS}");
            capuraLogs::nuevo_log("traerCotozaciones _tarjeta myUrl : {$myUrl}");

        } else {
            print_r("<br><br><br>");
            $orventa    = json_decode($response);
            $curl       = curl_init();
            $url        = $Completeurl . "/data/SalesOrderHeadersV2(dataAreaId=%27atp%27,SalesOrderNumber=%27{$orventa}%27)?=&cross-company=true";
            $POSTFIELDS = "{\n  \"SATPaymMethod_MX\": \"PPD\",\n\t\"DeliveryModeCode\": \"{$carrier}\"\n}";
            print_r("url :: {$url}<br>");
            print_r("POSTFIELDS :: {$POSTFIELDS}<br><br><br>");
            curl_setopt_array($curl, array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "PATCH",
                CURLOPT_POSTFIELDS     => $POSTFIELDS,
                CURLOPT_HTTPHEADER     => array(
                    "authorization: Bearer " . $token . "",
                    "content-type: application/json"),
            ));
            $response = curl_exec($curl);
            $err      = curl_error($curl);
            curl_close($curl);
            if ($err) {
                exit();
            } else {

                $sql_update = "UPDATE lista_ordenes_credito SET ov = IF(ov != '',CONCAT(ov,'_','{$orventa}'),'{$orventa}'),  orden_venta = 1 WHERE id_lista_orden_credito = {$cotizacion['id_lista_orden_credito']}";
                Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_update);
                capuraLogs::nuevo_log("traerCotozaciones _credito sql_update : {$sql_update}");
                print_r("sql_update: {$sql_update}<br><br><br>");
            }
        }
        curl_close($curl);
    }
}
