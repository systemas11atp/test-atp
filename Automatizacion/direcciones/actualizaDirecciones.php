<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
include_once dirname(__FILE__) . '/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

include_once '/classes/Cookie.php';
include '/init.php';
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

$content = trim(file_get_contents("php://input"));

$decodedT = json_decode($content, true);

if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$activeStore = explode("/", $_SERVER['REQUEST_URI'])[1];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id_address = $decodedT[id_address];
$accion     = $decodedT[accion];
//capuraLogs::nuevo_log("actualizaDirecciones id_address : {$id_address}");
//capuraLogs::nuevo_log("actualizaDirecciones accion : {$accion}");

if ($accion == 'actualizar') {
    sleep(3);
    $sql = "SELECT pa.id_customer, pc.customerID, pa.id_address, pa.alias, pcl.name as pais, pcy.iso_code  as iso_pais, ps.name as estado, ps.iso_code  as iso_estado, pa.city as ciudad, pa.colony as colonia, pa.address1 as calle, pa.postcode as codigo_postal, cpg.id_codigo_postal, cpg.CountyId, pa.addressID, pa.effective ";
    $sql .= "FROM prstshp_address pa  ";
    $sql .= "INNER JOIN codigos_postales_generales cpg ON cpg.asentamiento = pa.colony AND  cpg.codigo_postal = pa.postcode ";
    $sql .= "INNER JOIN prstshp_country_lang pcl ON pcl.id_country = pa.id_country AND pcl.id_lang = 2 ";
    $sql .= "INNER JOIN prstshp_country pcy ON pcy.id_country = pa.id_country ";
    $sql .= "INNER JOIN prstshp_state ps ON ps.id_state = pa.id_state   ";
    $sql .= "INNER JOIN prstshp_customer pc ON pc.id_customer = pa.id_customer AND pc.customerID IS NOT NULL ";
    $sql .= "WHERE pa.id_address = {$id_address} ";

    $result   = $conn->query($sql);
    $clientes = [];
    //capuraLogs::nuevo_log("actualizaDirecciones sql : {$sql}");
    //capuraLogs::nuevo_log("actualizaDirecciones result->num_rows : {$result->num_rows}");
    if ($result->num_rows > 0) {
        $contador = 0;
        while ($row = $result->fetch_assoc()) {
            $id_customer      = $row[id_customer];
            $customerID       = $row[customerID];
            $id_address       = $row[id_address];
            $addressID        = $row[addressID];
            $alias            = utf8_encode($row[alias]);
            $pais             = utf8_encode($row[pais]);
            $iso_pais         = $row[iso_pais];
            $estado           = utf8_encode($row[estado]);
            $iso_estado       = $row[iso_estado];
            $ciudad           = utf8_encode($row[ciudad]);
            $colonia          = utf8_encode($row[colonia]);
            $calle            = utf8_encode($row[calle]);
            $codigo_postal    = $row[codigo_postal];
            $id_codigo_postal = $row[id_codigo_postal];
            if ($row[CountyId] == null) {
                $CountyId = "-";
            } else {
                $CountyId = $row[CountyId];
            }
            if ($row[effective] == null) {
                $effective = "-";
            } else {
                $effective = $row[effective];
            }
            //capuraLogs::nuevo_log("actualizaDirecciones effective : {$effective}");
            $clientes[$contador] = array(
                "id_customer"      => (int) $id_customer,
                "customerID"       => $customerID,
                "id_address"       => (int) $id_address,
                "addressID"        => $addressID,
                "accion"           => $accion,
                "alias"            => $alias,
                "pais"             => $pais,
                "iso_pais"         => $iso_pais,
                "estado"           => $estado,
                "iso_estado"       => $iso_estado,
                "ciudad"           => $ciudad,
                "colonia"          => $colonia,
                "calle"            => $calle,
                "codigo_postal"    => $codigo_postal,
                "id_codigo_postal" => $id_codigo_postal,
                "CountyId"         => $CountyId,
                "effective"        => $effective,

            );
            $contador++;
        }
    }
} else {
    $sql = "SELECT pa.id_customer, pc.customerID, pa.id_address, pa.alias, pcl.name as pais, pcy.iso_code  as iso_pais, ps.name as estado, ps.iso_code  as iso_estado, pa.city as ciudad, pa.colony as colonia, pa.address1 as calle, pa.postcode as codigo_postal, cpg.id_codigo_postal, cpg.CountyId, pa.addressID, pa.effective ";
    $sql .= "FROM prstshp_address pa  ";
    $sql .= "INNER JOIN codigos_postales_generales cpg ON cpg.asentamiento = pa.colony AND  cpg.codigo_postal = pa.postcode ";
    $sql .= "INNER JOIN prstshp_country_lang pcl ON pcl.id_country = pa.id_country AND pcl.id_lang = 2 ";
    $sql .= "INNER JOIN prstshp_country pcy ON pcy.id_country = pa.id_country ";
    $sql .= "INNER JOIN prstshp_state ps ON ps.id_state = pa.id_state   ";
    $sql .= "INNER JOIN prstshp_customer pc ON pc.id_customer = pa.id_customer AND pc.customerID IS NOT NULL ";
    $sql .= "WHERE pa.id_address = {$id_address} ";

    $result   = $conn->query($sql);
    $clientes = [];
    //capuraLogs::nuevo_log("actualizaDirecciones result->num_rows : {$result->num_rows}");
    if ($result->num_rows > 0) {
        $contador = 0;
        while ($row = $result->fetch_assoc()) {
            $id_customer      = $row[id_customer];
            $customerID       = $row[customerID];
            $id_address       = $row[id_address];
            $addressID        = $row[addressID];
            $alias            = utf8_encode($row[alias]);
            $pais             = utf8_encode($row[pais]);
            $iso_pais         = $row[iso_pais];
            $estado           = utf8_encode($row[estado]);
            $iso_estado       = $row[iso_estado];
            $ciudad           = utf8_encode($row[ciudad]);
            $colonia          = utf8_encode($row[colonia]);
            $calle            = utf8_encode($row[calle]);
            $codigo_postal    = $row[codigo_postal];
            $id_codigo_postal = $row[id_codigo_postal];
            if ($row[CountyId] == null) {
                $CountyId = "-";
            } else {
                $CountyId = $row[CountyId];
            }
            if ($row[effective] == null) {
                $effective = "-";
            } else {
                $effective = $row[effective];
            }
            $clientes[$contador] = array(
                "id_customer"      => (int) $id_customer,
                "customerID"       => $customerID,
                "id_address"       => (int) $id_address,
                "addressID"        => $addressID,
                "accion"           => $accion,
                "alias"            => $alias,
                "pais"             => $pais,
                "iso_pais"         => $iso_pais,
                "estado"           => $estado,
                "iso_estado"       => $iso_estado,
                "ciudad"           => $ciudad,
                "colonia"          => $colonia,
                "calle"            => $calle,
                "codigo_postal"    => $codigo_postal,
                "id_codigo_postal" => $id_codigo_postal,
                "CountyId"         => $CountyId,
                "effective"        => $effective,

            );
            $contador++;
        }
    }
}

echo json_encode($clientes);
exit();
