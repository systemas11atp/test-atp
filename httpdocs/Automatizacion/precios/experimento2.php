<?php
//require_once('/var/www/vhosts/avanceytec.com.mx/httpdocs/logs_locales.php');
//$activeStore = explode("/",$_SERVER['REQUEST_URI'])[1];

$servername = "localhost";
$dbname     = "pruebas_shop";
$username   = "test_atp";
$password   = "_7xpLw81";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    print_r("PASS 1<br>");
}

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$di           = "prstshp_";
$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
} else {
    print_r("PASS 2<br>");
}

$grupos = array(
    'C000002837' => 4,
    'CHIH-D-BS'  => 5,
    'CHIH-D-IPP' => 6,
    'CHIH-D-TRA' => 7,
    'CHIH-EN-PP' => 8,
    'CHIH-ENV'   => 9,
    'CHIH-ENV-D' => 10,
    'DESC-ESP'   => 11,
    'LIDEART-1'  => 12,
    'MEXICO-BS'  => 13,
    'MEXICO-PR'  => 14,
    'SUCS-D-BS'  => 15,
    'SUCS-D-PR'  => 16,
    'TXLA-D-BS'  => 17,
    'TXLA-D-PR'  => 18,
);
$fecha = date("Y-m-d H:i:s");

$sql = "SELECT pa.id_product,pa.id_product_attribute, li.NombreLista, li.Percent1, li.Percent2, li.Precio, li.CantidadDesde, li.CantidadHasta, li.Moneda, pa.reference ";
$sql .= "FROM {$di}product_attribute pa ";
$sql .= "INNER JOIN list_price li ON li.ItemRelation = pa.reference AND NombreLista = 'CHIH-A' ";
//$sql .= "WHERE pa.reference = '9955-0210'";
$result = $conn_precios->query($sql);
print_r("{$result->num_rows}<br><br>");
print_r("{$sql}<br><br>");
$hechelepue = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_product           = $row[id_product];
        $id_product_attribute = $row[id_product_attribute];
        $nombrelista          = $row[NombreLista];
        $percent1             = $row[Percent1] / 100;
        $percent2             = $row[Percent2];
        $precio               = $row[Precio];
        $cantidaddesde        = $row[CantidadDesde];
        $cantidadhasta        = $row[CantidadHasta];
        $moneda               = $row[Moneda];
        $reference            = $row[reference];
        if ($cantidaddesde < 1) {
            $cantidaddesde = 1;
        }
        $sql_insert = "INSERT INTO lista_precios_base VALUES ({$id_product},{$id_product_attribute},0,{$precio},'{$moneda}', '{$reference}')";
        if ($moneda == "MXN") {
            $sql_insert = "INSERT INTO lista_precios_base VALUES ({$id_product},{$id_product_attribute},{$precio},0,'{$moneda}', '{$reference}')";
        }
        $sql_select    = "SELECT * FROM lista_precios_base WHERE id_product ={$id_product} AND moneda = '{$moneda}' AND id_product_attribute ={$id_product_attribute} ";
        $result_select = $conn_precios->query($sql_select);
        if ($result_select->num_rows == 0) {
            $conn_precios->query($sql_insert);
            $hechelepue++;
            print_r("{$hechelepue}) {$sql_insert}:::id_product:{$id_product},id_product_attribute:{$id_product_attribute},nombrelista:{$nombrelista},percent1:{$percent1},percent2:{$percent2},precio:{$precio},cantidaddesde:{$cantidaddesde},cantidadhasta:{$cantidadhasta},moneda:{$moneda},reference:{$reference}<br>");
        }
    }
}
print_r("<br>---------------------------------------------------------------------------------------------------------------------<br>");
$sql = "SELECT pa.id_product, li.NombreLista, li.Percent1, li.Percent2, li.Precio, li.CantidadDesde, li.CantidadHasta, li.Moneda, pa.reference ";
$sql .= "FROM {$di}product pa ";
$sql .= "INNER JOIN list_price li ON li.ItemRelation = pa.reference AND NombreLista = 'CHIH-A' ";
$result = $conn_precios->query($sql);
print_r("{$result->num_rows}<br><br>");
print_r("{$sql}<br><br>");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_product           = $row[id_product];
        $id_product_attribute = 0;
        $nombrelista          = $row[NombreLista];
        $percent1             = $row[Percent1] / 100;
        $percent2             = $row[Percent2];
        $precio               = $row[Precio];
        $cantidaddesde        = $row[CantidadDesde];
        $cantidadhasta        = $row[CantidadHasta];
        $moneda               = $row[Moneda];
        $reference            = $row[reference];
        if ($cantidaddesde < 1) {
            $cantidaddesde = 1;
        }
        $sql_insert = "INSERT INTO lista_precios_base VALUES ({$id_product},{$id_product_attribute},0,{$precio},'{$moneda}', '{$reference}')";
        if ($moneda == "MXN") {
            $sql_insert = "INSERT INTO lista_precios_base VALUES ({$id_product},{$id_product_attribute},{$precio},0,'{$moneda}', '{$reference}')";
        }
        $sql_select    = "SELECT * FROM lista_precios_base WHERE id_product ={$id_product} AND moneda = '{$moneda}' AND id_product_attribute ={$id_product_attribute} ";
        $result_select = $conn_precios->query($sql_select);
        if ($result_select->num_rows == 0) {
            $conn_precios->query($sql_insert);
            $hechelepue++;
            print_r("{$hechelepue}) {$sql_insert}:::id_product:{$id_product},id_product_attribute:{$id_product_attribute},nombrelista:{$nombrelista},percent1:{$percent1},percent2:{$percent2},precio:{$precio},cantidaddesde:{$cantidaddesde},cantidadhasta:{$cantidadhasta},moneda:{$moneda},reference:{$reference}<br>");
        }
    }
}

//print_r("sql ::: {$sql}<br>");
