<?php
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

$fecha = date("Y-m-d H:i:s");

print_r("<br>------------------------------------------------------------<br>");
$sql = "UPDATE  prstshp_product_shop  SET id_shop = 0 WHERE price = 0";
if ($conn->query($sql)) {
    print_r("sql ::: {$sql}<br>");
}
print_r("<br>------------------------------------------------------------<br>");
$sql = "UPDATE  prstshp_product_shop  SET id_shop = 1 WHERE price > 0";
if ($conn->query($sql)) {
    print_r("sql ::: {$sql}<br>");
}
print_r("<br>------------------------------------------------------------<br>");
$sql    = "SELECT id_product FROM prstshp_product_shop  WHERE price = 0";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sql = "UPDATE  prstshp_product_attribute_shop  SET id_shop = 0 WHERE id_product = {$row[id_product]}";
        if ($conn->query($sql)) {
            print_r("sql ::: {$sql}<br>");
        }
    }
}
print_r("<br>------------------------------------------------------------<br>");
$sql    = "SELECT id_product FROM prstshp_product_shop  WHERE price > 0";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sql = "UPDATE  prstshp_product_attribute_shop  SET id_shop = 1 WHERE id_product = {$row[id_product]}";
        if ($conn->query($sql)) {
            print_r("sql ::: {$sql}<br>");
        }
    }
}
print_r("<br>------------------------------------------------------------<br>");
/*
 */
