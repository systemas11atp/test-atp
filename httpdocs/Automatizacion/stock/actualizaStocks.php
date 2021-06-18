<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;

if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

$content = trim(file_get_contents("php://input"));

$decodedT = json_decode($content, true);

if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}
$id_stock_available = $decodedT[id_stock];
$valores            = $decodedT[valores];
$fecha              = date("Y-m-d");
$sql                = "UPDATE {$db_index}stock_global SET quantity = 0 WHERE id_stock_available = {$id_stock_available}";
//print_r("sql ::: {$sql}<br>");
Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
//print_r("id_stock_available :: {$id_stock_available}<br>");
$total = 0;
$sitio = "";
foreach ($valores as $valor) {
    $InventorySiteId = $valor[InventorySiteId];
    $OnHandQuantity  = $valor[OnHandQuantity];
    if ($InventorySiteId != $sitio) {
        if ($sitio != "") {
            $total = ((int) $total) - 1;
            if ($total < 0) {
                $total = 0;
            }
            //print_r("total :: {$total}<br>");
            //print_r("sitio :: {$sitio}<br><br>");
            $sql = "UPDATE {$db_index}stock_global SET quantity = {$total} WHERE id_stock_available = {$id_stock_available} AND sitio = '{$sitio}'";
            //print_r("sql ::: {$sql}<br>");
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
        }
        $sitio = $InventorySiteId;
        $total = 0;
    }
    $total += (float) $OnHandQuantity;
}
$total = ((int) $total) - 1;
if ($total < 0) {
    $total = 0;
}
//print_r("total :: {$total}<br>");
//print_r("sitio :: {$sitio}<br><br>");
$sql = "UPDATE {$db_index}stock_global SET quantity = {$total} WHERE id_stock_available = {$id_stock_available} AND sitio = '{$sitio}'";
//print_r("sql ::: {$sql}<br>");
Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
$sql = "UPDATE {$db_index}stock_global SET  actualizado = '{$fecha}' WHERE id_stock_available = {$id_stock_available}";
//print_r("sql ::: {$sql}<br>");
Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
