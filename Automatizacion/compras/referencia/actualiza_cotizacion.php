<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';

if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);
if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$cotizacion = $decodedT[orden_venta];
$id_order   = $decodedT[id_order];

$sql = "UPDATE lista_ordenes_referencia SET cot = '{$cotizacion}', cotizacion = 1 WHERE id_order = {$id_order}";
if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
    print_r("true");
} else {
    print_r("false :: {$sql}");
}
