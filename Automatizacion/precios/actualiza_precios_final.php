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

include '../token.php';
date_default_timezone_set("America/Chihuahua");
set_time_limit(0);
$token = new Token();

$tokenTemp = $token->getToken("ATP", "prod");
$token     = $tokenTemp[0]->Token;

$servername = "localhost";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    //print_r("PASS 1<br>");
}

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$di           = "prstshp_";
$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
} else {
    //print_r("PASS 2<br>");
}

$POSTFIELDS = "{}";
$fecha      = date("Y-m-d");
$fecha_tc   = date("Y-m-d");
$url        = "https://ayt.operations.dynamics.com/Data/ExchangeRates?%24top=1&%24orderby=StartDate%20desc&%24filter=RateTypeName%20eq%20'ATP'";
//print_r("url ::: {$url}<br>");

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => "",
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "GET",
    CURLOPT_POSTFIELDS     => $POSTFIELDS,
    CURLOPT_HTTPHEADER     => array(
        "authorization: Bearer {$token}",
        "content-type: application/json",
    ),
));

$responseP = curl_exec($curl);
$err       = curl_error($curl);

curl_close($curl);
$tc = 100;
if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $response = json_decode($responseP);
    foreach ($response as $resp) {
        if (is_array($resp)) {
            foreach ($resp as $value) {
                $tc       = $value->Rate;
                $fecha_tc = date($value->StartDate);

            }
        }
    }
}
//capuraLogs::actualiza_precios("0) fecha_tc :: {$fecha_tc}");
//capuraLogs::actualiza_precios("0) fecha :: {$fecha}");
//capuraLogs::actualiza_precios("0) fecha :: {$fecha}");
//capuraLogs::actualiza_precios("0) tc :: {$tc}");
//$sql = "SELECT app.* FROM actualiza_precios_ps app WHERE act_ps  < '2021-05-25' AND precio_final > 0 ORDER BY referencia";
//$sql = "SELECT * FROM actualiza_precios_ps WHERE act_ps  < '{$fecha}' AND precio_final > 0 ORDER BY referencia";
$sql = "SELECT * FROM actualiza_precios_ps WHERE act_ps  < '{$fecha}' AND precio_final > 0 ORDER BY id_product ASC, id_product_attribute ASC, referencia";
//capuraLogs::actualiza_precios("0) sql :: {$sql}");
if ($fecha_tc < $fecha) {
    exit();
}
$result      = $conn_precios->query($sql);
$fecha_str   = date("Y-m-d H:i:s");
$hechelepue  = 0;
$myproduct   = 0;
$precioPadre = 0;
$primero     = true;
$limite      = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $moneda     = $row[moneda];
        $mxn        = $row[mxn];
        $usd        = $row[usd];
        $unidad     = "";
        $referencia = $row[referencia];
        $sqlUnidad  = "SELECT Unidad FROM list_price WHERE ItemRelation = '{$referencia}' AND NombreLista = 'CHIH-A' GROUP BY Unidad";
        //print_r("sqlUnidad ::: {$sqlUnidad}<br>");
        $runi = $conn_precios->query($sqlUnidad);
        if ($runi->num_rows > 0) {
            while ($rowuni = $runi->fetch_assoc()) {
                $unidad = $rowuni[Unidad];
            }
        }
        $id_product = $row[id_product];
        //print_r("id_product :: {$id_product}<br>");
        $id_product_attribute = $row[id_product_attribute];
        //print_r("id_product_attribute :: {$id_product_attribute}<br>");
        //capuraLogs::actualiza_precios("1) moneda :: {$moneda}");
        //capuraLogs::actualiza_precios("2) mxn :: {$mxn}");
        //capuraLogs::actualiza_precios("3) usd :: {$usd}");
        //capuraLogs::actualiza_precios("4) unidad :: {$unidad}");
        //capuraLogs::actualiza_precios("5) referencia :: {$referencia}");
        //capuraLogs::actualiza_precios("6) id_product :: {$id_product}");
        //capuraLogs::actualiza_precios("7) id_product_attribute :: {$id_product_attribute}");

        $precio = $row[precio_final];
        $act_ps = $row[act_ps];
        if ($id_product != $myproduct) {
            if ($primero) {
                $myproduct   = $id_product;
                $precioPadre = $precio;
                //print_r("- - - - myproduct ::: {$myproduct}<br>");
                //print_r("- - - - precioPadre ::: {$precioPadre}<br>");
                $limite++;
                print_r("- - - - limite ::: {$limite}, ({$unidad})<br>");
                if ($limite == 150) {
                    $primero = false;
                }
            } else {
                print_r("{$fecha_str} ::: Comenzo<br>");
                $fecha_str = date("Y-m-d H:i:s");
                print_r("{$fecha_str} ::: Fin<br>");
                exit();
            }
        }
        $paso = true;
        if ($id_product_attribute == 0) {
            //print_r("123<br>");
            $nsql = "UPDATE prstshp_product SET price = {$precio} WHERE id_product = {$id_product} ";
            //capuraLogs::actualiza_precios("8) nsql :: {$nsql}");
            if ($conn->query($nsql)) {
            } else {
                $paso = false;
            }
            $w_sql = "UPDATE prstshp_product_shop SET id_tax_rules_group = 53, price = {$precio} WHERE id_product = {$id_product}";
            //capuraLogs::actualiza_precios("9) w_sql :: {$w_sql}");
            if ($conn->query($w_sql)) {
            } else {
                $paso = false;
            }
            $w_sql = "UPDATE prstshp_product SET id_tax_rules_group = 53, price = {$precio} WHERE id_product = {$id_product} ";
            //capuraLogs::actualiza_precios("10) w_sql :: {$w_sql}");
            if ($conn->query($w_sql)) {
            } else {
                $paso = false;
            }
            $w_sql = "UPDATE prstshp_layered_price_index SET price_min = {$precio}, price_max = {$precio} WHERE id_product = {$id_product} AND id_currency = 2 AND id_shop = 1 AND id_country = 145";
            //capuraLogs::actualiza_precios("11) w_sql :: {$w_sql}");
            if ($conn->query($w_sql)) {
            } else {
                $paso = false;
            }
            $w_sql = "UPDATE prstshp_specific_price_priority SET priority = 'id_shop;id_currency;id_country;id_group' WHERE id_product = {$id_product}";
            //capuraLogs::actualiza_precios("12) w_sql :: {$w_sql}");
            if ($conn->query($w_sql)) {
            } else {
                $w_sql = "INSERT INTO prstshp_specific_price_priority (priority, id_product) VALUES  ('id_shop;id_currency;id_country;id_group', {$id_product})";
                //capuraLogs::actualiza_precios("13) w_sql :: {$w_sql}");
                $conn->query($w_sql);
                $paso = false;
            }
            $w_sql = "UPDATE prstshp_specific_price SET price = {$precio} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute} AND id_group = 0";
            //capuraLogs::actualiza_precios("14) w_sql :: {$w_sql}");
            if ($conn->query($w_sql)) {
            } else {
                $w_sql = "INSERT INTO prstshp_specific_price VALUES(null,1,0,{$id_product},1,0,0,0,0,0,{$id_product_attribute},{$precio},1,0.02,0,'percentage','0000-00-00 00:00:00','0000-00-00 00:00:00')";
                //capuraLogs::actualiza_precios("15) w_sql :: {$w_sql}");
                $conn->query($w_sql);
                $paso = false;
            }

        } else {
            //print_r("124<br>");
            $nprice = $precio - $precioPadre;
            $txt    = "precio_padre = {$precioPadre}, precio_hijo = {$precio}, precio_final = {$nprice}";
            //capuraLogs::actualiza_precios("16) txt :: {$txt}");
            $nsql = "UPDATE prstshp_product_attribute SET price = {$nprice}, {$txt} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute}";
            //capuraLogs::actualiza_precios("16) nsql :: {$nsql}");
            if ($conn->query($nsql)) {
            } else {
                $paso = false;
            }
            $w_sql = "UPDATE prstshp_product_attribute_shop SET price = {$nprice} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute}";
            //capuraLogs::actualiza_precios("17) w_sql :: {$w_sql}");
            if ($conn->query($w_sql)) {
            } else {
                $paso = false;
            }
        }
        //print_r("125<br>");
        if ($unidad == "M" || $unidad == "PIES") {
            //print_r("125<br>");
            $rollo_sql = "SELECT ppa.*,psa.imultiply FROM prstshp_product_attribute ppa  INNER JOIN prstshp_stock_available psa ON psa.reference = '{$referencia}' WHERE ppa.reference LIKE '{$referencia}%' GROUP BY id_product_attribute";
            //capuraLogs::actualiza_precios("18) rollo_sql :: {$rollo_sql}");
            $resultnrollo               = $conn->query($rollo_sql);
            $id_product_attribute_rollo = "";
            $imultiplymin               = 0;
            $imultiply                  = 1;
            $imultiplyMax               = 1;
            $rollo_precio               = $precio * 360;
            $id_product_attribute_metro = "";
            if ($resultnrollo->num_rows > 0) {
                while ($rown_existe = $resultnrollo->fetch_assoc()) {
                    $id_product_attribute_rollo = $rown_existe[id_product_attribute];
                    $sql_rollo                  = "";
                    if ($rown_existe[unitId] != "RolloD") {
                        $id_product_attribute_metro = $rown_existe[id_product_attribute];
                    }
                    if ($rown_existe[unitId] == "RolloD") {
                        $sql_rollo = "SELECT * FROM prstshp_specific_price WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute_rollo} AND from_quantity >= {$imultiplymin} AND from_quantity <= {$imultiplyMax} ORDER BY id_group ASC";
                        //capuraLogs::actualiza_precios("19) sql_rollo :: {$sql_rollo}");
                        $imultiply    = (float) $rown_existe[imultiply];
                        $imultiplymin = ((int) $rown_existe[imultiply]) - 1;
                        $imultiplyMax = ((int) $rown_existe[imultiply]) + 1;
                        $rollo_precio = $precio * $imultiply;
                        $nprice       = $rollo_precio - $precioPadre;
                        $nsql         = "UPDATE prstshp_product_attribute SET price = {$nprice} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute_rollo}";
                        //capuraLogs::actualiza_precios("20) nsql :: {$nsql}");
                        if ($conn->query($nsql)) {
                        } else {
                            $paso = false;
                        }
                        $w_sql = "UPDATE prstshp_product_attribute_shop SET price = {$nprice} WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute_rollo}";
                        //capuraLogs::actualiza_precios("21) w_sql :: {$w_sql}");
                        if ($conn->query($w_sql)) {
                        } else {
                            $paso = false;
                        }
                        $sql = "SELECT * FROM prstshp_specific_price WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute_metro} GROUP BY id_group";
                        //capuraLogs::actualiza_precios("22) sql :: {$sql}");
                        $result_rollo = $conn->query($sql);
                        if ($result_rollo->num_rows > 0) {
                            while ($rowCheck = $result_rollo->fetch_assoc()) {
                                $id_group      = $rowCheck[id_group];
                                $sql_existe    = "SELECT * FROM prstshp_specific_price WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute_rollo} AND id_group = {$id_group} GROUP BY id_group ORDER BY reduction desc";
                                $result_existe = $conn->query($sql_existe);
                                if ($result_existe->num_rows > 0) {
                                } else {
                                    $sql_existe = "SELECT * FROM prstshp_specific_price WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute_metro} AND id_group = {$id_group} ORDER BY reduction DESC LIMIT 1";
                                    //capuraLogs::actualiza_precios("23) sql_existe :: {$sql_existe}");
                                    $result_existe = $conn->query($sql_existe);
                                    if ($result_existe->num_rows > 0) {
                                        while ($row_existe = $result_existe->fetch_assoc()) {
                                            $id_specific_price_rule = $row_existe[id_specific_price_rule];
                                            $id_cart                = $row_existe[id_cart];
                                            $id_product             = $row_existe[id_product];
                                            $id_shop                = $row_existe[id_shop];
                                            $id_shop_group          = $row_existe[id_shop_group];
                                            $id_currency            = $row_existe[id_currency];
                                            $id_country             = $row_existe[id_country];
                                            $id_group               = $row_existe[id_group];
                                            $id_customer            = $row_existe[id_customer];
                                            $id_product_attribute   = $id_product_attribute_rollo;
                                            $price                  = $row_existe[price];
                                            $from_quantity          = 1;
                                            $reduction              = $row_existe[reduction];
                                            $reduction_tax          = $row_existe[reduction_tax];
                                            $reduction_type         = $row_existe[reduction_type];
                                            $from                   = $row_existe[from];
                                            $to                     = $row_existe[to];
                                            $sqlInsert              = "INSERT INTO prstshp_specific_price VALUES (null,{$id_specific_price_rule}, {$id_cart}, {$id_product}, {$id_shop}, {$id_shop_group}, {$id_currency}, {$id_country}, {$id_group}, {$id_customer}, {$id_product_attribute}, {$price}, {$from_quantity}, {$reduction}, {$reduction_tax}, '{$reduction_type}', '{$from}', '{$to}')";
                                            //capuraLogs::actualiza_precios("24) sqlInsert :: {$sqlInsert}");
                                            if ($conn->query($sqlInsert)) {
                                            } else {
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $result2 = $conn->query($sql);
                    if ($result2->num_rows > 0) {
                        while ($row2 = $result2->fetch_assoc()) {
                            $id_group = $row2[id_group];
                        }
                    }
                }
            }

        }
        if ($paso) {
            $sql_update = "UPDATE actualiza_precios_ps SET act_ps= '{$fecha}' WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute} ";
            //capuraLogs::actualiza_precios("25) sql_update :: {$sql_update}");
            if ($conn_precios->query($sql_update)) {
                //print_r("actualizado ::: {$sql_update}<br>");
            }
        } else {
            $sql_update = "UPDATE actualiza_precios_ps SET act_ps= '{$act_ps}' WHERE id_product = {$id_product}";
            //capuraLogs::actualiza_precios("26) sql_update :: {$sql_update}");
            if ($conn_precios->query($sql_update)) {
                //print_r("actualizado ::: {$sql_update}<br>");
                exit();
            }
            if ($conn_precios->query($sql_update)) {
                //print_r("actualizado ::: {$sql_update}<br>");
                exit();
            }
            if ($conn_precios->query($sql_update)) {
                //print_r("actualizado ::: {$sql_update}<br>");
                exit();
            }
            exit();

        }
    }
}
print_r("{$fecha_str} ::: Comenzo<br>");
$fecha_str = date("Y-m-d H:i:s");
print_r("{$fecha_str} ::: Fin<br>");
exit();
