<?php
$activeStore = explode("/", $_SERVER['REQUEST_URI'])[1];
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

$sql    = "SELECT id_stock_available FROM {$db_index}stock_global WHERE Unidad IS NULL OR Unidad = '' GROUP BY id_stock_available LIMIT 500";
$stocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
print_r("sql :: {$sql}<br>");

$echelemichuy = 1;
foreach ($stocks as $stock) {
    $id_stock_available = $stock['id_stock_available'];
    $sql_unidad         = "SELECT unidadVenta,id_stock_available, reference FROM  {$db_index}stock_available WHERE id_stock_available = {$id_stock_available}";
    $unidades           = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_unidad);
    foreach ($unidades as $unidad) {
        $n_unidad             = $unidad[unidadVenta];
        $n_id_stock_available = $unidad[id_stock_available];
        $reference            = $unidad[reference];
        $sql_insert           = "UPDATE {$db_index}stock_global SET Unidad = '{$n_unidad}' WHERE id_stock_available = {$n_id_stock_available}";
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql_insert)) {
            // print_r("{$echelemichuy}) ok ok  : : {$sql_insert}<br>");
            print_r("{$echelemichuy}) ok ok ({$reference}) : : {$sql_insert}<br>");
        } else {
            print_r("{$echelemichuy}) no no no : : {$sql_insert}<br>");
            $echelemichuy++;
        }
    }

}
