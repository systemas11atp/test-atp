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
$clientes = [];
$sql      = "SELECT * FROM clientes_credito ";
$result   = $conn->query($sql);
capuraLogs::nuevo_log("clientes_credito sql : {$sql}");

if ($result->num_rows > 0) {
    $contador = 0;
    while ($row = $result->fetch_assoc()) {
        $id_customer = $row[id_customer];
        $limite      = $row[limite];
        $customerID  = $row[customerID];
        $fecha       = '1900-01-01T00:00:00Z';
        $sql_f       = "SELECT fecha FROM historial_creditos WHERE id_customer = {$id_customer} ORDER BY fecha DESC LIMIT 1";
        $result_f    = $conn->query($sql_f);
        if ($result_f->num_rows > 0) {
            while ($row_f = $result_f->fetch_assoc()) {
                $fecha = str_replace(" ", "T", $row_f[fecha]);
                $fecha .= "Z";
            }
        }
        $clientes[$contador] = array(
            "id_customer" => (int) $id_customer,
            "limite"      => (float) $limite,
            "customerID"  => "{$customerID}",
            "fecha"       => "{$fecha}",
        );
        $contador++;
    }
}
echo json_encode($clientes);
