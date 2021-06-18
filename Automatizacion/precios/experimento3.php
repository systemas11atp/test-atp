<?php
//require_once('/var/www/vhosts/avanceytec.com.mx/httpdocs/logs_locales.php');
//$activeStore = explode("/",$_SERVER['REQUEST_URI'])[1];
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

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
} else {
    print_r("PASS 2<br>");
}
$tc = 20.37;
//$sql = "SELECT * FROM lista_precios_base WHERE id_product > 0 ORDER BY id_product, id_product_attribute";
$sql         = "SELECT * FROM lista_precios_base WHERE id_product = 18119 ORDER BY id_product, id_product_attribute";
$result      = $conn_precios->query($sql);
$myproduct   = 0;
$precioPadre = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $moneda               = $row[moneda];
        $mxn                  = $row[mxn];
        $usd                  = $row[usd];
        $reference            = $row[reference];
        $id_product           = $row[id_product];
        $id_product_attribute = $row[id_product_attribute];
        $precio               = $mxn;
        if ($moneda == "USD") {
            $precio = $usd * $tc;
        }
        print_r("- - - - - - - moneda ::: {$moneda}<br>");
        print_r("- - - - - - - mxn ::: {$mxn}<br>");
        print_r("- - - - - - - usd ::: {$usd}<br>");
        print_r("- - - - - - - precio ::: {$precio}<br>");

        if ($id_product != $myproduct) {
            $myproduct   = $id_product;
            $precioPadre = $precio;
            print_r("- - - - myproduct ::: {$myproduct}<br>");
            print_r("- - - - precioPadre ::: {$precioPadre}<br>");
        }

        if ($id_product_attribute == 0) {
            $nsql = "UPDATE prstshp_product SET price = {$precio} WHERE id_product = {$id_product} ";
            if ($conn->query($nsql)) {
                print_r("1 YEP ::: {$nsql}<br>");
            } else {
                print_r("1 ------------NOP ::: {$nsql}<br>");
            }
            $w_sql = "UPDATE prstshp_product_shop SET id_tax_rules_group = 53, price = {$precio} WHERE id_product = {$id_product}";
            if ($conn->query($w_sql)) {
                print_r("2 YEP ::: {$w_sql}<br>");
            } else {
                print_r("2 ------------NOP ::: {$w_sql}<br>");
            }
            $w_sql = "UPDATE prstshp_product SET id_tax_rules_group = 53, price = {$precio} WHERE id_product = {$id_product} ";
            if ($conn->query($w_sql)) {
                print_r("3 YEP ::: {$w_sql}<br>");
            } else {
                print_r("3 ------------NOP ::: {$w_sql}<br>");
            }
            $w_sql = "UPDATE prstshp_layered_price_index SET price_min = {$precio}, price_max = {$precio} WHERE id_product = {$id_product} AND id_currency = 2 AND id_shop = 1 AND id_country = 145";
            if ($conn->query($w_sql)) {
                print_r("4 YEP ::: {$w_sql}<br>");
            } else {
                print_r("4 ------------NOP ::: {$w_sql}<br>");
            }
            $w_sql = "UPDATE prstshp_specific_price_priority SET priority = 'id_shop;id_currency;id_country;id_group' WHERE id_product = {$id_product}";
            if ($conn->query($w_sql)) {
                print_r("5 YEP ::: {$w_sql}<br>");
            } else {
                $w_sql = "INSERT INTO prstshp_specific_price_priority (priority, id_product) VALUES  ('id_shop;id_currency;id_country;id_group', {$id_product})";
                $conn->query($w_sql);
                print_r("5 ------------NOP ::: {$w_sql}<br>");
            }
            $w_sql = "UPDATE prstshp_specific_price SET price = {$precio} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute} AND id_group = 0";
            if ($conn->query($w_sql)) {
                print_r("6 YEP ::: {$w_sql}<br>");
            } else {
                $w_sql = "INSERT INTO prstshp_specific_price VALUES(null,1,0,{$id_product},1,0,0,0,0,0,{$id_product_attribute},{$precio},1,0.02,0,'percentage','0000-00-00 00:00:00','0000-00-00 00:00:00')";
                $conn->query($w_sql);
                print_r("6 ------------NOP ::: {$w_sql}<br>");
            }

        } else {
            $nprice = $precio - $precioPadre;
            print_r("- - - - precioPadre ::: {$precioPadre}<br>");
            print_r("- - - - precio ::: {$precio}<br>");
            print_r("- - - - nprice ::: {$nprice}<br>");

            $nsql = "UPDATE prstshp_product_attribute SET price = {$nprice} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute}";
            if ($conn->query($nsql)) {
                print_r("7 YEP ::: {$nsql}<br>");
            } else {
                print_r("7 ------------NOP ::: {$nsql}<br>");
            }
            $w_sql = "UPDATE prstshp_product_attribute_shop SET price = {$nprice} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute}";
            if ($conn->query($w_sql)) {
                print_r("8 YEP ::: {$w_sql}<br>");
            } else {
                print_r("8 ------------NOP ::: {$w_sql}<br>");
            }
        }
    }
}
