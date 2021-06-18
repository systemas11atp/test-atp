<?php

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
include_once '/classes/Cookie.php';
include '/init.php';
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Consulta principal para traerme todos los productos que su unidad es en metros o en pies
$sql = "SELECT id_product,unitid,reference,id_product_attribute,width,height,depth,volumen
            FROM prstshp_product_attribute WHERE unitid IN ('RolloD') ";
//id_product in (16701,16904,16914,16915,16918,16926,16949,16979,17095)
$result         = $conn->query($sql);
$count          = 0;
$countAttributo = 0;
while ($row = $result->fetch_assoc()) {
    $id_product           = $row[id_product];
    $unitid               = $row[unitid];
    $reference            = $row[reference];
    $id_product_attribute = $row[id_product_attribute];
    $width                = $row[width];
    $height               = $row[height];
    $depth                = $row[depth];
    $volumen              = $row[volumen];

    $sql = "UPDATE prstshp_stock_available
             set reference=(select reference from prstshp_product where id_product={$id_product}) ,
                unidadVenta=(select unitid from prstshp_product where id_product={$id_product})
                 WHERE id_product ={$id_product} and id_product_attribute=0 ";
    $conn->query($sql);
    /*print_r("Id producto:: " . $id_product);
    print_r("<br>");

    print_r("Unidad:: " . $unitid);
    print_r("<br>");

    print_r("Referencia:: " . $reference);
    print_r("<br>");*/
    $count++;

    $sql = "SELECT id_product,reference,id_product_attribute FROM prstshp_stock_available
                 WHERE id_product ={$id_product} and id_product_attribute={$id_product_attribute} ";
    //print_r($sql);
    $resultStock = $conn->query($sql);

    $validacion = true;
    while ($rowStock = $resultStock->fetch_assoc()) {
        $sql = "UPDATE prstshp_stock_available
                set reference='{$reference}' , unidadVenta='{$unitid}'

                 WHERE id_product ={$id_product} and id_product_attribute={$id_product_attribute} ";
        //$conn->query($sql);
        $validacion = false;
    }

    if ($validacion) {
        $countAttributo++;
        $referenciaSinR = str_replace("-R", "", $reference);

        $sql = "INSERT INTO `prstshp_stock_available`( `id_product`, `id_product_attribute`, `id_shop`, `id_shop_group`, `quantity`, `physical_quantity`, `reserved_quantity`, `depends_on_stock`, `out_of_stock`, `location`, `itrans`, `imultiply`, `reference`, `width`, `height`, `depth`, `categoria`, `volumen`, `pies`, `metros`, `weight`, `unidadVenta`, `actualizado`) SELECT  `id_product`, {$id_product_attribute}, `id_shop`, `id_shop_group`, `quantity`, `physical_quantity`, `reserved_quantity`, `depends_on_stock`, `out_of_stock`, `location`, `itrans`, `imultiply`, '{$reference}', `width`, `height`, `depth`, `categoria`, `volumen`, `pies`, `metros`, `weight`, 'RolloD', `actualizado` FROM `prstshp_stock_available` WHERE  id_product={$id_product} and reference='{$referenciaSinR}' limit 1;";
        print_r($sql);
        print_r("<br>");
        //$conn->query($sql);
    }
}
print_r($countAttributo);
print_r("Total::: " . $count);
