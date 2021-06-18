<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$db_index = _DB_PREFIX_;

$id_cart = $context->cookie->id_cart;
if ($id_cart == "") {
    $id_cart = 40;
}
$sql             = "SELECT pago_tarjeta, monto FROM prstshp_cart WHERE id_cart = {$id_cart}";
$cartInformation = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
$info            = array();
foreach ($cartInformation as $item) {
    $info = $item;
}
print_r(json_encode($info));
