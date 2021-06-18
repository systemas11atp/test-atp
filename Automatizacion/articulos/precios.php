<?php

/*
include '../token.php';
require_once('/var/www/vhosts/avanceytec.com.mx/httpdocs/logs_locales.php');
set_time_limit(0);
$token = new Token(); // Dynamic Token
/*
ATP
LIN

prod
prue
/
$tokenTemp = $token->getToken("ATP","prod"); // Dynamic Token
$token = $tokenTemp[0]->Token; // Dynamic Token

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
throw new Exception('Request method must be POST!');
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content = trim(file_get_contents("php://input"));
$decodedT = json_decode($content, true);
if(!is_array($decodedT)){
throw new Exception('Received content contained invalid JSON!');
}

$fecha=date("m/d/Y");

$curl = curl_init();
$itemId = $decodedT[ItemId];
$unitId = $decodedT[UnitId];
$id_product = $decodedT[id_product];
$id_product_attribute = $decodedT[id_product_attribute];
$POSTFIELDS =  "{\"CustAccount\": \"C000007078\",\n";
$POSTFIELDS .= "\"ItemId\": \"{$itemId}\",\n";
$POSTFIELDS .= "\"amountQty\": 1,\n";
$POSTFIELDS .= "\"transDate\": \"{$fecha}\",\n";
$POSTFIELDS .= "\"currencyCode\": \"MXN\",\n";
$POSTFIELDS .= "\"InventSiteId\": \"CHIH\",\n";
$POSTFIELDS .= "\"InventLocationId\": \"0\",\n";
$POSTFIELDS .= "\"PercentCharges\": 0,\n";
$POSTFIELDS .= "\"company\": \"ATP\",\n";
$POSTFIELDS .= "\"UnitId\": \"{$unitId}\"}";
curl_setopt_array($curl, array(
// CURLOPT_URL => "https://tes-ayt.sandbox.operations.dynamics.com/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceUnitPrice",
CURLOPT_URL => "https://ayt.operations.dynamics.com/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceLineAmountV2",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "POST",
CURLOPT_POSTFIELDS => $POSTFIELDS,
CURLOPT_HTTPHEADER => array(
"authorization: Bearer " . $token."",
"content-type: application/json"
),
));

$responseP = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
capuraLogs::nuevo_log("precios POSTFIELDS : {$POSTFIELDS}");
echo "cURL Error #:" . $err;
} else {
if(is_numeric($responseP)) {
$valor=round($responseP, 2);
$body = array( "precio"=> (float)$valor,
"referencia"=> "{$itemId}",
"id_product"=> (int)$id_product,
"id_product_attribute"=> (int)$id_product_attribute
);
echo json_encode($body);
}
}
 */
?>
