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
$sql    = "SELECT id_product,unitid,reference FROM prstshp_product WHERE unitid IN ('M','Pies') and unidad is null ";
$result = $conn->query($sql);
$count  = 0;
while ($row = $result->fetch_assoc()) {
    $id_product = $row[id_product];
    $unitid     = $row[unitid];
    $reference  = $row[reference];
    print_r("Id producto:: " . $id_product);
    print_r("<br>");

    print_r("Unidad:: " . $unitid);
    print_r("<br>");

    print_r("Referencia:: " . $reference);
    print_r("<br>");

    //Metro 1669
    //Pie      1670
    //Rollo 1671
    $unidadId = 1669;
    if ($unitid != "M") {
        $unidadId = 1670;
    }

    //Reviso primero si el producto
    $sqlAttributos   = "SELECT id_product_attribute,reference FROM prstshp_product_attribute WHERE id_product=" . $id_product;
    $resultAtributos = $conn->query($sqlAttributos);
    print_r("sqlAttributos:: " . $sqlAttributos);
    print_r("<br>");
    while ($rowAtributo = $resultAtributos->fetch_assoc()) {
        $id_product_attribute = $rowAtributo[id_product_attribute];
        $reference            = $rowAtributo[reference];
        print_r("Id producto Atributo:: " . $id_product_attribute);
        print_r("<br>");

        // Insertar atributo rollo

        $sql = "INSERT INTO `prstshp_product_attribute`( `id_product`, `reference`, `supplier_reference`, `location`, `ean13`, `isbn`, `upc`, `mpn`, `wholesale_price`, `price`, `ecotax`, `quantity`, `width`, `height`, `depth`, `categoria`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`, `volumen`, `pies`, `metros`, `unitId`, `act_existencia`) SELECT  `id_product`, '{$reference}-R', `supplier_reference`, `location`, `ean13`, `isbn`, `upc`, `mpn`, `wholesale_price`, 400 , `ecotax`, `quantity`, `width`, `height`, `depth`, `categoria`, `weight`, `unit_price_impact`, null, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`, `volumen`, `pies`, `metros`, 'RolloD', `act_existencia` FROM `prstshp_product_attribute`
            WHERE id_product={$id_product} and id_product_attribute={$id_product_attribute}";
        //print_r($sql);

        $conn->query($sql);
        $atributoIdRollo = $conn->insert_id;
        //Duplica combinaciones para agregarlo al nuevo product_attribute
        $sql = "INSERT INTO `prstshp_product_attribute_combination`(`id_attribute`, `id_product_attribute`)
            SELECT `id_attribute`, {$atributoIdRollo}
            FROM `prstshp_product_attribute_combination`
            WHERE `id_product_attribute`={$id_product_attribute}";
        $conn->query($sql);

        //Agregar combinacion de la unidad
        $sql = "INSERT INTO `prstshp_product_attribute_combination`(`id_attribute`, `id_product_attribute`)
        VALUES ({$unidadId},{$id_product_attribute})";
        $conn->query($sql);

        //Agregar combinacion del rollo
        $sql = "INSERT INTO `prstshp_product_attribute_combination`(`id_attribute`, `id_product_attribute`)
        VALUES (1671,{$atributoIdRollo})";
        $conn->query($sql);

        $sql = "INSERT INTO `prstshp_product_attribute_shop`(`id_product`, `id_product_attribute`, `id_shop`, `wholesale_price`, `price`, `ecotax`, `weight`, `unit_price_impact`, `default_on`, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date`) SELECT `id_product`, {$atributoIdRollo}, `id_shop`, `wholesale_price`, 400 , `ecotax`, `weight`, `unit_price_impact`, null, `minimal_quantity`, `low_stock_threshold`, `low_stock_alert`, `available_date` FROM `prstshp_product_attribute_shop` WHERE  id_product={$id_product} and id_product_attribute={$id_product_attribute}";
        $conn->query($sql);

        $sql = "INSERT INTO `prstshp_stock_available`( `id_product`, `id_product_attribute`, `id_shop`, `id_shop_group`, `quantity`, `physical_quantity`, `reserved_quantity`, `depends_on_stock`, `out_of_stock`, `location`, `itrans`, `imultiply`, `reference`, `width`, `height`, `depth`, `categoria`, `volumen`, `pies`, `metros`, `weight`, `unidadVenta`, `actualizado`, `con_sitios`, `sitio`) SELECT  `id_product`, {$atributoIdRollo}, `id_shop`, `id_shop_group`, `quantity`, `physical_quantity`, `reserved_quantity`, `depends_on_stock`, `out_of_stock`, `location`, `itrans`, `imultiply`, `reference`, `width`, `height`, `depth`, `categoria`, `volumen`, `pies`, `metros`, `weight`, 'RolloD', `actualizado`, `con_sitios`, `sitio` FROM `prstshp_stock_available` WHERE  id_product={$id_product} and id_product_attribute={$id_product_attribute}";
        $conn->query($sql);
    }

    $sql = "update  prstshp_product set unidad='1' where id_product=" . $id_product;
    $conn->query($sql);
    $count++;
}
print_r($count);
