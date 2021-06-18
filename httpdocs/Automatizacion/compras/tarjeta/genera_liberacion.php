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

print_r("{$token}<br><br><br>");
$DYNAMICS365 = 'tes-ayt.sandbox.operations.dynamics.com';

if ($destino == "prod") {
    $DYNAMICS365 = 'ayt.operations.dynamics.com';
}

$sql = "SELECT lot.id_order as factura, 'CB_ECOM' as journalName, lot.ov, lot.sitio, pc.email as descripcion, lot.customerID as customer, 'PTE_WEBPAY' as diarioCuentaContra, po.total_paid as monto ";
$sql .= "FROM lista_ordenes_tarjeta lot ";
$sql .= "INNER JOIN prstshp_customer pc ON pc.customerID = lot.customerID  ";
$sql .= "INNER JOIN prstshp_orders po ON po.id_order = lot.id_order ";
$sql .= "WHERE lot.diario = 1 AND lot.liberacion = 0 AND lot.numero_diario != '' LIMIT 5";
$results  = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$info     = array();
$contador = 0;
foreach ($results as $result) {
    $ov                 = $result[ov];
    $factura            = $result[factura];
    $journalName        = $result[journalName];
    $sitio              = $result[sitio];
    $descripcion        = $result[descripcion];
    $customer           = $result[customer];
    $diarioCuentaContra = $result[diarioCuentaContra];
    $monto              = $result[monto];
    $ovs                = explode("_", $result[ov]);
    $cuentale           = 0;
    foreach ($ovs as $ov) {
        $CURLOPT_URL        = "https://{$DYNAMICS365}/api/services/STF_INAX/STF_LiberacionOV/releaseToWarehouseV2";
        $CURLOPT_POSTFIELDS = "{\"salesId\" : \"{$ov}\",\"company\" : \"ATP\"}";

        print_r("CURLOPT_URL : {$CURLOPT_URL}<br><br>");
        print_r("CURLOPT_POSTFIELDS : {$CURLOPT_POSTFIELDS}<br><br>");

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
        print_r("response ::: {$response}<br><br><br>");
        $response = json_decode($response);
        $err      = curl_error($curl);
        if ($err) {
            print_r("cURL Error #:" . $err);exit();
        } else {
            curl_close($curl);
            $CURLOPT_URL = "https://{$DYNAMICS365}/Data/STF_WHSWorkLineEntity?%24filter=OrderNum%20eq%20%27{$ov}%27";
            print_r("CURLOPT_URL : {$CURLOPT_URL}<br><br>");
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
                print_r($result);exit();
            } else {
                if ($response2->value[0]->WorkId != '') {
                    $cuentale++;
                } else {
                    print_r("false");
                }
            }
        }
    }
    if ($cuentale == count($result[ov])) {
        $sql = "UPDATE lista_ordenes_tarjeta SET liberacion = 1 WHERE ov = '{$result[ov]}'";
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
            print_r("1 ::: {$sql}<br>");
        } else {
            print_r("2 ::: {$sql}<br>");
        }
    }
    print_r("<br>");
}
