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
$sql    = "SELECT id_product,unitid,reference FROM prstshp_product WHERE unitid IN ('M','Pies') AND unidad IS NULL ";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $id_product = $row[id_product];
    $unitid     = $row[unitid];
    $reference  = $row[reference];
    print_r("Id producto:: " . $id_product);
    print_r("<br>");

    print_r("Unidad:: " . $unitid);
    print_r("<br>");

    $sinAtributos = true;
    $count        = 0;

    //Metro 1669
    //Pie      1670
    //Rollo 1671
    $unidadId = 1669;
    if ($unitid != "M") {
        $unidadId = 1670;
    }

    //Reviso primero si el producto
    $sqlAttributos   = "SELECT id_product_attribute FROM prstshp_product_attribute WHERE id_product=" . $id_product;
    $resultAtributos = $conn->query($sqlAttributos);
    print_r("sqlAttributos:: " . $sqlAttributos);
    print_r("<br>");
    while ($rowAtributo = $resultAtributos->fetch_assoc()) {
        $sinAtributos = false;
        $count++;
    }

    if ($sinAtributos) {
        print_r("No tiene Atributos <br>");

        //Primer insert articulo con su unidad principal
        $sql = "INSERT INTO `prstshp_product_attribute`(`id_product`, `reference`, `supplier_reference`, `location`, `ean13`, `isbn`, `upc`, `mpn`, `wholesale_price`, `price`, `ecotax`, `quantity`, `width`, `height`, `depth`, `categoria`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`, `volumen`, `pies`, `metros`, `unitId`, `act_existencia`)SELECT  {$id_product}, '{$reference}', `supplier_reference`, `location`, `ean13`, `isbn`, `upc`, `mpn`, `wholesale_price`, `price`, `ecotax`, `quantity`, `width`, `height`, `depth`, `categoria`, `weight`, `unit_price_impact`, 1, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`, `volumen`, `pies`, `metros`, '{$unitid}', `act_existencia` FROM `prstshp_product_attribute` WHERE id_product_attribute=542";
        print_r($sql);
        $conn->query($sql);
        //Obtener id registrado
        $atributoIdUnidad = $conn->insert_id;
        print_r("Atributo por default:: " . $atributoIdUnidad);
        print_r("<br>");

        $sql = "update  prstshp_product set cache_default_attribute={$atributoIdUnidad} where id_product=" . $id_product;
        $conn->query($sql);

        $sql = "update  prstshp_stock_available set id_product_attribute={$atributoIdUnidad} where id_product=" . $id_product;
        $conn->query($sql);

        $sql = "INSERT INTO `prstshp_product_attribute_combination`(`id_attribute`, `id_product_attribute`)
        VALUES ({$unidadId},{$atributoIdUnidad})";
        $conn->query($sql);

        $sql = "INSERT INTO `prstshp_product_attribute_shop`(`id_product`, `id_product_attribute`, `id_shop`, `wholesale_price`, `price`, `ecotax`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`) SELECT {$id_product}, {$atributoIdUnidad}, `id_shop`, `wholesale_price`, `price`, `ecotax`, `weight`, `unit_price_impact`, 1, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date` FROM `prstshp_product_attribute_shop` WHERE  id_product_attribute=542";
        $conn->query($sql);

        // Insert rollo
        $sql = "INSERT INTO `prstshp_product_attribute`(`id_product`, `reference`, `supplier_reference`, `location`, `ean13`, `isbn`, `upc`, `mpn`, `wholesale_price`, `price`, `ecotax`, `quantity`, `width`, `height`, `depth`, `categoria`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`, `volumen`, `pies`, `metros`, `unitId`, `act_existencia`)SELECT  {$id_product}, '{$reference}-R', `supplier_reference`, `location`, `ean13`, `isbn`, `upc`, `mpn`, `wholesale_price`, `price`, `ecotax`, `quantity`, `width`, `height`, `depth`, `categoria`, `weight`, `unit_price_impact`, null, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`, `volumen`, `pies`, `metros`, 'RolloD', `act_existencia` FROM `prstshp_product_attribute` WHERE id_product_attribute=542";
        $conn->query($sql);
        $atributoIdRollo = $conn->insert_id;

        $sql = "INSERT INTO `prstshp_product_attribute_combination`(`id_attribute`, `id_product_attribute`)
        VALUES (1671,{$atributoIdRollo})";
        $conn->query($sql);

        $sql = "INSERT INTO `prstshp_product_attribute_shop`(`id_product`, `id_product_attribute`, `id_shop`, `wholesale_price`, `price`, `ecotax`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`) SELECT {$id_product}, {$atributoIdRollo}, `id_shop`, `wholesale_price`, `price`, `ecotax`, `weight`, `unit_price_impact`, null, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date` FROM `prstshp_product_attribute_shop` WHERE  id_product_attribute=542";
        $conn->query($sql);

        $sql = "INSERT INTO `prstshp_stock_available`( `id_product`, `id_product_attribute`, `id_shop`, `id_shop_group`, `quantity`, `physical_quantity`, `reserved_quantity`, `depends_on_stock`, `out_of_stock`, `location`, `itrans`, `imultiply`, `reference`, `width`, `height`, `depth`, `categoria`, `volumen`, `pies`, `metros`, `weight`, `unidadVenta`, `actualizado`) SELECT  `id_product`, {$atributoIdRollo}, `id_shop`, `id_shop_group`, `quantity`, `physical_quantity`, `reserved_quantity`, `depends_on_stock`, `out_of_stock`, `location`, `itrans`, `imultiply`, `reference`, `width`, `height`, `depth`, `categoria`, `volumen`, `pies`, `metros`, `weight`, 'RolloD', `actualizado` FROM `prstshp_stock_available` WHERE `id_product`=" . $id_product;
        $conn->query($sql);

        $sql = "update  prstshp_product set unidad='1' where id_product=" . $id_product;
        $conn->query($sql);

    }

    print_r($tieneAtributos);
    print_r("<br>");
    print_r($count);
    print_r("<br>");
    print_r("<br>");
}
