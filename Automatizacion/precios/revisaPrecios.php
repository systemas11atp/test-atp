<?php
//require_once('/var/www/vhosts/avanceytec.com.mx/httpdocs/logs_locales.php');
$servername = "localhost";

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
} else {
    print_r("PASS 1<br>");
}

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
    print_r("PASS 2<br><br>");
}
$sql_references = "";
$sql_references .= "SELECT pp.id_product, pp.reference as prd_reference, ppa.id_product_attribute, ppa.reference as att_reference  ";
$sql_references .= "FROM prstshp_product pp ";
$sql_references .= "INNER JOIN prstshp_product_attribute ppa ON ppa.id_product = pp.id_product and ppa.reference like '%-%' ";
$sql_references .= "WHERE pp.reference != '' AND pp.reference is not null AND pp.reference like '%-%'";
$sql_references .= "GROUP BY ppa.reference";
$result_reference = $conn->query($sql_references);
$padre            = "";

if ($result_reference->num_rows > 0) {
    while ($row = $result_reference->fetch_assoc()) {
        $id_product           = $row[id_product];
        $prd_reference        = $row[prd_reference];
        $id_product_attribute = $row[id_product_attribute];
        $att_reference        = $row[att_reference];
        if ($padre != $prd_reference) {
            $padre       = $prd_reference;
            $sql_precios = "SELECT id_price, Percent1, Percent2, Precio, NombreLista, CantidadDesde, CantidadHasta, ItemRelation, Moneda  FROM list_price where ItemRelation = '{$padre}'";
            $rs_precios  = $conn_precios->query($sql_precios);
            if ($rs_precios->num_rows > 0) {
                while ($rowpr = $rs_precios->fetch_assoc()) {
                    $id_price      = $rowpr[id_price];
                    $percent1      = $rowpr[Percent1];
                    $percent2      = $rowpr[Percent2];
                    $precio        = $rowpr[Precio];
                    $nombrelista   = $rowpr[NombreLista];
                    $cantidaddesde = $rowpr[CantidadDesde];
                    $cantidadhasta = $rowpr[CantidadHasta];
                    $itemrelation  = $rowpr[ItemRelation];
                    $moneda        = $rowpr[Moneda];
                    $nsql          = "INSERT INTO nueva_lista (id_price, percent1, percent2, precio, nombrelista, cantidaddesde, cantidadhasta, itemrelation, moneda) VALUES ('{$id_price}','{$percent1}','{$percent2}','{$precio}','{$nombrelista}','{$cantidaddesde}','{$cantidadhasta}','{$itemrelation}','{$moneda}')";
                    if ($conn_precios->query($nsql)) {
                        print_r("paso ::: {$itemrelation}, {$nombrelista}<br>");
                    } else {
                        print_r("-------------- no paso ::: {$itemrelation}, {$nombrelista}<br>");
                    }
                }
            }
        }
        if ($prd_reference != $att_reference) {
            $sql_precios = "SELECT id_price, Percent1, Percent2, Precio, NombreLista, CantidadDesde, CantidadHasta, ItemRelation, Moneda  FROM list_price where ItemRelation = '{$att_reference}'";
            $rs_precios  = $conn_precios->query($sql_precios);
            if ($rs_precios->num_rows > 0) {
                while ($rowpr = $rs_precios->fetch_assoc()) {
                    $id_price      = $rowpr[id_price];
                    $percent1      = $rowpr[Percent1];
                    $percent2      = $rowpr[Percent2];
                    $precio        = $rowpr[Precio];
                    $nombrelista   = $rowpr[NombreLista];
                    $cantidaddesde = $rowpr[CantidadDesde];
                    $cantidadhasta = $rowpr[CantidadHasta];
                    $itemrelation  = $rowpr[ItemRelation];
                    $moneda        = $rowpr[Moneda];
                    $nsql          = "INSERT INTO nueva_lista (id_price, percent1, percent2, precio, nombrelista, cantidaddesde, cantidadhasta, itemrelation, moneda) VALUES ('{$id_price}','{$percent1}','{$percent2}','{$precio}','{$nombrelista}','{$cantidaddesde}','{$cantidadhasta}','{$itemrelation}','{$moneda}')";
                    if ($conn_precios->query($nsql)) {
                        print_r("paso ::: {$itemrelation}, {$nombrelista}<br>");
                    } else {
                        print_r("-------------- no paso ::: {$itemrelation}, {$nombrelista}<br>");
                    }
                }
            }
        }
    }
}
