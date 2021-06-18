<?php

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';

$db_index = _DB_PREFIX_;

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);

$id_cart = $context->cookie->id_cart;
$total   = $decodedT[total];
$total   = "1." . rand(1, 30);
if ($id_cart == "") {
    $id_cart = $decodedT[id_cart];
}
$customerID         = "";
$email              = "";
$referencia_cliente = "C000000330";
$ambienteCadena     = "Prue";

$sql = "SELECT pc.customerID,pc.email,pcart.id_cart, pcart.monto, pcart.webpay
FROM prstshp_cart pcart
JOIN prstshp_customer pc ON pcart.id_customer=pc.id_customer
WHERE pcart.id_cart=" . $id_cart;
$cartInformation = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
foreach ($cartInformation as $item) {
    $customerID = $item[customerID];
    $email      = $item[email];
    $monto      = $item[monto];
    $webpay     = $item[webpay];
}

//Test
/*
$id_company = "SNBX";
$id_branch  = "01SNBXBRNCH";
$user       = "SNBXUSR01";
$pwd        = "SECRETO";
$urlBan     = "https://wppsandbox.mit.com.mx/gen";
$key        = '5DCC67393750523CD165F17E1EFADD21';
 */
/*****************************************************/
if ($webpay != null && $monto == $decodedT[total]) {
    $mensaje = array(
        'ligaWebPay' => $webpay,
        'id_cart'    => $id_cart,
        'total'      => $decodedT[total],
        'existente'  => true,
    );
    echo json_encode($mensaje, JSON_HEX_QUOT | JSON_HEX_TAG);
    exit();
} else {
    $id_company = "Z691";
    $id_branch  = "0031";
    $user       = "Z691SIUS1";
    $pwd        = "C2V8SXKNRZ";
    $urlBan     = "https://bc.mitec.com.mx/p/gen";
    $key        = '326c6a039b80b35dbad8e552cb2dcb46';

    $originalString = '<?xml version="1.0" encoding="UTF-8"?>
    <P>
    <business>
    <id_company>Z691</id_company>
    <id_branch>0031</id_branch>
    <user>Z691SIUS1</user>
    <pwd>C2V8SXKNRZ</pwd>
    </business>
    <url>
    <reference>' . $referencia_cliente . '</reference>
    <amount>' . $total . '</amount>
    <moneda>MXN</moneda>
    <canal>W</canal>
    <omitir_notif_default>1</omitir_notif_default>
    <promociones>C,3,6,9</promociones>
    <st_correo>1</st_correo>
    <fh_vigencia></fh_vigencia>
    <mail_cliente>' . $email . '</mail_cliente>
    <st_cr>A</st_cr>
    <datos_adicionales>
    <data id="1" display="true">
    <label>Orden de venta</label>
    <value>' . $id_cart . '</value>
    </data>
    <data id="2" display="true">
    <label>Cliente</label>
    <value>' . $Cliente . '</value>
    </data>
    <data id="3" display="false">
    <label>Fuente</label>
    <value>info</value>
    </data>
    <data id="4" display="false">
    <label>Sitio</label>
    <value>E-COM</value>
    </data>
    <data id="5" display="false">
    <label>Destino</label>
    <value>' . $ambienteCadena . '</value>
    </data>
    </datos_adicionales>
    </url>
    </P>';

    $encryptedString = AESCrypto::encriptar($originalString, $key);
    $url             = "<pgs><data0>9265655197</data0><data>$encryptedString</data></pgs>";
    $encodedString   = urlencode($url);
    $curl            = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => "https://bc.mitec.com.mx/p/gen",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => "xml=\n{$encodedString}",
        CURLOPT_HTTPHEADER     => array("content-type: application/x-www-form-urlencoded"),
    ));
    $response = curl_exec($curl);
    $err      = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    }
    $originalString = $response;
// $key = '326c6a039b80b35dbad8e552cb2dcb46';
    // $key='5dcc67393750523cd165f17e1efadd21';
    $key             = '326c6a039b80b35dbad8e552cb2dcb46';
    $decryptedString = AESCrypto::desencriptar($originalString, $key);
    $respuesta       = simplexml_load_string($decryptedString);
    $ligaWebPay      = (string) $respuesta->nb_url[0];
    capuraLogs::nuevo_log("webpay ligaWebPay : {$ligaWebPay}");
    $sql = "UPDATE prstshp_cart SET webpay = '{$ligaWebPay}', monto = {$decodedT[total]} WHERE id_cart = {$id_cart}";
    Db::getInstance()->execute($sql);
    $mensaje = array(
        'ligaWebPay' => $ligaWebPay,
        'id_cart'    => $id_cart,
        'total'      => $decodedT[total],
        'existente'  => false,
    );
    echo json_encode($mensaje, JSON_HEX_QUOT | JSON_HEX_TAG);
    exit();
}
class AESCrypto
{
    /**
     * Permite cifrar una cadena a partir de un llave proporcionada

     * @param strToEncrypt

     * @param key

     * @return String con la cadena encriptada

     */
    public static function encriptar($plaintext, $key128)
    {
        $iv         = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));
        $cipherText = openssl_encrypt($plaintext, 'AES-128-CBC', hex2bin($key128), 1, $iv);
        return base64_encode($iv . $cipherText);
    }
    /**
     * Permite descifrar una cadena a partir de un llave proporcionada

     * @param strToDecrypt

     * @param key

     * @return String con la cadena descifrada

     */
    public static function desencriptar($encodedInitialData, $key128)
    {
        $encodedInitialData = base64_decode($encodedInitialData);
        $iv                 = substr($encodedInitialData, 0, 16);
        $encodedInitialData = substr($encodedInitialData, 16);
        $decrypted          = openssl_decrypt($encodedInitialData, 'AES-128-CBC', hex2bin($key128), 1, $iv);
        return $decrypted;
    }
}
