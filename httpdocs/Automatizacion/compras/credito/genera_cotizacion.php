<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';

$sql = "SELECT loc.id_cart,loc.id_order, pc.firstname, pc.lastname, loc.carrier, loc.sitio,pc.customerID, CONCAT(pc.customerID,', ',loc.id_order) as referencia, '99' as payment, loc.paqueterias as shipping ";
$sql .= "FROM lista_ordenes_credito loc ";
$sql .= "INNER JOIN  prstshp_customer pc ON pc.customerID = loc.customerID ";
$sql .= "INNER JOIN  prstshp_orders po ON po.id_order = loc.id_order AND pc.id_customer = po.id_customer ";
$sql .= "WHERE loc.cotizacion = 0 AND loc.cot = ''";
$orderInfo = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$info      = array();
$contador  = 0;
foreach ($orderInfo as $item) {
    $id_order      = $item[id_order];
    $firstname     = $item[firstname];
    $lastname      = $item[lastname];
    $carrier       = $item[carrier];
    $sitio         = $item[sitio];
    $customerID    = $item[customerID];
    $referencia    = $item[referencia];
    $payment       = $item[payment];
    $id_cart       = $item[id_cart];
    $sql_sitios    = "SELECT sitio FROM prstshp_cart_product WHERE id_cart = {$id_cart} GROUP BY sitio";
    $sitios        = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_sitios);
    $sitioscompras = array();
    $cuentaSitios  = 0;
    foreach ($sitios as $sitio) {
        $nsitio = $sitio[sitio];

        $sql_carriers = "SELECT SUM(precio) as total, SUM(tarimas) as tarimas FROM prstshp_cart_carriers WHERE id_cart = {$id_cart} AND sitio = '{$nsitio}'";
        $carriers     = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_carriers);
        $paqueteria   = 0;
        $tarimas      = 0;
        if (count($carriers) > 0) {
            $paqueteria = $carriers[0][total];
            $tarimas    = $carriers[0][tarimas];
        }

        $nsql = "SELECT pod.product_quantity as cantidad , pod.product_reference as referenciaP, pod.unit_price_tax_excl as precio, pod.group_reduction as descuento, pcp.sitio, sa.categoria ";
        $nsql .= "FROM prstshp_order_detail pod ";
        $nsql .= "INNER JOIN prstshp_cart_product pcp ON pcp.id_cart = {$id_cart} AND pcp.id_product = pod.product_id AND  pcp.id_product_attribute = pod.product_attribute_id ";
        $nsql .= "INNER JOIN prstshp_stock_available sa ON sa.id_product = pod.product_id AND  sa.id_product_attribute = pod.product_attribute_id ";
        $nsql .= "WHERE pod.id_order = {$id_order} AND pod.product_reference like '%-%' AND pcp.sitio = '{$nsitio}' ";
        $nsql .= "ORDER BY sitio DESC";
        $productos = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($nsql);

        $lineascompras   = array();
        $cuentaProductos = 0;
        $seguro          = 0;
        foreach ($productos as $producto) {
            $referenciaP = $producto[referenciaP];
            $pos         = strpos($referenciaP, "-R");
            $cantidad    = $producto[cantidad];
            $precio      = $producto[precio] * .98;
            $seguro += ($precio * .98);
            if ($pos !== false) {
                $referenciaP = str_replace("-R", "", $referenciaP);
                $sql_multi   = "SELECT * FROM prstshp_stock_available WHERE reference ='$referenciaP'";
                $multi       = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_multi);
                if (count($multi) > 0) {
                    $cantidad = (float) $cantidad * (float) ($multi[0][imultiply]);
                    $precio   = ($precio / $cantidad);
                }
            }
            $descuento                       = $producto[descuento];
            $lineascompras[$cuentaProductos] = array(
                'cantidad'    => (float) $cantidad,
                'referenciaP' => $referenciaP,
                'precio'      => (float) $precio,
                'descuento'   => (float) $descuento,
            );
            $cuentaProductos++;
        }
        $seguro = $seguro * .01;
        if ($seguro < 10) {
            $seguro = 10;
        }
        $sitioscompras[$cuentaSitios] = array(
            "sitio"         => $nsitio,
            "paqueteria"    => (float) $paqueteria,
            "seguro"        => (float) $seguro,
            "tarimas"       => (int) $tarimas,
            "Lineascompras" => $lineascompras,
        );
        $cuentaSitios++;
    }
    $info[$contador] = array(
        "id_order"      => (int) $id_order,
        "id_cart"       => (int) $id_cart,
        "firstname"     => $firstname,
        "lastname"      => $lastname,
        "carrier"       => $carrier,
        "customerID"    => $customerID,
        "referencia"    => $referencia,
        "payment"       => $payment,
        "sitiosCompras" => $sitioscompras,
    );
    $contador++;
}

print_r(json_encode($info));
