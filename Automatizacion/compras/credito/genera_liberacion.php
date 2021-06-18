<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;
include '../../token.php';

$destino   = "prue";
$token     = new Token();
$tokenTemp = $token->getToken("ATP", $destino);
$token     = $tokenTemp[0]->Token;

$DYNAMICS365 = 'tes-ayt.sandbox.operations.dynamics.com';

if ($destino == "prod") {
    $DYNAMICS365 = 'ayt.operations.dynamics.com';
}

$sql      = "SELECT ov FROM lista_ordenes_credito lot WHERE lot.orden_venta = 1 AND lot.ov != ''AND lot.liberacion = 0 LIMIT 5";
$results  = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$info     = array();
$contador = 0;
foreach ($results as $result) {
    $ovs      = explode("_", $result[ov]);
    $cuentale = 0;
    foreach ($ovs as $ov) {
        $CURLOPT_URL        = "https://{$DYNAMICS365}/api/services/STF_INAX/STF_LiberacionOV/releaseToWarehouseV2";
        $CURLOPT_POSTFIELDS = "{\"salesId\" : \"{$ov}\",\"company\" : \"ATP\"}";

        //print_r("CURLOPT_URL : {$CURLOPT_URL}<br><br>");
        //print_r("CURLOPT_POSTFIELDS : {$CURLOPT_POSTFIELDS}<br><br>");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $CURLOPT_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $CURLOPT_POSTFIELDS,
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer " . $token,
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $response = str_replace('\\', '', $response);
        $response = substr($response, 1, -1);
        //print_r("response ::: {$response}<br><br><br>");
        $response = json_decode($response);
        $err      = curl_error($curl);
        if ($err) {
            //print_r("cURL Error #:" . $err);exit();
        } else {
            curl_close($curl);
            $CURLOPT_URL = "https://{$DYNAMICS365}/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20%27{$ov}%27";
            //print_r("CURLOPT_URL : {$CURLOPT_URL}<br><br>");
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL            => $CURLOPT_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "GET",
                CURLOPT_POSTFIELDS     => "",
                CURLOPT_HTTPHEADER     => array(
                    "authorization: Bearer " . $token,
                    "content-type: application/json",
                ),
            ));
            $response2 = curl_exec($curl);
            $err       = curl_error($curl);
            $response2 = json_decode($response2);
            if ($err) {
                $result = "cURL Error #:" . $err;
                //print_r($result); exit();
            } else {
                //print_r($response2);
                //print_r("<br><br>");
                $cuentale++;

            }
        }
    }
    if ($cuentale == count($ovs)) {
        $sql = "UPDATE lista_ordenes_credito SET liberacion = 1 WHERE ov = '{$result[ov]}'";
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
            print_r("1 ::: {$sql}<br>");
        } else {
            print_r("2 ::: {$sql}<br>");
        }
    }
    print_r("<br>");
}
/*

{"salesId" : "OV-2000868620","company" : "ATP"}

https://tes-ayt.sandbox.operations.dynamics.com/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20%27OV-2000868620%27

 */
