<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];

$db_index = "prstshp_";
$conn     = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);
$limite      = $decodedT[limite];

$id_customer = $decodedT[id_customer];

$valores = $decodedT[valor];
$total   = 0;
$sql     = "SELECT total FROM historial_creditos WHERE id_customer = {$id_customer} ORDER BY fecha DESC LIMIT 1";
$result  = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $total = $row[total];
    }
}
$cuentale = 0;
foreach ($valores as $key) {
    $CreatedDateTime1 = $key[CreatedDateTime1];
    $AmountCur        = $key[AmountCur];
    $MCRPaymOrderID   = $key[MCRPaymOrderID];
    $TransType        = $key[TransType];
    $sql              = "SELECT * FROM historial_creditos WHERE id_customer = {$id_customer}  AND accion = '{$TransType},{$MCRPaymOrderID}' AND fecha = '{$CreatedDateTime1}' ORDER BY fecha";
    //print_r("sql ::: {$sql}<br>");
    $result = $conn->query($sql);
    if ($result->num_rows == 0) {
        $total += $AmountCur;
        $sql = "INSERT INTO historial_creditos VALUES ( {$id_customer},'{$CreatedDateTime1}',{$AmountCur},{$total},'{$TransType},{$MCRPaymOrderID}') ";
        if ($conn->query($sql)) {
            $cuentale++;
        } else {
            $cuentale++;
            print_r("FALSE {$cuentale}");
            exit();
        }
        //print_r("<br>");
    } else {
        //print_r("FALSE ----<br>");

    }
}
print_r("{$cuentale}");
