<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;

include '../../token.php';
/*
$sql  = "SELECT lot.id_order, lot.diario, lot.liberacion, lot.ov, lot.cot, lot.sitio, po.date_add, NOW() as fecha, pc.email, pc.customerID";
$sql .= "FROM lista_ordenes_tarjeta lot  ";
$sql .= "INNER JOIN prstshp_orders po ON po.id_order = lot.id_order ";
$sql .= "INNER JOIN prstshp_customer pc ON pc.id_customer = po.id_customer ";
$sql .= "WHERE lot.pago_aprobado = 1 AND (lot.diario = 0 OR lot.liberacion = 0) ";
$sql .= "LIMIT 2";
 */
/*
$row[id_order]
$row[diario]
$row[liberacion]
$row[ov]
$row[sitio]
$row[date_add]
$row[fecha]
$row[email]
$row[customerID]
$row[SiteId]
 */
$destino   = "prue";
$token     = new Token();
$tokenTemp = $token->getToken("ATP", $destino);
$token     = $tokenTemp[0]->Token;

$DYNAMICS365 = 'tes-ayt.sandbox.operations.dynamics.com';

if ($destino == "prod") {
    $DYNAMICS365 = 'ayt.operations.dynamics.com';
}

$sql = "SELECT lot.id_order as factura, 'CB_ECOM' as journalName, lot.ov, lot.sitio, pc.email as descripcion, lot.customerID as customer, 'PTE_WEBPAY' as diarioCuentaContra, po.total_paid as monto ";
$sql .= "FROM lista_ordenes_tarjeta lot ";
$sql .= "INNER JOIN prstshp_customer pc ON pc.customerID = lot.customerID ";
$sql .= "INNER JOIN prstshp_orders po ON po.id_order = lot.id_order AND pc.id_customer = po.id_customer ";
$sql .= "WHERE lot.orden_venta = 1 AND lot.diario = 0 AND lot.numero_diario = '' LIMIT 5";
$results  = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$info     = array();
$contador = 0;
foreach ($results as $result) {
    $factura            = $result[factura];
    $journalName        = $result[journalName];
    $ov                 = $result[ov];
    $sitio              = $result[sitio];
    $descripcion        = date('d/m/Y') . ', Cobros ' . $sitio . ', e-commerce, ' . $factura;
    $customer           = $result[customer];
    $diarioCuentaContra = $result[diarioCuentaContra];
    $monto              = $result[monto];
    $myOVs              = "";
    $myOVs              = explode("_", $ov);
    $novs               = array();
    $cov                = 0;
    foreach ($myOVs as $nov) {
        $novs[$cov] = array("ov-single" => $nov);
        $cov++;
    }
    $curl               = curl_init();
    $CURLOPT_URL        = "https://{$DYNAMICS365}/data/CustomerPaymentJournalHeaders";
    $CURLOPT_POSTFIELDS = "{\n\t\"dataAreaId\": \"ATP\",\n\t\"JournalName\": \"" . $journalName . "\",\n\t\"Description\": \"" . $descripcion . "\"\n}";
    $curl               = curl_init();
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
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0",
        ),
    ));
    $response = curl_exec($curl);
    $err      = curl_error($curl);
    curl_close($curl);
    if ($err) {
        print_r('crearDiarioEcomm  - : 2 cURL Error #:' . $err);
        print_r("<br>");
    } else {
        $response = json_decode($response);
    }
    $info[$contador] = array(
        "factura"            => (int) $factura,
        "journalName"        => $journalName,
        "ov"                 => $ov,
        "myOVs"              => $novs,
        "myOVstotal"         => (int) count($myOVs),
        "sitio"              => $sitio,
        "descripcion"        => $descripcion,
        "customer"           => $customer,
        "diarioCuentaContra" => $diarioCuentaContra,
        "JournalBatchNumber" => $response->JournalBatchNumber,
        "monto"              => (float) $monto,
    );
    $contador++;
}
print_r(json_encode($info));
