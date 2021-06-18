<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => "https://solutiontinax-solutiontokeninaxpr.azurewebsites.net/SolutionToken/api/SolutionToken",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => "",
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "GET",
    CURLOPT_POSTFIELDS     => "",
    CURLOPT_COOKIE         => "ARRAffinity=74a00607285d883b7d8bba56c6442a95e1c75f2e7e55bc860a2aed31d56ba255; TiPMix=33.0483104256207; ARRAffinitySameSite=74a00607285d883b7d8bba56c6442a95e1c75f2e7e55bc860a2aed31d56ba255",
]);

$response = curl_exec($curl);
$err      = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $tok   = $response;
    $token = json_decode($tok);
    $token = $token[0]->Token;
}
print_r($reference);
print_r("<br>");

$sql = "SELECT  reference,imultiply FROM prstshp_stock_available
        WHERE location='' and reference not like'%-R'
         group by reference,imultiply LIMIT 40";
$arrayImultiply = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
foreach ($arrayImultiply as $item) {
    $reference = $item[reference];
    $imultiply = $item[imultiply];

    /*print_r($reference);
    print_r("<br>");*/

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://ayt.operations.dynamics.com/Data/AYT_InventTableTaxPackagingQties?%24select=ItemId%2CTaxPackagingQty&%24filter=ItemId%20eq%20'{$reference}'",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "GET",
        CURLOPT_POSTFIELDS     => "",
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . $token,
        ],
    ]);

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    $responseT = json_decode($response);

    $suma = 1;
    $sql  = "UPDATE  prstshp_stock_available
                     SET location='-1'  WHERE reference ='$reference' OR reference ='$reference-R'";
    print_r($sql);
    print_r("<br>");
    Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    foreach ($responseT as $resp) {
        foreach ($resp as $value) {
            $ItemId          = $value->ItemId;
            $TaxPackagingQty = $value->TaxPackagingQty;
            if ($TaxPackagingQty == 0) {
                $TaxPackagingQty = 1;
            }
            print_r($ItemId);
            print_r("<br>");
            print_r($TaxPackagingQty);
            print_r("<br>");
            $sql = "UPDATE  prstshp_stock_available
                     SET location='$imultiply', imultiply = $TaxPackagingQty   WHERE reference ='$ItemId' OR reference ='$ItemId-R'";
            print_r($sql);
            print_r("<br>");
            Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        }
    }

}
