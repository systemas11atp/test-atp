<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql_total = "SELECT * FROM {$db_index}customer WHERE con_rfc = 0 AND (RFC IS NULL OR RFC = '')";
capuraLogs::nuevo_log("actualiza_rfc_clientes sql_total : {$sql_total}");
$result = $conn->query($sql_total);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_customer    = $row[id_customer];
        $elRFC          = "XAXX010101000";
        $sql_rfc_update = "UPDATE {$db_index}customer SET RFC = '{$elRFC}', con_rfc = 1 WHERE id_customer = {$id_customer}";
        capuraLogs::nuevo_log("actualiza_rfc_clientes sql_rfc_update : {$sql_rfc_update}");
        $conn->query($sql_rfc_update);
    }

}
