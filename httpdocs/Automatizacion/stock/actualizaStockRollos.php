<?php

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;
date_default_timezone_set("America/Chihuahua");
set_time_limit(0);
$fecha  = date("Y-m-d");
$sql    = "SELECT id_stock_available, id_product, id_product_attribute, REPLACE(reference,'-R','') as reference, quantity, sitio, Unidad, actualizado FROM {$db_index}stock_global WHERE actualizado < '{$fecha}' GROUP BY id_stock_available ORDER BY id_stock_available desc, sitio asc LIMIT 67";
$stocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
print_r(json_encode($stocks));
