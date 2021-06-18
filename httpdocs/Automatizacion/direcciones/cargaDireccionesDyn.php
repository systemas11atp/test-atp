<?php
include '../token.php';
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
}

/*
SELECT cd.CountyId FROM colonias_dyn cd
INNER JOIN codigos_postales_generales cp ON cd.Description like CONCAT('%',cp.asentamiento)
WHERE cd.codigo_postal IS NULL
GROUP BY CountyId
LIMIT 10
 */
$sql     = "SELECT CountyId, Description FROM colonias_dyn WHERE codigo_postal IS NULL GROUP BY CountyId LIMIT 75";
$result  = $conn->query($sql);
$urlProd = "https://ayt.operations.dynamics.com";
$urlPrue = "https://tes-ayt.sandbox.operations.dynamics.com";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $token       = new Token();
        $tokenTemp   = $token->getToken("ATP", "prue");
        $token       = $tokenTemp[0]->Token;
        $CountyId    = $row[CountyId];
        $Description = urlencode($row[Description]);

        //print_r("<br>Description : {$Description}<br>");
        $api     = "/Data/AYT_LogisticsPostalAddresses?%24orderby=ZipCode%20asc&%24select=ZipCode,County,Address&%24filter=County%20eq%20'{$CountyId}'%20and%20ZipCode%20ne%20''%20and%20Address%20eq%20'*{$Description}*'&%24top=1";
        $fullUrl = "{$urlProd}{$api}";
        //print_r("fullUrl : {$fullUrl}<br>");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_POSTFIELDS     => $POSTFIELDS,
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer {$token}",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $partes  = json_decode($response);
            $cuantos = count($partes->value);
            if ($cuantos > 0) {
                $ZipCode = utf8_decode($partes->value[0]->ZipCode);
                $County  = utf8_decode($partes->value[0]->County);

                if ($County == '') {
                    $County = $CountyId;
                }
                $AddressFull   = explode("\n", $partes->value[0]->Address);
                $cuantosAddres = count($AddressFull);
                $colonia       = $AddressFull[$cuantosAddres - 3];
                $Address       = utf8_decode($partes->value[0]->Address);

                //print_r("ZipCode : {$ZipCode}<br>");
                //print_r("cuantos : {$cuantos}<br>");
                //print_r("County : {$County}<br>");
                //print_r("cuantosAddres : {$cuantosAddres}<br>");
                //print_r("<br>");
                //for ($i=0; $i < $cuantosAddres; $i++) {
                //print_r("AddressFull[{$i}]  : {$AddressFull[$i]}<br>");

                //}
                //print_r("<br>");
                //print_r("colonia : {$colonia}<br>");
                //print_r("Description : {$Description}<br>");
                //print_r("Address : {$Address}<br>");
                $sql2         = "SELECT asentamiento FROM codigos_postales_generales WHERE codigo_postal = {$ZipCode}";
                $result2      = $conn->query($sql2);
                $asentamiento = "asentamiento desconocido";
                if ($result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $tmp_asentamiento = $row2[asentamiento];
                        //print_r("tmp_asentamiento : {$tmp_asentamiento}<br>");
                        $sql3    = "SELECT * FROM colonias_dyn WHERE  CountyId = '{$County}' AND Description like '%{$tmp_asentamiento}' ";
                        $result3 = $conn->query($sql3);
                        if ($result3->num_rows > 0) {
                            $asentamiento = $tmp_asentamiento;
                        }
                    }
                }
                print_r("asentamiento : " . utf8_encode($asentamiento) . "<br>");

                $sql = "UPDATE colonias_dyn SET codigo_postal = '{$ZipCode}', asentamiento = '{$asentamiento}' WHERE  CountyId = '{$County}' AND codigo_postal IS NULL";
                print_r("sql : {$sql}<br><br>");
                $conn->query($sql);
            } else {
                $sql = "UPDATE colonias_dyn SET codigo_postal = 'S/CP', asentamiento = 'S/CP' WHERE  CountyId = '{$CountyId}' AND codigo_postal  IS NULL";
                print_r("sql : {$sql}<br><br>");
                $conn->query($sql);
            }

        }
    }
}
