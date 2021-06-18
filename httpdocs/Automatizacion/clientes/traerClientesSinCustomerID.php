<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

$db_index = "prstshp_";
$conn     = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$clientes = [];
$sql      = "SELECT id_customer, firstname, lastname, email, RFC FROM {$db_index}customer WHERE (RFC IS NOT NULL AND RFC != '') AND (customerID IS NULL OR customerID = '')";
$result   = $conn->query($sql);
capuraLogs::nuevo_log("traerClientesSinCustomerID sql : {$sql}");
capuraLogs::nuevo_log("traerClientesSinCustomerID result->num_rows : {$result->num_rows}");
if ($result->num_rows > 0) {
    $contador = 0;
    while ($row = $result->fetch_assoc()) {
        $id_customer = $row[id_customer];
        $firstname   = utf8_encode($row[firstname]);
        $lastname    = utf8_encode($row[lastname]);
        $email       = utf8_encode($row[email]);
        $RFC         = utf8_encode($row[RFC]);
        $filtro      = "dataAreaId eq 'atp' and RFCNumber eq '{$RFC}'";
        if ($RFC == 'XAXX010101000') {
            $filtro = "";
        }
        $clientes[$contador] = array("id_customer" => (int) $id_customer,
            "firstname"                                => "{$firstname}",
            "lastname"                                 => "{$lastname}",
            "email"                                    => "{$email}",
            "RFC"                                      => "{$RFC}",
            "filtro"                                   => "{$filtro}",
        );
        $contador++;
    }
}
echo json_encode($clientes);
