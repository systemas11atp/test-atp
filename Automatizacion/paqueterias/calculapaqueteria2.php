<?php

include_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/classes/Cookie.php';

require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/logs_locales.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/config.inc.php';
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/config/settings.inc.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];
$db_index  = _DB_PREFIX_;

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$content     = trim(file_get_contents("php://input"));
$decodedT    = json_decode($content, true);

$servername = "localhost";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    $error = "ERROR FATAL";
    die("Connection failed: " . $conn->connect_error);
}

$cart = $decodedT['cartId'];
if ($cart == "") {
    $cart = 250;
}
$sql_delete = "DELETE FROM prstshp_cart_carriers WHERE id_cart = {$cart}";
if ($conn->query($sql_delete)) {
    capuraLogs::nuevo_log("calculapaqueteria borre  ::: {$sql_delete}");
} else {
    capuraLogs::nuevo_log("calculapaqueteria No borre  ::: {$sql_delete}");
}

$sql = "SELECT * FROM prstshp_cart_product pcp ";
$sql .= " INNER JOIN prstshp_stock_available psa ON psa.id_product = pcp.id_product ";
$sql .= " AND psa.id_product_attribute = pcp.id_product_attribute AND psa.reference IS NOT NULL ";
$sql .= " WHERE pcp.id_cart = {$cart} AND (psa.categoria='' or psa.categoria is null)";
$referenciasCategorias = "";
$result                = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productReference = $row[reference];
        $referenciasCategorias .= "<tr><td>{$productReference}</td></tr>";
    }
}

//capuraLogs::nuevo_log("calculapaqueteria referenciasCategorias ::: {$referenciasCategorias}");
if ($referenciasCategorias != "") {

    $html = "<table>";
    $html .= "<tr>";
    $html .= "<td>";
    $html .= "Los siguientes productos no cuentan con categorias, por lo cual no se calcula la paqueteria. ";
    $html .= "</td>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<td>";
    $html .= "Favor de completar la información para futuras compras de clientes.";
    $html .= "</td>";
    $html .= "</tr>";
    $html .= "{$referenciasCategorias}";
    $html .= "</table>";
    //Envio correo sin categoria
    /*
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Debugoutput = 'html';
    $mail->Host = 'ssl://smtp.gmail.com';
    $mail->Port = '465';
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPAuth = true;
    $mail->Username = "notificacionatp@avanceytec.com.mx";
    $mail->Password = "ub*M[2y[Yv";
    $mail->FromName = "Avance y Tecnologia en Plasticos";
    $mail->addAddress("sistemas10@avanceytec.com.mx");
    if($activeStore == 'storejorge'){
    $mail->Subject = utf8_decode("Productos sin categoria. (Pruebas) Carrito:  {$cart}");
    }else{
    $mail->addAddress("gerentecedis@avanceytec.com.mx");
    $mail->addAddress("sistemas12@avanceytec.com.mx");
    $mail->addAddress("gerenteventas@avanceytec.com.mx");
    $mail->Subject = utf8_decode("Avance y Tecnología en Plásticos - Solicitud de pedido. Carrito: {$cart}");
    }
    $mail->msgHTML($html);
    $mail->send();
     */
    $mensaje = array(
        'sumatoriaPrcios' => 0,
    );
    echo json_encode($mensaje, JSON_HEX_QUOT | JSON_HEX_TAG);
    exit();
}

$addressId = $decodedT['addressId'];

$sql = "SELECT  pa.colony,pa.postcode,pa.city,sc.colonia,sc.codigo_postal,sc.nombre,sc.calle,psp.sitio";
$sql .= " FROM prstshp_cart_product psp";
$sql .= " INNER JOIN prstshp_address pa on pa.id_address =psp.id_address_delivery";
$sql .= " INNER JOIN sucursales sc on psp.sitio=sc.sitio  WHERE psp.id_cart ={$cart}";
$sql .= " group by pa.colony,pa.postcode,pa.city,sc.colonia,sc.codigo_postal,sc.nombre,sc.calle,psp.sitio";
//capuraLogs::nuevo_log("calculapaqueteria sql ::: {$sql}");
$resultSucursales = $conn->query($sql);
$num_rows         = $resultSucursales->num_rows;

$mensaje = array(
    'sql'              => $sql,
    'resultSucursales' => $resultSucursales,
    'num_rows'         => $num_rows,
);

$refi             = array("C" => array(), "I" => array(), "IB" => array(), "IB-1" => array(), "IB-10" => array(), "IB-12" => array(), "IB-14" => array(), "IB-15" => array(), "IB-2" => array(), "IB-25" => array(), "IB-3" => array(), "IB-30" => array(), "IB-4" => array(), "IB-5" => array(), "IB-50" => array(), "IB-6" => array(), "IB-8" => array(), "T" => array(), "V" => array());
$contadorArr      = array("C" => 0, "I" => 0, "IB" => 0, "IB-1" => 0, "IB-10" => 0, "IB-12" => 0, "IB-14" => 0, "IB-15" => 0, "IB-2" => 0, "IB-25" => 0, "IB-3" => 0, "IB-30" => 0, "IB-4" => 0, "IB-5" => 0, "IB-50" => 0, "IB-6" => 0, "IB-8" => 0, "T" => 0, "V" => 0);
$numrows          = $result->num_rows;
$contadorSucursal = 0;
$sumatoriaPrecios = 0;
foreach ($resultSucursales as $item) {
    $var  = $item['colony'];
    $var1 = $item['postcode'];
    $var2 = $item['city'];
    $var3 = $item['colonia'];
    $var4 = $item['codigo_postal'];
    $var5 = $item['nombre'];
    $var6 = $item['calle'];
    $var7 = $item['sitio'];

    $iva = 1.16;

    $coloniaDeEntrega     = $item['colony'];
    $codigoPostalEntrega  = $item['postcode'];
    $coloniaSucursal      = $item['colonia'];
    $codigoPostalSucursal = $item['codigo_postal'];
    $sitioSucursal        = $item['sitio'];

    $sqlProductosSucursal = "SELECT t1.id_product, t1.id_product_attribute, t1.quantity, t2.width, t2.height, ";
    $sqlProductosSucursal .= "t2.depth, t2.categoria, t2.volumen, t2.pies, t2.reference, t2.metros, t2.imultiply , t2.weight, (t1.quantity/t2.imultiply) as multiplicador, t2.unidadVenta ";
    $sqlProductosSucursal .= "FROM prstshp_cart_product t1 ";
    $sqlProductosSucursal .= "INNER JOIN prstshp_stock_available t2 ON t2.id_product = t1.id_product AND t2.id_product_attribute = t1.id_product_attribute ";
    $sqlProductosSucursal .= "WHERE t1.id_cart = {$cart} AND t2.categoria IS NOT NULL AND t1.Sitio='{$sitioSucursal}' ORDER BY t2.categoria, t2.reference";
    capuraLogs::nuevo_log("calculapaqueteria sqlProductosSucursal ::: {$sqlProductosSucursal}");
    $resultArticulos = $conn->query($sqlProductosSucursal);

    $cont          = 0;
    $paquetesCatI  = [];
    $paquetesCatIB = [];
    if ($resultArticulos->num_rows > 0) {
        $cont                      = 1;
        $contadorSucursalCategoria = -1;
        $categoria                 = "categoria";
        $lineasSucursal            = [];

        $totalPaquetesTramos = 0;
        $tramos10            = 0;
        $tramos8             = 0;
        $tramos6             = 0;
        $tramos5             = 0;
        $tramos3             = 0;
        $tramos1             = 0;
        while ($rowe = $resultArticulos->fetch_assoc()) {
            $id_product            = $rowe['id_product'];
            $id_product_attribute  = $rowe['id_product_attribute'];
            $quantity              = $rowe['quantity'];
            $width                 = $rowe['width'];
            $height                = $rowe['height'];
            $depth                 = $rowe['depth'];
            $reference             = $rowe['reference'];
            $multiplicador         = $rowe['multiplicador'];
            $volumen               = $rowe['volumen'];
            $multiplicacionVolumen = (($depth * $multiplicador) * $height * $width);
            $pies                  = $rowe['pies'];
            $metros                = $rowe['metros'];
            $imultiply             = $rowe['imultiply'];
            $weight                = $rowe['weight'];
            $unidadVenta           = $rowe['unidadVenta'];
            if ($categoria != $rowe['categoria']) {
                $contadorSucursalCategoria++;
                $categoria         = $rowe['categoria'];
                $pesoCategoria     = 0;
                $pesosCategoria    = "";
                $volumenCategoria  = 0;
                $cantidadCategoria = 0;
                if ($categoria == "C") {
                    $lineasSucursal[$categoria]['sequence'] = $categoria;
                    $pesoCategoria += ($rowe['weight'] * $rowe['multiplicador']);
                    $pesosCategoria .= $rowe['weight'] . ", ";
                    $volumenCategoria += ((($depth * $multiplicador) * $height * $width) / 1000000);
                    $cantidadCategoria += $rowe['multiplicador'];
                    $lineasSucursal[$categoria]['peso']     = $pesoCategoria;
                    $lineasSucursal[$categoria]['volumen']  = $volumenCategoria;
                    $lineasSucursal[$categoria]['cantidad'] = $cantidadCategoria;
                } else if ($categoria == "I") {
                    $lineasSucursal[$categoria]['sequence'] = $categoria;
                    $pesoCategoria                          = ($rowe['weight'] * $rowe['multiplicador']);
                    $pesosCategoria                         = $rowe['weight'] . ", ";
                    $volumenCategoria                       = ((($depth * $multiplicador) * $height * $width) / 1000000);
                    $cantidadCategoria                      = $rowe['multiplicador'];
                    $a                                      = array('peso' => $pesoCategoria, 'volumen' => $volumenCategoria, 'cantidad' => $cantidadCategoria, 'reference' => $reference);
                    array_push($paquetesCatI, $a);
                } else if ($categoria == "IB" || $categoria == "IB-1" || $categoria == "IB-10" || $categoria == "IB-12" || $categoria == "IB-14" || $categoria == "IB-15" || $categoria == "IB-2" || $categoria == "IB-25" || $categoria == "IB-3" || $categoria == "IB-30" || $categoria == "IB-4" || $categoria == "IB-5" || $categoria == "IB-50" || $categoria == "IB-6" || $categoria == "IB-8") {
                    $lineasSucursal["IB"]['sequence'] = $categoria;
                    $pesoCategoria                    = ($rowe['weight'] * $rowe['multiplicador']);
                    $pesosCategoria                   = $rowe['weight'] . ", ";
                    $volumenCategoria                 = ((($depth * $multiplicador) * $height * $width) / 1000000);
                    if ($volumenCategoria < 0.0001) {
                        $volumenCategoria = 0.0001;
                    }
                    $cantidadCategoria = $rowe['multiplicador'];
                    if ($categoria == "IB") {
                        $totalAmarre = "2";
                    } else {
                        $totalAmarre = explode("-", $rowe['categoria'])[1];
                    }
                    $a = array('categoria' => $categoria, 'peso' => $pesoCategoria, 'volumen' => $volumenCategoria, 'cantidad' => $cantidadCategoria, 'reference' => $reference, 'amarre' => (int) $totalAmarre);
                    if (count($paquetesCatIB[$categoria]) == 0) {
                        $paquetesCatIB[$categoria] = [];
                    }
                    array_push($paquetesCatIB[$categoria], $a);
                } else if ($categoria == "T") {
                    $lineasSucursal[$categoria]['sequence'] = $categoria;
                    $pesoCategoria += ($rowe['weight'] * $rowe['multiplicador']);
                    //capuraLogs::nuevo_log("Carrier 1) pesoCategoria : -{$pesoCategoria}-");
                    //capuraLogs::nuevo_log("Carrier 2) weight : -{$rowe['weight']}-");
                    //capuraLogs::nuevo_log("Carrier 3) multiplicador : -{$rowe['multiplicador']}-");
                    $pesosCategoria .= $rowe['weight'] . ", ";
                    $volumenCategoria += ((($depth * $multiplicador) * $height * $width) / 1000000);
                    $cantidadCategoria += $rowe['multiplicador'];
                    $lineasSucursal[$categoria]['peso']     = $pesoCategoria;
                    $lineasSucursal[$categoria]['volumen']  = $volumenCategoria;
                    $lineasSucursal[$categoria]['depth']    = $depth;
                    $lineasSucursal[$categoria]['height']   = $height;
                    $lineasSucursal[$categoria][width]      = $width;
                    $lineasSucursal[$categoria]['cantidad'] = $cantidadCategoria;
                } else if ($categoria == "V") {
                    $cantidadCategoria = $rowe['quantity'];
                    if ($unidadVenta == "M") {
                        $cantidadCategoria = $cantidadCategoria * 3.2808;
                    }
                    $multiplequele = ($quantity / $imultiply);
                    if ($cantidadCategoria <= 10) {
                        $tramos10++;
                    } else if ($cantidadCategoria <= 15) {
                        $tramos8++;
                    } else if ($cantidadCategoria <= 20) {
                        $tramos6++;
                    } else if ($cantidadCategoria <= 30) {
                        $tramos5++;
                    } else if ($cantidadCategoria <= 35) {
                        $tramos3++;
                    } else {
                        $separados = (int) ($quantity / $imultiply);
                        if ((($quantity / $imultiply) - $separados) > 0) {
                            $separados += 1;
                        }
                        $tramos1 += $separados;
                        $multiplequele = $separados;
                    }
                    $lineasSucursal[$categoria]['sequence'] = $categoria;

                    $pesoCategoria += ($rowe['weight'] * $multiplequele);

                    $volumenCategoria += ((($depth * $multiplequele) * $height * $width) / 1000000);

                    $lineasSucursal[$categoria]['peso']     = $pesoCategoria;
                    $lineasSucursal[$categoria]['volumen']  = $volumenCategoria;
                    $lineasSucursal[$categoria]['cantidad'] = $cantidadCategoria;

                }
            } else {
                if ($categoria == "C") {
                    $lineasSucursal[$categoria]['sequence'] = $categoria;
                    $pesoCategoria += ($rowe['weight'] * $rowe['multiplicador']);
                    $pesosCategoria .= $rowe['weight'] . ", ";
                    $volumenCategoria += ((($depth * $multiplicador) * $height * $width) / 1000000);
                    $cantidadCategoria += $rowe['multiplicador'];
                    $lineasSucursal[$categoria]['peso']     = $pesoCategoria;
                    $lineasSucursal[$categoria]['volumen']  = $volumenCategoria;
                    $lineasSucursal[$categoria]['cantidad'] = $cantidadCategoria;
                } else if ($categoria == "I") {
                    $lineasSucursal[$categoria]['sequence'] = $categoria;
                    $pesoCategoria                          = ($rowe['weight'] * $rowe['multiplicador']);
                    $pesosCategoria                         = $rowe['weight'] . ", ";
                    $volumenCategoria                       = ((($depth * $multiplicador) * $height * $width) / 1000000);
                    if ($volumenCategoria < 0.0001) {
                        $volumenCategoria = 0.0001;
                    }
                    $cantidadCategoria = $rowe['multiplicador'];
                    $a                 = array('categoria' => $categoria, 'peso' => $pesoCategoria, 'volumen' => $volumenCategoria, 'cantidad' => $cantidadCategoria, 'reference' => $reference);
                    array_push($paquetesCatI, $a);
                } else if ($categoria == "IB" || $categoria == "IB-1" || $categoria == "IB-10" || $categoria == "IB-12" || $categoria == "IB-14" || $categoria == "IB-15" || $categoria == "IB-2" || $categoria == "IB-25" || $categoria == "IB-3" || $categoria == "IB-30" || $categoria == "IB-4" || $categoria == "IB-5" || $categoria == "IB-50" || $categoria == "IB-6" || $categoria == "IB-8") {
                    $lineasSucursal["IB"]['sequence'] = $categoria;
                    $pesoCategoria                    = ($rowe['weight'] * $rowe['multiplicador']);
                    $pesosCategoria                   = $rowe['weight'] . ", ";
                    $volumenCategoria                 = ((($depth * $multiplicador) * $height * $width) / 1000000);
                    if ($volumenCategoria < 0.0001) {
                        $volumenCategoria = 0.0001;
                    }
                    $cantidadCategoria = $rowe['multiplicador'];
                    if ($categoria == "IB") {
                        $totalAmarre = "2";
                    } else {
                        $totalAmarre = explode("-", $rowe['categoria'])[1];
                    }
                    $a = array('peso' => $pesoCategoria, 'volumen' => $volumenCategoria, 'cantidad' => $cantidadCategoria, 'reference' => $reference, 'amarre' => (int) $totalAmarre);
                    if (count($paquetesCatIB[$categoria]) == 0) {
                        $paquetesCatIB[$categoria] = [];
                    }
                    array_push($paquetesCatIB[$categoria], $a);
                } else if ($categoria == "T") {
                    $lineasSucursal[$categoria]['sequence'] = $categoria;
                    $pesoCategoria += ($rowe['weight'] * $rowe['multiplicador']);
                    //capuraLogs::nuevo_log("Carrier 4) pesoCategoria : -{$pesoCategoria}-");
                    //capuraLogs::nuevo_log("Carrier 5) weight : -{$rowe['weight']}-");
                    //capuraLogs::nuevo_log("Carrier 6) multiplicador : -{$rowe['multiplicador']}-");
                    $pesosCategoria .= $rowe['weight'] . ", ";
                    $volumenCategoria += ((($depth * $multiplicador) * $height * $width) / 1000000);
                    $cantidadCategoria += $rowe['multiplicador'];
                    $lineasSucursal[$categoria]['peso']     = $pesoCategoria;
                    $lineasSucursal[$categoria]['volumen']  = $volumenCategoria;
                    $lineasSucursal[$categoria]['depth']    = $depth;
                    $lineasSucursal[$categoria]['height']   = $height;
                    $lineasSucursal[$categoria][width]      = $width;
                    $lineasSucursal[$categoria]['cantidad'] = $cantidadCategoria;
                } else if ($categoria == "V") {
                    $cantidadCategoria = $rowe['quantity'];
                    if ($unidadVenta == "M") {
                        $cantidadCategoria = $cantidadCategoria * 3.2808;
                    }
                    $multiplequele = ($quantity / $imultiply);
                    if ($cantidadCategoria <= 10) {
                        $tramos10++;
                    } else if ($cantidadCategoria <= 15) {
                        $tramos8++;
                    } else if ($cantidadCategoria <= 20) {
                        $tramos6++;
                    } else if ($cantidadCategoria <= 30) {
                        $tramos5++;
                    } else if ($cantidadCategoria <= 35) {
                        $tramos3++;
                    } else {
                        $separados = (int) ($quantity / $imultiply);
                        if ((($quantity / $imultiply) - $separados) > 0) {
                            $separados += 1;
                        }
                        $multiplequele = $separados;
                        $tramos1 += $separados;
                    }
                    $lineasSucursal[$categoria]['sequence'] = $categoria;

                    $pesoCategoria += ($rowe['weight'] * $multiplequele);

                    $volumenCategoria += ((($depth * $multiplequele) * $height * $width) / 1000000);

                    $lineasSucursal[$categoria]['volumen']  = $volumenCategoria;
                    $lineasSucursal[$categoria]['peso']     = $pesoCategoria;
                    $lineasSucursal[$categoria]['cantidad'] = $cantidadCategoria;
                }
            }
            $refi[$rowe['categoria']][$contadorArr[$rowe['categoria']]++] = $rowe;
            $id_product                                                   = $rowe['id_product'];
        }
        $totalPaquetesTramos += $tramos1;
        if ($tramos3 > 0) {
            $totalPaquetesTramos += (int) ($tramos3 / 3) + 1;
        }
        if ($tramos10 > 0) {
            $totalPaquetesTramos += (int) ($tramos10 / 10) + 1;
        }
        if ($tramos8 > 0) {
            $totalPaquetesTramos += (int) ($tramos8 / 8) + 1;
        }
        if ($tramos6 > 0) {
            $totalPaquetesTramos += (int) ($tramos6 / 6) + 1;
        }
        if ($tramos5 > 0) {
            $totalPaquetesTramos += (int) ($tramos5 / 5) + 1;
        }
        $secuencia   = 1;
        $lineasFinal = array();
        $texto       = "";
        $totalTres   = 0;
        $pesoD       = 0;
        $volumenD    = 0;
        if ($lineasSucursal[I]) {
            $lineasSucursal[I]['arreglo'] = $paquetesCatI;
        }
        if ($lineasSucursal[IB]) {
            $lineasSucursal[IB]['arreglo'] = $paquetesCatIB;
        }
        $nGuiasTotal    = 0;
        $tarima_totales = 0;
        foreach ($lineasSucursal as $lineas) {
            $nGuiasTotal = 0;
            $categoria   = $lineas['sequence'];
            $cantidad    = $lineas['cantidad'];
            if ($categoria == "C") {
                $cantidad        = ceil($lineas['peso'] / 11);
                $cantidadVolumen = ceil($lineas['volumen'] / 0.125);
                if ($cantidad < $cantidadVolumen) {
                    $cantidad = $cantidadVolumen;
                }
                $lineasFinal[$secuencia - 1]['highShip'] = 1;
                $lineasFinal[$secuencia - 1]['longShip'] = 1;
                $lineasFinal[$secuencia - 1]['quantity'] = $cantidad;
                $nGuiasTotal += $cantidad;
                $lineasFinal[$secuencia - 1]['sequence']  = $secuencia;
                $lineasFinal[$secuencia - 1]['categoria'] = $categoria;
                $lineasFinal[$secuencia - 1]['shpCode']   = "2";
                $lineasFinal[$secuencia - 1][volume]      = 0.074;
                $lineasFinal[$secuencia - 1]['weight']    = 11;
                $lineasFinal[$secuencia - 1][widthShip]   = 1;
                $secuencia++;
            } else if ($categoria == "T") {
                $volumen                                 = $lineas['volumen'];
                $peso                                    = $lineas['peso'];
                $lineasFinal[$secuencia - 1]['highShip'] = 1;
                $lineasFinal[$secuencia - 1]['longShip'] = 1;
                $lineasFinal[$secuencia - 1]['quantity'] = 1;
                $nGuiasTotal++;
                $lineasFinal[$secuencia - 1]['sequence']  = $secuencia;
                $lineasFinal[$secuencia - 1]['categoria'] = $categoria;
                $lineasFinal[$secuencia - 1]['shpCode']   = "2";
                $tarima_totales                           = (int) ($peso / 1200) + 1;
                if ($peso >= 60) {
                }
                $tarima_vol = $lineas[width] * $lineas['height'];
                if ($lineas['depth'] > 4) {
                    $tarima_vol = $lineas[width] * $lineas['depth'];
                }
                $tarima_vol                             = ($tarima_vol * 18) / 1000000;
                $lineasFinal[$secuencia - 1][volume]    = $volumen + ($tarima_totales * $tarima_vol);
                $lineasFinal[$secuencia - 1]['weight']  = $peso + ($tarima_totales * 30);
                $lineasFinal[$secuencia - 1][widthShip] = 1;

                $secuencia++;
            } else if ($categoria == "I") {
                foreach ($lineas[arreglo] as $lineasArreglo) {
                    $volumen                                 = $lineasArreglo['volumen'];
                    $peso                                    = $lineasArreglo['peso'];
                    $qty                                     = ceil($lineasArreglo['cantidad']);
                    $lineasFinal[$secuencia - 1]['highShip'] = 1;
                    $lineasFinal[$secuencia - 1]['longShip'] = 1;
                    $lineasFinal[$secuencia - 1]['quantity'] = 1;
                    $nGuiasTotal++;
                    $lineasFinal[$secuencia - 1]['sequence']  = $secuencia;
                    $lineasFinal[$secuencia - 1]['categoria'] = $categoria;
                    $lineasFinal[$secuencia - 1]['shpCode']   = "2";
                    $lineasFinal[$secuencia - 1][volume]      = $volumen;
                    $lineasFinal[$secuencia - 1]['weight']    = $peso;
                    $lineasFinal[$secuencia - 1][widthShip]   = 1;
                    $secuencia++;
                }
            } else if ($categoria == "V") {
                $volumen                                 = $lineas['volumen'];
                $peso                                    = $lineas['peso'];
                $lineasFinal[$secuencia - 1]['highShip'] = 1;
                $lineasFinal[$secuencia - 1]['longShip'] = 1;
                $lineasFinal[$secuencia - 1]['quantity'] = $totalPaquetesTramos;
                $nGuiasTotal += $totalPaquetesTramos;
                $lineasFinal[$secuencia - 1]['sequence']  = $secuencia;
                $lineasFinal[$secuencia - 1]['categoria'] = $categoria;
                $lineasFinal[$secuencia - 1]['shpCode']   = "2";
                $lineasFinal[$secuencia - 1][volume]      = $lineas['volumen'] / $totalPaquetesTramos;
                $lineasFinal[$secuencia - 1]['weight']    = $lineas['peso'] / $totalPaquetesTramos;
                $lineasFinal[$secuencia - 1][widthShip]   = 1;
                $secuencia++;
            } else if ($categoria == "IB" || $categoria == "IB-1" || $categoria == "IB-10" || $categoria == "IB-12" || $categoria == "IB-14" || $categoria == "IB-15" || $categoria == "IB-2" || $categoria == "IB-25" || $categoria == "IB-3" || $categoria == "IB-30" || $categoria == "IB-4" || $categoria == "IB-5" || $categoria == "IB-50" || $categoria == "IB-6" || $categoria == "IB-8") {
                $cuentaIB = 0;
                foreach ($lineas[arreglo] as $lineasArreglo) {
                    foreach ($lineasArreglo as $interno) {
                        $qty  = ceil($interno['cantidad'] / $interno[amarre]);
                        $qty2 = $interno['cantidad'] / $interno[amarre];
                        if ($qty > $qty2 && $qty2 >= 1) {
                            $qty                                     = ceil($interno['cantidad'] / $interno[amarre]);
                            $volumen                                 = $interno['volumen'];
                            $peso                                    = ($interno['peso'] / $interno['cantidad']) * $interno[amarre];
                            $interno['peso']                         = $interno['peso'] - ($qty * $peso);
                            $interno['cantidad']                     = $interno['cantidad'] - ($qty * $interno[amarre]);
                            $lineasFinal[$secuencia - 1]['highShip'] = 1;
                            $lineasFinal[$secuencia - 1]['longShip'] = 1;
                            $lineasFinal[$secuencia - 1]['quantity'] = $qty;
                            $nGuiasTotal += $qty;
                            $lineasFinal[$secuencia - 1]['sequence']  = $secuencia;
                            $lineasFinal[$secuencia - 1]['categoria'] = $interno['categoria'];
                            $lineasFinal[$secuencia - 1]['shpCode']   = "2";
                            $lineasFinal[$secuencia - 1][volume]      = $volumen;
                            $lineasFinal[$secuencia - 1]['weight']    = $peso;
                            $lineasFinal[$secuencia - 1][widthShip]   = 1;
                            $secuencia++;
                        }
                        $qty = ceil($interno['cantidad'] / $interno[amarre]);
                        if ($qty > 0) {
                            $volumen                                 = $interno['volumen'];
                            $peso                                    = $interno['peso'];
                            $lineasFinal[$secuencia - 1]['highShip'] = 1;
                            $lineasFinal[$secuencia - 1]['longShip'] = 1;
                            $lineasFinal[$secuencia - 1]['quantity'] = $qty;
                            $nGuiasTotal += $qty;
                            $lineasFinal[$secuencia - 1]['sequence']  = $secuencia;
                            $lineasFinal[$secuencia - 1]['categoria'] = $interno['categoria'];
                            $lineasFinal[$secuencia - 1]['shpCode']   = "2";
                            $lineasFinal[$secuencia - 1][volume]      = $volumen;
                            $lineasFinal[$secuencia - 1]['weight']    = $peso;
                            $lineasFinal[$secuencia - 1][widthShip]   = 1;
                            $secuencia++;
                        }

                    }
                }
            }

        }
        $totalSucursal       = 0;
        $totalSucursalSinIva = 0;
        foreach ($lineasFinal as $miLinea) {
            $nlinea  = $miLinea;
            $miLinea = json_encode($miLinea);
            $cadena  = '{ ';
            $cadena .= '"header": { ';
            $cadena .= '"security": { ';
            $cadena .= '"user": "365790", ';
            $cadena .= '"password": "1234", ';
            $cadena .= '"type": 1, ';
            $cadena .= '"token": "B62442B6250A05C2E053C0A80A14B4C8" ';
            $cadena .= '}, ';
            $cadena .= '"device": { ';
            $cadena .= '"appName": "Customer", ';
            $cadena .= '"type": "Web", ';
            $cadena .= '"ip": "", ';
            $cadena .= '"idDevice": "" ';
            $cadena .= '}, ';
            $cadena .= '"target": { ';
            $cadena .= '"module": "QUOTER", ';
            $cadena .= '"version": "1.0", ';
            $cadena .= '"service": "quoter", ';
            $cadena .= '"uri": "quotes", ';
            $cadena .= '"event": "R" ';
            $cadena .= '}, ';
            $cadena .= '"output": "JSON", ';
            $cadena .= '"language": null ';
            $cadena .= '}, ';
            $cadena .= '"body": { ';
            $cadena .= '"request": { ';
            $cadena .= '"data": { ';
            $cadena .= '"clientAddrOrig": { ';
            $cadena .= '"zipCode": "' . $codigoPostalSucursal . '", ';
            $cadena .= '"colonyName": "' . $coloniaSucursal . '" ';
            $cadena .= '}, ';
            $cadena .= '"clientAddrDest": { ';
            $cadena .= '"zipCode": "' . $codigoPostalEntrega . '", ';
            $cadena .= '"colonyName": "' . $coloniaDeEntrega . '"';
            $cadena .= '}, ';
            $cadena .= '"services": { ';
            $cadena .= '"dlvyType": "1", ';
            $cadena .= '"ackType": "N", ';
            $cadena .= '"totlDeclVlue": 0, ';
            $cadena .= '"invType": "A", ';
            $cadena .= '"radType": "1" ';
            $cadena .= '}, ';
            $cadena .= '"otherServices": { ';
            $cadena .= '"otherServices": [] ';
            $cadena .= '}, ';
            $cadena .= '"shipmentDetail": { ';
            $cadena .= '"shipments": ' . $miLinea . ' ';
            $cadena .= '} ';
            $cadena .= '}, ';
            $cadena .= '"objectDTO": null ';
            $cadena .= '}, ';
            $cadena .= '"response": null ';
            $cadena .= '} ';
            $cadena .= '}';
            //capuraLogs::nuevo_log("calculapaqueteria cadena ::: {$cadena}");
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_PORT           => "",
                CURLOPT_URL            => "https://cc.paquetexpress.com.mx/WsQuotePaquetexpress/api/apiQuoter/v2/getQuotation",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => "POST",
                CURLOPT_POSTFIELDS     => $cadena,
                CURLOPT_HTTPHEADER     => array(
                    "content-type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err      = curl_error($curl);
            curl_close($curl);
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            //capuraLogs::nuevo_log("calculapaqueteria response ::: {$response}");
            $response     = json_decode($response);
            $totalAmnt    = round($response->body->response->data->amount->totalAmnt * 1.1, 2);
            $subTotalAmnt = round($totalAmnt / $iva, 2);

            $totalSucursal += $totalAmnt;
            $sumatoriaPrecios += $totalAmnt;

            $totalSucursalSinIva += $subTotalAmnt;

            $a1  = $cart;
            $a2  = $sitioSucursal;
            $a3  = $nlinea['highShip'];
            $a4  = $nlinea['longShip'];
            $a5  = $nlinea['quantity'];
            $a6  = $nlinea['sequence'];
            $a7  = $nlinea['categoria'];
            $a8  = $nlinea['shpCode'];
            $a9  = $nlinea['volume'];
            $a10 = $nlinea['weight'];
            $a11 = $nlinea['widthShip'];
            $a12 = $totalAmnt;
            $a13 = 0;
            if ($a7 == "T") {
                $a13 = $tarima_totales;
            }
            $sql_delete = "DELETE FROM prstshp_cart_carriers WHERE id_cart = {$a1} AND sitio = '{$a2}' AND categoria = '{$a7}'";
            if ($conn->query($sql_delete)) {
                capuraLogs::nuevo_log("calculapaqueteria borre  ::: {$sql_delete}");
            } else {
                capuraLogs::nuevo_log("calculapaqueteria No borre  ::: {$sql_delete}");
            }
            $sql = "INSERT INTO prstshp_cart_carriers VALUES ({$a1},'{$a2}',{$a3},{$a4},{$a5},{$a6},'{$a7}','{$a8}',{$a9},{$a10},{$a11},{$a12},{$a13})";
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            //capuraLogs::nuevo_log(" ----------------------------------- ");
            if ($conn->query($sql)) {
                capuraLogs::nuevo_log("calculapaqueteria INSERTE sql ::: {$sql}");
            } else {
                capuraLogs::nuevo_log("calculapaqueteria NO INSERTE sql ::: {$sql}");
            }

        }
        $arrySucursales[$contadorSucursal][$sitioSucursal]      = $lineasSucursal;
        $arrySucursales[$contadorSucursal][precio]              = $totalSucursal;
        $arrySucursalesFinal[$contadorSucursal][$sitioSucursal] = $lineasFinal;
        $arrySucursalesFinal[$contadorSucursal][precio]         = $totalSucursal;

        $contadorSucursal++;
    }
}
$numrows = $result->num_rows;

//capuraLogs::nuevo_log("Carrier delivery_options : -"json_encode($delivery_options)"-");
$mensaje = array(
    'cartId'                    => $cart,
    'contadorArr'               => $contadorArr,
    'coloniaDeEntrega'          => $coloniaDeEntrega,
    'codigoPostalEntrega'       => $codigoPostalEntrega,
    'coloniaSucursal'           => $coloniaSucursal,
    'codigoPostalSucursal'      => $codigoPostalSucursal,
    'sitioSucursal'             => $sitioSucursal,
    'resultArticulos'           => $resultArticulos,
    'lineasx'                   => $lineasx,
    'lineasSucursal'            => $lineasSucursal,
    'arrySucursales'            => $arrySucursales,
    'contadorSucursalCategoria' => $contadorSucursalCategoria,
    'cantidadCategoria'         => $cantidadCategoria,
    'lineasFinal'               => $lineasFinal,
    'arrySucursalesFinal'       => $arrySucursalesFinal,
    'cadena'                    => $cadena,
    'sumatoriaPrcios'           => $sumatoriaPrecios,
);
echo json_encode($mensaje, JSON_HEX_QUOT | JSON_HEX_TAG);
exit();
