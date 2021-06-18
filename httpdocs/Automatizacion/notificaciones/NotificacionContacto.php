<?php

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);

$email       = $decodedT[email];
$periodo     = 1;
$tema        = $decodedT[tema];
$empresa     = "ATP";
$comentarios = $decodedT[comentarios];

$sql = "SELECT pc.`firstname`,pc.`lastname`,pc.`RFC`,pc.`customerID` ,
                (SELECT phone FROM `prstshp_address` WHERE id_customer=pc.id_customer  limit 1) as phone
                FROM `prstshp_customer` pc
                 where  email='$email' limit 1";
$equipos    = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$nombre     = "";
$apellido   = "";
$rfc        = "";
$phone      = "";
$customerID = "";
foreach ($equipos as $item) {
    $nombre     = $item[firstname];
    $apellido   = $item[lastname];
    $rfc        = $item[RFC];
    $phone      = $item[phone];
    $customerID = $item[customerID];
}

$curl  = curl_init();
$array = array(
    'nombre'      => $nombre,
    'apellido'    => $apellido,
    'email'       => $email,
    'phone'       => $phone,
    'rfc'         => $rfc,
    'customerID'  => $customerID,
    'tema'        => $tema,
    //'empresa'     => $empresa,
    'comentarios' => $comentarios,
);

curl_setopt_array($curl, [
    CURLOPT_PORT           => "443",
    CURLOPT_URL            => "https://prod-155.westus.logic.azure.com:443/workflows/dca36b94ea8848d3bfc08a3994ffcb63/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=sab6WuaKBkJT2cYPB8Sawb1hJ8uB1C8TOODkcJ2-wg0",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => "",
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "POST",
    CURLOPT_POSTFIELDS     => json_encode($array),
    CURLOPT_HTTPHEADER     => [
        "Content-Type: application/json",
    ],
]);

$response = curl_exec($curl);
$err      = curl_error($curl);

curl_close($curl);

if ($err) {
    $ejecucion = false;
} else {
    $ejecucion = true;
}
$mensaje = array(
    'response'    => $ejecucion,
    'informacion' => $array,
);
echo json_encode($mensaje, JSON_HEX_QUOT | JSON_HEX_TAG);
exit();
