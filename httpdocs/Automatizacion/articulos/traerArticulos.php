<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];

date_default_timezone_set("America/Chihuahua");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hrs = date("H");
//print_r("hrs : {$hrs}<br>");
if ($hrs > 5) {

    $contador  = 0;
    $productos = array();
    $fecha     = date("Y-m-d") . " 12:00:00";
    //print_r("fecha : {$fecha}<br>");
    $cuantos = 0;
    /*
    $sql =  "SELECT id_product, 0 as id_product_attribute, reference, unitId FROM prstshp_product WHERE reference != '' AND reference LIKE '%-%'  AND unitId IS NOT NULL AND act_existencia < '{$fecha}' AND id_product > 0 LIMIT 80";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
    $cuantos++;
    $id_order = $row[id_order];
    $id_customer = $row[id_customer];
    $id_cart = $row[id_cart];
    $productos[$contador] = array( "id_product" => (int)$row[id_product],
    "id_product_attribute" => (int)$row[id_product_attribute],
    "reference" =>"{$row[reference]}",
    "unitId" =>"{$row[unitId]}",
    "cuantos" =>"{$cuantos}"
    );
    $contador++;
    }
    }else{
    capuraLogs::nuevo_log("traerArticulos sql : {$sql}");
    }
    $sql =  "SELECT id_product, id_product_attribute, reference, unitId  FROM prstshp_product_attribute WHERE reference != '' AND reference LIKE '%-%' AND unitId IS NOT NULL AND act_existencia < '{$fecha}' AND id_product > 0 LIMIT 80";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
    $cuantos++;
    $id_order = $row[id_order];
    $id_customer = $row[id_customer];
    $id_cart = $row[id_cart];
    $productos[$contador] = array( "id_product" => (int)$row[id_product],
    "id_product_attribute" => (int)$row[id_product_attribute],
    "reference" =>"{$row[reference]}",
    "unitId" =>"{$row[unitId]}",
    "cuantos" =>"{$cuantos}"
    );
    $contador++;
    }
    }else{
    capuraLogs::nuevo_log("traerArticulos sql : {$sql}");
    }
     */
    // SELECT * FROM prstshp_stock_available WHERE id_shop = 0 AND id_shop_group = 1 AND reference like '%-%'

    $sql = "SELECT id_product, id_product_attribute, reference, unidadVenta as unitId  FROM prstshp_stock_available WHERE id_shop = 0 AND id_shop_group = 1 AND reference like '%-%' AND actualizado < '{$fecha}' GROUP BY reference";
    //print_r("sql : {$sql}<br>");
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cuantos++;
            $id_order             = $row[id_order];
            $id_customer          = $row[id_customer];
            $id_cart              = $row[id_cart];
            $productos[$contador] = array("id_product" => (int) $row[id_product],
                "id_product_attribute"                     => (int) $row[id_product_attribute],
                "reference"                                => "{$row[reference]}",
                "unitId"                                   => "{$row[unitId]}",
                "cuantos"                                  => $cuantos,
            );
            $contador++;
        }
    } else {
        capuraLogs::nuevo_log("traerArticulos sql : {$sql}");
    }
    echo json_encode($productos);
}
