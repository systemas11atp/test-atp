<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);
if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$customerID   = $decodedT[customerID];
$tipo_cliente = $decodedT[tipo_cliente];
$id_customer  = $decodedT[id_customer];
$limite       = $decodedT[limite];
$grupo        = $decodedT[grupo];

print_r("customerID :: {$customerID}<br>");
print_r("tipo_cliente :: {$tipo_cliente}<br>");
print_r("id_customer :: {$id_customer}<br>");
print_r("limite :: {$limite}<br>");
print_r("grupo :: {$grupo}<br>");
if ($grupo != "") {
    $sql = "SELECT id_group FROM prstshp_group_lang  WHERE name = '{$grupo}' AND id_lang = 2";
    capuraLogs::nuevo_log("1) ---------- sql : {$sql}");
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sql = "SELECT * FROM prstshp_customer_group  WHERE id_customer = {$id_customer} AND id_group = {$row[id_group]}";
            capuraLogs::nuevo_log("2) ---------- sql : {$sql}");
            $result_group = $conn->query($sql);
            if ($result_group->num_rows == 0) {
                $sql = "INSERT INTO prstshp_customer_group  VALUES  ({$id_customer},{$row[id_group]})";
                capuraLogs::nuevo_log("3) ---------- sql : {$sql}");
                $conn->query($sql);

            }
            $sql = "UPDATE {$db_index}customer SET id_default_group = {$row[id_group]} WHERE id_customer = {$id_customer}";
            capuraLogs::nuevo_log("4) ---------- sql : {$sql}");
            $conn->query($sql);
        }
    }
}
$organizationNumber    = getSantanderConstant($customerID);
$sql_customerID_update = "UPDATE {$db_index}customer SET customerID = '{$customerID}', tipo_cliente = '{$tipo_cliente}', bank_reference = '{$organizationNumber}' WHERE id_customer = {$id_customer}";
capuraLogs::nuevo_log("actualizaCustomerID sql_customerID_update : {$sql_customerID_update}");
if ($conn->query($sql_customerID_update)) {
    print_r("true 1<br>");
    if ($tipo_cliente == "CREDITO") {
        $sql = "INSERT INTO clientes_credito VALUES({$id_customer},'{$customerID}',{$limite})";
        if ($conn->query($sql)) {
            print_r("true 2<br>");
        } else {
            print_r("false 3<br>");
        }
    }
} else {
    print_r("false 4<br>");
}

function santanderSetCharValues($char)
{
    $assignValuesArray = array(
        'A' => '1', 'B' => '2', 'C' => '3', 'D' => '4',
        'E' => '5', 'F' => '6', 'G' => '7', 'H' => '8',
        'I' => '9', 'J' => '1', 'K' => '2', 'L' => '3',
        'M' => '4', 'N' => '5', 'O' => '6', 'P' => '7',
        'Q' => '8', 'R' => '9', 'S' => '1', 'T' => '2',
        'U' => '3', 'V' => '4', 'W' => '5', 'X' => '6',
        'Y' => '7', 'Z' => '8',
    );
    if ($assignValuesArray[$char]) {
        print_r("assignValuesArray[{$char}] : {$assignValuesArray[$char]}<br>");
        return $assignValuesArray[$char];
    } else {
        print_r("char : {$char}<br>");
        return $char;
    }
}

function santanderVerificationDigit($array)
{
    //print_r($array);
    $multiplierArray = [11, 13, 17, 19, 23];
    $resultArray     = array();
    $index           = 0;
    foreach ($array as $key => $value) {
        if ($index > 4) {
            $index = 0;
        }

        $resultArray[] = $value * $multiplierArray[$index];
        $index++;
    }
    print_r("array_sum(resultArray) : " . array_sum($resultArray) . "<br>");
    $toDivide = array_sum($resultArray) + 330;
    print_r("toDivide : {$toDivide}<br>");
    //echo $toDivide;
    $residual = ($toDivide % 97) + 1;
    print_r("residual : {$residual}<br>");
    //echo $residual;
    $formatCode = str_pad($residual, 2, 0, STR_PAD_LEFT);
    print_r("formatCode : {$formatCode}<br>");
    return $formatCode;
}

function getSantanderConstant($clientNumber)
{
    //echo "dentro de santander";
    $temporalString = str_split($clientNumber);
    //print_r("temporalString : {$temporalString}<br>");
    //print_r($assignValuesArray);
    $formatedChars = array_map("santanderSetCharValues", $temporalString);

    $verificationCode = santanderVerificationDigit(array_reverse($formatedChars));
    //print_r("verificationCode : {$verificationCode}<br>");
    return $clientNumber . $verificationCode;
}
