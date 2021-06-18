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

//$sql ="SELECT id_stock_available,id_product,id_product_attribute,reference,quantity FROM {$db_index}stock_available  WHERE id_shop = 0 AND id_shop_group = 1 AND reference like '%-%' AND id_stock_available not in (select id_stock_available FROM {$db_index}stock_global GROUP BY id_stock_available)";
$sql = "SELECT id_stock_available,sitio FROM {$db_index}stock_global";
print_r("sql :: {$sql}<br><br>");
$stocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
/*
$sql ="SELECT sitio FROM sucursales";
$sitios = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
-**/
$echelemichuy = 1;
foreach ($stocks as $stock) {
    $id_stock_available = $stock['id_stock_available'];
    $sitio              = $stock['sitio'];
    $quantity           = random_int(1, 50);
    $sql_update         = "UPDATE {$db_index}stock_global SET quantity = {$quantity} WHERE sitio = '{$sitio}' AND id_stock_available = {$id_stock_available}";
    if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_update)) {
        print_r("{$echelemichuy})sql_update ok ok<br>{$sql_update}<br><br>");
    } else {
        print_r("{$echelemichuy})no no no {$sql_update}<br><br><br>");
    }
    $echelemichuy++;
    /*
$id_product = $stock['id_product'];
$id_product_attribute = $stock['id_product_attribute'];
$reference = $stock['reference'];
$quantity = $stock['quantity'];
foreach ($sitios as $sitio) {
$sto = $sitio['sitio'];
$values = "({$id_stock_available},{$id_product},{$id_product_attribute},'{$reference}',{$quantity},'{$sto}')";

$sql_exist ="SELECT * FROM {$db_index}stock_global WHERE id_stock_available = {$id_stock_available} AND sitio = '{$sto}'";
$exist = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_exist);
print_r("count(exist)".count($exist)."<br>");
if(count($exist) == 0){
$sql_update ="INSERT INTO {$db_index}stock_global VALUES {$values}";
if(Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_update)){
print_r("{$echelemichuy})sql_update ok ok<br>{$sql_update}<br><br>");
}else{
print_r("{$echelemichuy})no no no {$sql_update}<br><br><br>");
}
$echelemichuy++;
}
}
 */
}
