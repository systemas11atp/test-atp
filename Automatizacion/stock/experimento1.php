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

$sql    = "SELECT * FROM {$db_index}stock_available WHERE id_shop_group = 1 AND reference like '%-%' AND id_shop = 0  AND id_stock_available not in (SELECT id_stock_available FROM {$db_index}stock_global) ORDER BY id_stock_available LIMIT 500";
$stocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
print_r("sql :: {$sql}<br>");
$sql    = "SELECT sitio FROM sucursales";
$sitios = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
print_r("sql :: {$sql}<br>");
$echelemichuy = 1;
foreach ($stocks as $stock) {
    $id_stock_available   = $stock['id_stock_available'];
    $id_product           = $stock['id_product'];
    $id_product_attribute = $stock['id_product_attribute'];
    $reference            = $stock['reference'];
    $unidadVenta          = $stock['unidadVenta'];
    if ($reference != '-R') {
        foreach ($sitios as $sitio) {
            $quantity = random_int(1, 50);
            $nsitio   = $sitio['sitio'];
            $values   = "({$id_stock_available}, ";
            $values .= "{$id_product}, ";
            $values .= "{$id_product_attribute}, ";
            $values .= "'{$reference}', ";
            $values .= "{$quantity}, ";
            $values .= "'{$nsitio}',";
            $values .= "'{$unidadVenta}',";
            $values .= "'0000-00-00')";
            $sql_insert = "INSERT INTO {$db_index}stock_global VALUES {$values}";

            if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_insert)) {
                print_r("{$echelemichuy}ok ok  : : {$sql_insert}<br>");
            } else {
                print_r("{$echelemichuy}no no no : : {$sql_insert}<br>");
            }
            print_r("<br>");
            $echelemichuy++;
        }
    }
    print_r("<br>--------------------------------------------------------<br><br>");
}
