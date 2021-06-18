<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;

include '../../token.php';

if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

$content = trim(file_get_contents("php://input"));

$decodedT = json_decode($content, true);

if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$id_order     = $decodedT[id_order];
$diario       = $decodedT[diario];
$numeroLineas = $decodedT[numeroLineas];
$customerID   = $decodedT[customerID];
$monto        = $decodedT[monto];
$ov           = $decodedT[ov];
print_r("<br>ov::: {$ov}<br>");
$sitio     = $decodedT[sitio];
$destino   = "prue";
$token     = new Token();
$tokenTemp = $token->getToken("ATP", $destino);
$token     = $tokenTemp[0]->Token;

$DYNAMICS365 = 'tes-ayt.sandbox.operations.dynamics.com';

if ($destino == "prod") {
    $DYNAMICS365 = 'ayt.operations.dynamics.com';
}
$numeroLinea = 0;
$deposito    = $monto;
$myOVs       = explode("_", $ov);

$descripcion = date('d/m/Y') . ', Cobros ' . $sitio . ', e-commerce, ' . $id_order;

if (count($myOVs) > 1) {
    $numeroLinea++;
    $CURLOPT_URL        = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines";
    $CURLOPT_POSTFIELDS = "{\"dataAreaId\": \"ATP\"";
    $CURLOPT_POSTFIELDS .= ",\"LineNumber\": {$numeroLinea}";
    $CURLOPT_POSTFIELDS .= ",\"JournalBatchNumber\": \"{$diario}\"";
    $CURLOPT_POSTFIELDS .= ",\"OffsetAccountType\": \"Bank\"";
    $CURLOPT_POSTFIELDS .= ",\"STF_RefSalesId\": \"\"";
    $CURLOPT_POSTFIELDS .= ",\"PaymentReference\": \"\"";
    $CURLOPT_POSTFIELDS .= ",\"AccountDisplayValue\": \"\"";
    $CURLOPT_POSTFIELDS .= ",\"OffsetAccountDisplayValue\": \"\"";
    $CURLOPT_POSTFIELDS .= ",\"DebitAmount\": {$deposito}";
    $CURLOPT_POSTFIELDS .= ",\"PaymentMethodName\": \"\"";
    $CURLOPT_POSTFIELDS .= ",\"TransactionText\": \"{$descripcion}\"";
    $CURLOPT_POSTFIELDS .= ",\"CurrencyCode\": \"MXN\"}";

    print_r("1 CURLOPT_URL ::: {$CURLOPT_URL}<br>");
    print_r("1 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");

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
            "authorization: Bearer {$token}",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0",
        ),
    ));
    $response2 = curl_exec($curl);
    $err       = curl_error($curl);
    if ($err) {
        print_r('crearDiarioEcomm  - : 3 cURL Error #:' . $err);
        print_r("<br>");
        exit();
    }
    $CURLOPT_URL        = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines(dataAreaId=%27ATP%27,LineNumber={$numeroLinea},JournalBatchNumber=%27{$diario}%27)";
    $CURLOPT_POSTFIELDS = "{\"AccountDisplayValue\": \"PTE_ECOMME\"";
    $CURLOPT_POSTFIELDS .= ",\"OffsetAccountDisplayValue\": \"\"";
    $CURLOPT_POSTFIELDS .= ",\"AccountType\": \"Bank\"}";
    print_r("2 CURLOPT_URL : {$CURLOPT_URL}<br>");
    print_r("2 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
    curl_setopt_array($curl, array(
        CURLOPT_URL            => $CURLOPT_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "PATCH",
        CURLOPT_POSTFIELDS     => $CURLOPT_POSTFIELDS,
        CURLOPT_HTTPHEADER     => array(
            "authorization: Bearer {$token}",
            "content-type: application/json; odata.metadata=minimal",
            "odata-version: 4.0",
        ),
    ));
    $response3 = curl_exec($curl);
    $err       = curl_error($curl);
    if ($err) {
        print_r('crearDiarioEcomm  - : 4 cURL Error #:' . $err);
        print_r("<br>");
        exit();
    }
}
$suma               = 0;
$numeroLineas       = 0;
$diarioCuentaContra = 'PTE_WEBPAY';
for ($i = 0; $i < count($myOVs); $i++) {
    $numeroLineas++;
    $nfactura    = $myOVs[$i];
    $descripcion = date('d/m/Y') . ', Cobros ' . $sitio . ', e-commerce, ' . $nfactura;
    print_r("<br>descripcion : {$descripcion}<br>");
    if (count($myOVs) > 1) {
        $diarioCuentaContra = "";
        $CURLOPT_POSTFIELDS = "{\n\t\"dataAreaId\": \"ATP\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"LineNumber\": {$numeroLineas},";
        $CURLOPT_POSTFIELDS .= "\n\t\"JournalBatchNumber\": \"{$diario}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"OffsetAccountType\": \"Bank\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"PaymentReference\": \"{$nfactura}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"STF_RefSalesId\": \"{$nfactura}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"AccountDisplayValue\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"OffsetAccountDisplayValue\": \"{$diarioCuentaContra}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"CreditAmount\": {$monto},";
        $CURLOPT_POSTFIELDS .= "\n\t\"PaymentMethodName\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"TransactionText\": \"{$descripcion}, {$diario}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"CurrencyCode\": \"MXN\"\n}";
        $CURLOPT_URL = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines";
        print_r("3 CURLOPT_URL : {$CURLOPT_URL}<br>");
        print_r("3 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
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
                "content-type: application/json; odata.metadata=minimal",
                "odata-version: 4.0",
            ),
        ));
        $response2 = curl_exec($curl);
        $err       = curl_error($curl);
        if ($customerID != '') {
            $CURLOPT_URL        = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines(dataAreaId=%27ATP%27,LineNumber={$numeroLineas},JournalBatchNumber=%27{$diario}%27)";
            $CURLOPT_POSTFIELDS = "{\"AccountDisplayValue\": \"{$customerID}\"}";
            print_r("4 CURLOPT_URL : {$CURLOPT_URL}<br>");
            print_r("4 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL            => $CURLOPT_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "PATCH",
                CURLOPT_POSTFIELDS     => $CURLOPT_POSTFIELDS,
                CURLOPT_HTTPHEADER     => array(
                    "authorization: Bearer " . $token,
                    "content-type: application/json; odata.metadata=minimal",
                    "odata-version: 4.0",
                ),
            ));
            $response3 = curl_exec($curl);
            $err       = curl_error($curl);
            if ($err) {
                print_r('crearDiarioEcomm  - : 6 cURL Error #:' . $err);
                print_r("<br>");
                print_r("cURL Error #:" . $err);exit();
            }
        } else {
            print_r('crearDiarioEcomm  - : fallo ');
            print_r("<br>");
            print_r("fallo");exit();
        }
    } else {
        $CURLOPT_POSTFIELDS = "{\n\t\"dataAreaId\": \"ATP\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"LineNumber\": {$numeroLineas},";
        $CURLOPT_POSTFIELDS .= "\n\t\"JournalBatchNumber\": \"{$diario}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"OffsetAccountType\": \"Bank\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"PaymentReference\": \"{$nfactura}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"STF_RefSalesId\": \"{$nfactura}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"AccountDisplayValue\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"OffsetAccountDisplayValue\": \"{$diarioCuentaContra}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"CreditAmount\": {$monto},";
        $CURLOPT_POSTFIELDS .= "\n\t\"PaymentMethodName\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"TransactionText\": \"{$descripcion}, {$diario}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"CurrencyCode\": \"MXN\"\n}";
        $CURLOPT_URL = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines";
        print_r("5 CURLOPT_URL : {$CURLOPT_URL}<br>");
        print_r("5 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
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
                "content-type: application/json; odata.metadata=minimal",
                "odata-version: 4.0",
            ),
        ));
        $response2 = curl_exec($curl);
        $err       = curl_error($curl);
        if ($err) {
            print_r('crearDiarioEcomm  - : 7 cURL Error #:' . $err);
            print_r("<br>");
            print_r("cURL Error #:" . $err);exit();
            print_r("<br>");
        }
        if ($customerID != '') {
            $CURLOPT_URL        = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines(dataAreaId=%27ATP%27,LineNumber={$numeroLineas},JournalBatchNumber=%27{$diario}%27)";
            $CURLOPT_POSTFIELDS = "{\"AccountDisplayValue\": \"{$customerID}\"}";
            print_r("6 CURLOPT_URL : {$CURLOPT_URL}<br>");
            print_r("6 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL            => $CURLOPT_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "PATCH",
                CURLOPT_POSTFIELDS     => $CURLOPT_POSTFIELDS,
                CURLOPT_HTTPHEADER     => array(
                    "authorization: Bearer " . $token,
                    "content-type: application/json; odata.metadata=minimal",
                    "odata-version: 4.0",
                ),
            ));
            $response3 = curl_exec($curl);
            $err       = curl_error($curl);
            if ($err) {
                print_r('crearDiarioEcomm  - : 8 cURL Error #:' . $err);
                print_r("<br>");
                print_r("cURL Error #:" . $err);exit();
            }
        } else {
            print_r('crearDiarioEcomm  - : fallo 2');
            print_r("<br>");
            print_r("fallo");exit();
        }
    }
}
if (count($myOVs) > 1) {
    $restante = $monto - $suma;
    if ($restante > 0) {
        $numeroLineas++;
        $CURLOPT_POSTFIELDS = "{\n\t\"dataAreaId\": \"ATP\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"LineNumber\": {$numeroLineas},";
        $CURLOPT_POSTFIELDS .= "\n\t\"JournalBatchNumber\": \"{$diario}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"OffsetAccountType\": \"Bank\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"PaymentReference\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"STF_RefSalesId\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"AccountDisplayValue\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"OffsetAccountDisplayValue\": \"{$diarioCuentaContra}\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"CreditAmount\": {$restante},";
        $CURLOPT_POSTFIELDS .= "\n\t\"PaymentMethodName\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"TransactionText\": \"\",";
        $CURLOPT_POSTFIELDS .= "\n\t\"CurrencyCode\": \"MXN\"\n}";
        $CURLOPT_URL = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines";
        print_r("7 CURLOPT_URL : {$CURLOPT_URL}<br>");
        print_r("7 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
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
                "authorization: Bearer {$token}",
                "content-type: application/json; odata.metadata=minimal",
                "odata-version: 4.0",
            ),
        ));
        $response2 = curl_exec($curl);
        $err       = curl_error($curl);
        if ($err) {
            print_r('crearDiarioEcomm  - : 9 cURL Error #:' . $err);
            print_r("<br>");
            print_r("cURL Error #:" . $err);exit();
            print_r("<br>");
        }
        $CURLOPT_URL        = "https://{$DYNAMICS365}/data/CustomerPaymentJournalLines(dataAreaId=%27ATP%27,LineNumber={$numeroLineas},JournalBatchNumber=%27{$diario}%27)";
        $CURLOPT_POSTFIELDS = "{\"AccountDisplayValue\": \"{$customerID}\"}";
        print_r("8 CURLOPT_URL : {$CURLOPT_URL}<br>");
        print_r("8 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $CURLOPT_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "PATCH",
            CURLOPT_POSTFIELDS     => $CURLOPT_POSTFIELDS,
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer " . $token,
                "content-type: application/json; odata.metadata=minimal",
                "odata-version: 4.0",
            ),
        ));
        $response3 = curl_exec($curl);
        $err       = curl_error($curl);
        if ($err) {
            print_r('crearDiarioEcomm  - : 10 cURL Error #:' . $err);
            print_r("<br>");
            print_r("cURL Error #:" . $err);exit();
            print_r("<br>");
        }
    }
}
$curl               = curl_init();
$CURLOPT_URL        = "https://{$DYNAMICS365}/api/services/STF_INAX/STF_DiariosPagos/postPaymentJournal";
$CURLOPT_POSTFIELDS = "{\n\t\"journal\": \"{$diario}\",\n\t\"company\": \"ATP\"\n}";
print_r("9 CURLOPT_URL : {$CURLOPT_URL}<br>");
print_r("9 CURLOPT_POSTFIELDS ::: {$CURLOPT_POSTFIELDS}<br><br>");
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
$response4 = curl_exec($curl);
$err       = curl_error($curl);
if ($err) {
    print_r('crearDiarioEcomm  - : 11 cURL Error #:' . $err);
    print_r("<br>");
    print_r('crearDiarioEcomm  - : 11 cURL response2 #:' . $response2);
    print_r("<br>");
    print_r("crearDiarioEcomm  - : 11 cURL Error - CURLOPT_URL : {$CURLOPT_URL}");
    print_r("<br>");
    print_r("crearDiarioEcomm  - : 11 cURL Error - CURLOPT_POSTFIELDS : {$CURLOPT_POSTFIELDS}");
    print_r("<br>");
    print_r("cURL Error #:" . $err);exit();
    print_r("<br>");
} else {

    $sql = "UPDATE lista_ordenes_tarjeta SET numero_diario = '{$diario}', diario = 1 WHERE id_order = {$id_order}";
    print_r("1 ::: {$sql}");
    if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
        print_r("1 ::: {$sql}");
    } else {
        print_r("2 ::: {$sql}");
    }
}
