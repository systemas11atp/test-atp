<?php
include '../token.php';
date_default_timezone_set("America/Chihuahua");
set_time_limit(0);
$token = new Token();

$tokenTemp = $token->getToken("ATP", "prod");
$token     = $tokenTemp[0]->Token;

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    print_r("PASS 1<br>");
}

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
} else {
    print_r("PASS 2<br>");
}

$fecha     = date("Y-m-d");
$fecha_dyn = date("m/d/Y");

$sql    = "SELECT * FROM actualiza_precios_dyn WHERE actualizado  < '{$fecha}' ORDER BY referencia LIMIT 65";
$result = $conn_precios->query($sql);
print_r("{$result->num_rows}<br>");
print_r("{$sql}<br>");
$fecha_str = date("Y-m-d H:i:s");

$hechelepue = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_apd        = $row[id_apd];
        $referencia    = $row[referencia];
        $moneda        = $row[moneda];
        $mxn           = $row[mxn];
        $usd           = $row[usd];
        $unidad        = "";
        $sql_unidad    = "SELECT Unidad FROM list_price WHERE ItemRelation  = '{$referencia}' AND NombreLista = 'CHIH-A' GROUP BY  ItemRelation ";
        $result_unidad = $conn_precios->query($sql_unidad);
        if ($result_unidad->num_rows > 0) {
            while ($row_unidad = $result_unidad->fetch_assoc()) {
                $unidad = $row_unidad[Unidad];
            }
        }

        $curl                 = curl_init();
        $itemId               = $decodedT[ItemId];
        $unitId               = $decodedT[UnitId];
        $id_product           = $decodedT[id_product];
        $id_product_attribute = $decodedT[id_product_attribute];
        $POSTFIELDS           = "{\"CustAccount\": \"C000007078\",\n";
        $POSTFIELDS .= "\"ItemId\": \"{$referencia}\",\n";
        $POSTFIELDS .= "\"amountQty\": 1,\n";
        $POSTFIELDS .= "\"transDate\": \"{$fecha_dyn}\",\n";
        $POSTFIELDS .= "\"currencyCode\": \"{$moneda}\",\n";
        $POSTFIELDS .= "\"InventSiteId\": \"CHIH\",\n";
        $POSTFIELDS .= "\"InventLocationId\": \"0\",\n";
        $POSTFIELDS .= "\"PercentCharges\": 0,\n";
        $POSTFIELDS .= "\"company\": \"ATP\",\n";
        $POSTFIELDS .= "\"UnitId\": \"{$unidad}\"}";

        $url = "https://ayt.operations.dynamics.com/api/services/STF_INAX/STF_ItemSalesPrice/getSalesPriceLineAmountV2";

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $POSTFIELDS,
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer {$token}",
                "content-type: application/json",
            ),
        ));

        $responseP = curl_exec($curl);
        print_r("responseP ::: {$responseP}<br>");
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            capuraLogs::nuevo_log("precios POSTFIELDS : {$POSTFIELDS}");
            echo "cURL Error #:" . $err;
        } else {
            if (is_numeric($responseP)) {
                $valor = (float) $responseP;
                print_r("valor ::: {$valor}<br>");
                if ($moneda == "MXN") {
                    $mxn = $valor;
                    print_r("mxn ::: {$mxn}<br>");
                } else {
                    $usd = $valor;
                    print_r("usd ::: {$usd}<br>");
                }
                $sql_update = "UPDATE actualiza_precios_dyn SET mxn = {$mxn}, usd = {$usd}, actualizado = '{$fecha}' WHERE id_apd = {$id_apd}";
                if ($conn_precios->query($sql_update)) {
                    $hechelepue++;
                    print_r("{$hechelepue}) +++++++ sql_update ::: {$sql_update}<br>");

                } else {
                    print_r("--------------------------------xxxxxxx sql_update ::: {$sql_update}<br>");
                }
            }
        }
        print_r(" ------------------------------------------------------------ <br>");
    }
}
print_r("{$fecha_str} ::: Comenzo<br>");
$fecha_str = date("Y-m-d H:i:s");
print_r("{$fecha_str} ::: Fin<br>");
