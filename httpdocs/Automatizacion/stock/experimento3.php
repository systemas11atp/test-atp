<?php

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;
include '../token.php';

date_default_timezone_set("America/Chihuahua");
set_time_limit(0);
$token = new Token();

$tokenTemp = $token->getToken("ATP", "prue");
$token     = $tokenTemp[0]->Token;

$fecha = date("Y-m-d") . " 12:00:00";
$sql   = "SELECT id_stock_available,imultiply FROM {$db_index}stock_available  WHERE id_shop = 0 AND id_shop_group = 1 AND reference like '%-%' AND actualizado < '{$fecha}' ORDER BY id_stock_available ASC LIMIT 350";
print_r("sql :: {$sql}<br><br>");
//exit();
$stocks       = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$echelemichuy = 1;
foreach ($stocks as $stock) {
    $id_stock_available = $stock['id_stock_available'];
    $imultiply          = (float) $stock['imultiply'];
    $sql2               = "SELECT quantity,Unidad FROM  {$db_index}stock_global WHERE id_stock_available = {$id_stock_available} AND actualizado = '{$fecha}' ORDER BY quantity DESC LIMIT 1";
    $quantity           = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql2);
    if (count($quantity) > 0) {
        $qty    = (int) $quantity[0]['quantity'];
        $unidad = $quantity[0]['Unidad'];
        if ($undad == "RolloD") {
            $qty = ((int) ($qty / $imultiply)) - 1;
            if ($qty < 0) {
                $qty = 0;
            }
        }
        $sql_update = "UPDATE {$db_index}stock_available SET quantity = {$qty}, actualizado = '{$fecha}' WHERE id_stock_available = {$id_stock_available}";
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_update)) {
            print_r("{$echelemichuy})sql_update ok ok<br>{$sql_update}<br>");
        } else {
            print_r("{$echelemichuy})no no no {$sql_update}<br>");
        }
        $echelemichuy++;
    }
}
