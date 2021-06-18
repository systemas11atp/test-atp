<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;

$sql = "SELECT po.id_order, po.id_cart,pca.id_reference, pcu.customerID, po.total_shipping ";
$sql .= "FROM prstshp_cart pc ";
$sql .= "INNER JOIN prstshp_orders po ON po.id_cart = pc.id_cart AND pc.pago_tarjeta = 1 ";
$sql .= "INNER JOIN prstshp_customer pcu ON po.id_customer = pcu.id_customer ";
$sql .= "INNER JOIN prstshp_carrier pca ON pc.id_carrier = pca.id_carrier ";
$sql .= "WHERE po.id_order NOT IN (SELECT id_order FROM lista_ordenes_tarjeta) ";

$orderInformation = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
foreach ($orderInformation as $item) {
    $id_order   = $item[id_order];
    $id_cart    = $item[id_cart];
    $customerID = $item[customerID];
    $carrier    = "E-PAS";
    if ($item[id_reference] == 2) {
        $carrier = "E-DOM";
    } else if ($item[id_reference] == 3) {
        $carrier = "E-PAQ";
    }
    $pay           = "PUE";
    $payment       = "28";
    $cotizacion    = 0;
    $orden_venta   = 0;
    $pago_aprobado = 1;
    $diario        = 0;
    $numero_diario = "";
    $liberacion    = 0;
    $correo        = 0;
    $paqueterias   = $item[total_shipping];
    $ov            = "";
    $error_ov      = 0;
    $cot           = "";
    $sitio         = "CHIH";
    $encarrier     = 0;
    $guia          = 0;
    $guiaspaq      = "";

    $sql_insert = "INSERT INTO lista_ordenes_tarjeta VALUES (null,{$id_order},{$id_cart},'{$carrier}','{$customerID}','{$pay}','{$payment}',{$cotizacion},{$orden_venta},{$pago_aprobado},{$diario},'{$numero_diario}',{$liberacion},{$correo},{$paqueterias},'{$ov}',{$error_ov},'{$cot}','{$sitio}',{$encarrier},{$guia},'{$guiaspaq}')";
    if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_insert)) {
        print_r("1 sql_insert ::: {$sql_insert}<br>");
    } else {
        print_r("2 sql_insert ::: {$sql_insert}<br>");
    }
}
