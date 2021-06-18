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

set_time_limit(0);

if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    throw new Exception('Request method must be POST!');
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);
if (!is_array($decodedT)) {
    throw new Exception('Received content contained invalid JSON!');
}

$activeStore = explode("/", $_SERVER['REQUEST_URI'])[1];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$accion = $decodedT[accion];
switch ($accion) {
    case 'zipcode':
        forZipCode($decodedT[postCode], $conn);
        break;

    case 'porState':
        forState($decodedT[state], $conn);
        break;

    case 'porCity':
        forCity($decodedT[city], $conn);
        break;

    case 'paraEditar':
        forEdit($decodedT[postCode], $decodedT[id_address], $conn);
        break;

    case 'nuevas':
        forNewAddresses($conn);
        break;

    default:
        # code...
        break;
}

//$zip_code = 31050;

function forEdit($zip_code, $id_address, $conn)
{
    $estado            = 0;
    $ciudad            = "";
    $colonia           = "";
    $ciudades          = [];
    $colonias          = [];
    $contador_colonias = 0;
    $contador_ciudades = 0;
    $db_index          = _DB_PREFIX_;

    $sql_estado    = "SELECT id_state FROM codigos_postales_generales WHERE codigo_postal = {$zip_code} GROUP BY id_state";
    $result_estado = $conn->query($sql_estado);
    if ($result_estado->num_rows > 0) {
        while ($row_estado = $result_estado->fetch_assoc()) {
            $estado = $row_estado[id_state];
        }
    }

    $sql_ciudades    = "SELECT ciudad, codigo_postal FROM codigos_postales_generales WHERE id_state = {$estado} GROUP BY ciudad ORDER BY ciudad";
    $result_ciudades = $conn->query($sql_ciudades);
    if ($result_ciudades->num_rows > 0) {
        while ($row_ciudades = $result_ciudades->fetch_assoc()) {
            $ciudades[$contador_ciudades] = utf8_encode("{$row_ciudades[ciudad]}");
            $contador_ciudades++;
        }
    }

    $sql_colonias    = "SELECT  asentamiento, codigo_postal FROM codigos_postales_generales WHERE codigo_postal = {$zip_code} ORDER BY asentamiento";
    $result_colonias = $conn->query($sql_colonias);
    if ($result_colonias->num_rows > 0) {
        while ($row_colonias = $result_colonias->fetch_assoc()) {
            $colonias[$contador_colonias] = array("colonia" => utf8_encode("{$row_colonias[asentamiento]}"), "codigo_postal" => $row_colonias[codigo_postal]);
            $contador_colonias++;
        }
    }

    $sql_ciudad    = "SELECT city, colony FROM {$db_index}address WHERE id_address = {$id_address}";
    $result_ciudad = $conn->query($sql_ciudad);
    if ($result_ciudad->num_rows > 0) {
        while ($row_ciudad = $result_ciudad->fetch_assoc()) {
            $ciudad  = utf8_encode("{$row_ciudad[city]}");
            $colonia = utf8_encode("{$row_ciudad[colony]}");
        }
    }
    $direcciones = array("estado" => (int) $estado,
        "ciudad"                      => $ciudad,
        "colonia"                     => $colonia,
        "ciudades"                    => $ciudades,
        "colonias"                    => $colonias,
    );
    echo json_encode($direcciones);
}
function forNewAddresses($conn)
{
    $direcciones          = [];
    $contador_direcciones = 0;
    $sql_nuevas           = " SELECT pa.id_address, pa.alias, pa.address1, pa.address2, pa.postcode, ps.name, ps.iso_code, pa.city, pa.colony, pc.customerID  FROM prstshp_address pa";
    $sql_nuevas .= " INNER JOIN prstshp_customer pc ON pc.id_customer = pa.id_customer";
    $sql_nuevas .= " INNER JOIN prstshp_state ps ON ps.id_state = pa.id_state";
    $sql_nuevas .= " WHERE pc.customerID != '' AND pc.RFC != '' AND pc.RFC IS NOT NULL AND pc.customerID IS NOT NULL AND pa.addressID IS NULL";
    $result_nuevas = $conn->query($sql_nuevas);
    if ($result_nuevas->num_rows > 0) {
        while ($row_nuevas = $result_nuevas->fetch_assoc()) {
            $direcciones[$contador_direcciones] = array(
                "id_address" => (int) $row_nuevas[id_address],
                "alias"      => utf8_encode("{$row_nuevas[alias]}"),
                "address1"   => utf8_encode("{$row_nuevas[address1]}"),
                "address2"   => utf8_encode("{$row_nuevas[address2]}"),
                "postcode"   => (int) $row_nuevas[postcode],
                "name"       => utf8_encode("{$row_nuevas[name]}"),
                "iso_code"   => utf8_encode("{$row_nuevas[iso_code]}"),
                "city"       => utf8_encode("{$row_nuevas[city]}"),
                "colony"     => utf8_encode("{$row_nuevas[colony]}"),
                "customerID" => "{$row_nuevas[customerID]}",
            );
            $contador_direcciones++;
        }
    }
    echo json_encode($direcciones);
}
function forZipCode($zip_code, $conn)
{

    $estado            = 0;
    $ciudad            = "";
    $ciudades          = [];
    $colonias          = [];
    $contador_colonias = 0;
    $contador_ciudades = 0;

    $sql_estado    = "SELECT id_state FROM codigos_postales_generales WHERE codigo_postal = {$zip_code} GROUP BY id_state";
    $result_estado = $conn->query($sql_estado);
    if ($result_estado->num_rows > 0) {
        while ($row_estado = $result_estado->fetch_assoc()) {
            $estado = $row_estado[id_state];
        }
    }

    $sql_ciudades    = "SELECT ciudad, codigo_postal FROM codigos_postales_generales WHERE id_state = {$estado} GROUP BY ciudad ORDER BY ciudad";
    $result_ciudades = $conn->query($sql_ciudades);
    if ($result_ciudades->num_rows > 0) {
        while ($row_ciudades = $result_ciudades->fetch_assoc()) {
            $ciudades[$contador_ciudades] = utf8_encode("{$row_ciudades[ciudad]}");
            $contador_ciudades++;
        }
    }

    $sql_ciudad    = "SELECT ciudad FROM codigos_postales_generales WHERE codigo_postal = {$zip_code} GROUP BY ciudad ORDER BY ciudad";
    $result_ciudad = $conn->query($sql_ciudad);
    if ($result_ciudad->num_rows > 0) {
        while ($row_ciudad = $result_ciudad->fetch_assoc()) {
            $ciudad = utf8_encode("{$row_ciudad[ciudad]}");
        }
    }

    $sql_colonias    = "SELECT  asentamiento, codigo_postal FROM codigos_postales_generales WHERE codigo_postal = {$zip_code} ORDER BY asentamiento";
    $result_colonias = $conn->query($sql_colonias);
    if ($result_colonias->num_rows > 0) {
        while ($row_colonias = $result_colonias->fetch_assoc()) {
            $colonias[$contador_colonias] = array("colonia" => utf8_encode("{$row_colonias[asentamiento]}"), "codigo_postal" => $row_colonias[codigo_postal]);
            $contador_colonias++;
        }
    }
    $direcciones = array("estado" => (int) $estado,
        "ciudad"                      => $ciudad,
        "ciudades"                    => $ciudades,
        "colonias"                    => $colonias,
    );
    echo json_encode($direcciones);
}
function forState($estado, $conn)
{
    $ciudades          = [];
    $contador_ciudades = 0;
    $sql_ciudades      = "SELECT ciudad FROM codigos_postales_generales WHERE id_state = {$estado} GROUP BY ciudad ORDER BY ciudad";
    $result_ciudades   = $conn->query($sql_ciudades);
    if ($result_ciudades->num_rows > 0) {
        while ($row_ciudades = $result_ciudades->fetch_assoc()) {
            $ciudades[$contador_ciudades] = utf8_encode("{$row_ciudades[ciudad]}");
            $contador_ciudades++;
        }
    }
    $colonias          = [];
    $contador_colonias = 0;
    $city              = utf8_decode($ciudades[0]);
    $sql_colonias      = "SELECT asentamiento, codigo_postal FROM codigos_postales_generales WHERE ciudad = '{$city}' ORDER BY asentamiento";
    $result_colonias   = $conn->query($sql_colonias);
    if ($result_colonias->num_rows > 0) {
        while ($row_colonias = $result_colonias->fetch_assoc()) {
            $colonias[$contador_colonias] = array("colonia" => utf8_encode("{$row_colonias[asentamiento]}"), "codigo_postal" => $row_colonias[codigo_postal]);
            $contador_colonias++;
        }
    }
    $direcciones = array("ciudades" => $ciudades,
        "colonias"                      => $colonias);
    echo json_encode($direcciones);
}

function forCity($city, $conn)
{
    $colonias          = [];
    $contador_colonias = 0;
    $city              = utf8_decode($city);
    $sql_colonias      = "SELECT asentamiento, codigo_postal FROM codigos_postales_generales WHERE ciudad = '{$city}' ORDER BY asentamiento";
    $result_colonias   = $conn->query($sql_colonias);
    if ($result_colonias->num_rows > 0) {
        while ($row_colonias = $result_colonias->fetch_assoc()) {
            $colonias[$contador_colonias] = array("colonia" => utf8_encode("{$row_colonias[asentamiento]}"), "codigo_postal" => $row_colonias[codigo_postal]);
            $contador_colonias++;
        }
    }
    $direcciones = array("colonias" => $colonias);
    echo json_encode($direcciones);
}
