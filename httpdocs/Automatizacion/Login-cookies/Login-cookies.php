<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;
date_default_timezone_set("America/Chihuahua");

$conn = new mysqli($servername, $username, $password, $dbname);

if (isset($_COOKIE['login-cookie'])) {
    $valor = $_COOKIE['login-cookie'];
    print_r("Si existe la cookie ");
    return true;
} else {
    $sql = "SELECT value FROM prstshp_configuation
    WHERE name = 'PS_COOKIE_LOGIN'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $valor_cookie = $row['valor'];
            if ($valor_cookie = !'' && $valor_cookie <= '2500') {
                $valor_cookie++
                $sql = "update  prstshp_configuration
                        set valor = " . $valor_cookie .
                    "where name = 'PS_COOKIE_LOGIN'";
                $conn->query($sql);
                setcookie('login-cookie', $valor_cookie, time() + (60 * 60 * 24 * 2));
            } else {
                return false
            }
        }
    }
    print_r("Se creo cookie con valor: " . $valor_cookie);
    return true;

}
