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

include '../token.php';
date_default_timezone_set("America/Chihuahua");
set_time_limit(0);

$servername = "localhost";
$conn       = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dbname_precios   = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
    die("Connection failed: " . $conn_precios->connect_error);
}

$fecha    = date("Y-m-d");
$fechaasd = date("Y-m-d") . "T12:00:00Z";
$listas   = "(";
$elor     = "";
$sql      = "SELECT NombreLista FROM list_price GROUP BY NombreLista ORDER BY NombreLista asc";
$result   = $conn_precios->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nl = "NombreLista%20eq%20'{$row[NombreLista]}'";
        $listas .= "{$elor}{$nl}";
        $elor = "%20or%20";
    }
}
$listas .= ")";
$sql    = "SELECT * FROM list_price WHERE actualizado  < '{$fecha}' GROUP BY ItemRelation LIMIT 1";
$result = $conn_precios->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $token                          = new Token();
        $tokenTemp                      = $token->getToken("ATP", "prod");
        $token                          = $tokenTemp[0]->Token;
        $id_price                       = $row[id_price];
        $CantidadDesde                  = $row[CantidadDesde];
        $CantidadHasta                  = $row[CantidadHasta];
        $NombreLista                    = $row[NombreLista];
        $ItemRelation                   = $row[ItemRelation];
        $OriginalPriceDiscAdmTransRecId = $row[OriginalPriceDiscAdmTransRecId];
        $RecidAYT                       = $row[RecidAYT];
        $POSTFIELDS                     = "{}";
        $filter                         = "%24filter=ItemRelation%20eq%20'{$ItemRelation}'%20and%20{$listas}";
        $order                          = "%24orderby=NombreLista%20asc,CantidadDesde%20asc,CantidadHasta%20asc";
        $url                            = "https://ayt.operations.dynamics.com/Data/AYT_ListaPrecios?{$filter}&{$order}";
        print_r("{$url}<br><br>");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_POSTFIELDS     => $POSTFIELDS,
            CURLOPT_HTTPHEADER     => array(
                "authorization: Bearer {$token}",
                "content-type: application/json",
            ),
        ));

        $responseP = curl_exec($curl);
        //print_r("responseP ::: {$responseP}<br>}<br>");
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response = json_decode($responseP);
            foreach ($response as $key => $resp) {
                if (is_array($resp) && $key == "value") {
                    foreach ($resp as $r) {
                        //"dataAreaId": "atp",
                        $FechaHasta  = $r->FechaHasta;
                        $NombreLista = $r->NombreLista;
                        if (($FechaHasta > '1900-01-01T12:00:00Z' and $FechaHasta < $fechaasd) || $NombreLista == '') {

                        } else {
                            $dataAreaId    = $r->dataAreaId;
                            $ItemRelation  = $r->ItemRelation;
                            $CantidadDesde = $r->CantidadDesde;
                            $NombreLista   = $r->NombreLista;

                            $Percent1      = $r->Percent1;
                            $Percent2      = $r->Percent2;
                            $CantidadHasta = $r->CantidadHasta;
                            $FechaDesde    = $r->FechaDesde;
                            $FechaHasta    = $r->FechaHasta;
                            $Precio        = $r->Precio;
                            $UnidadPrecio  = $r->UnidadPrecio;
                            $Unidad        = $r->Unidad;
                            $Moneda        = $r->Moneda;
                            $ToDate        = $r->ToDate;

                            $sets = "Percent1 = {$Percent1}, ";
                            $sets .= "Percent2 = {$Percent2}, ";
                            $sets .= "CantidadDesde = {$CantidadDesde}, ";
                            $sets .= "CantidadHasta = {$CantidadHasta}, ";
                            $sets .= "FechaDesde = '{$FechaDesde}', ";
                            $sets .= "FechaHasta = '{$FechaHasta}', ";
                            $sets .= "Precio = {$Precio}, ";
                            $sets .= "UnidadPrecio = {$UnidadPrecio}, ";
                            $sets .= "Unidad = '{$Unidad}', ";
                            $sets .= "Moneda = '{$Moneda}', ";
                            $sets .= "ToDate = '{$ToDate}'";
                            $CantidadDesde = (int) $r->CantidadDesde;
                            if ($CantidadDesde > 0) {
                                print_r(" ---------------------------------------------------------------------------------------------------- <br>");
                                print_r(" ---------------------------------------------------------------------------------------------------- <br>");
                            }
                            $sql = "UPDATE list_price SET {$sets} WHERE ItemRelation = '{$ItemRelation}' AND NombreLista = '{$NombreLista}' AND CantidadDesde = {$CantidadDesde}";
                            print_r("sql ::: {$sql}<br>");
                            if ($CantidadDesde > 0) {
                                print_r(" ---------------------------------------------------------------------------------------------------- <br>");
                                print_r(" ---------------------------------------------------------------------------------------------------- <br>");
                            }

                        }
                        print_r("<br><br>");

                        //"RecidAYT": 5637525160,
                        //print_r("RecidAYT ::: {$r->RecidAYT}<br>");
                        //"ItemRelation": "0100-0010-0050",
                        //"CalendarDays": "No",
                        //print_r("CalendarDays ::: {$r->CalendarDays}<br>");
                        //"InventDimId": "AllBlank",
                        //print_r("InventDimId ::: {$r->InventDimId}<br>");
                        //"ModifiedDateTimeAYT": "2021-05-19T17:35:03Z",
                        //print_r("ModifiedDateTimeAYT ::: {$r->ModifiedDateTimeAYT}<br>");
                        //"BuscaSiguiente": "No",
                        //print_r("BuscaSiguiente ::: {$r->BuscaSiguiente}<br>");
                        //"Percent1": 36.86,
                        //"Percent2": 0,
                        //"Unidad": "M",
                        //"DataAreaIdAYT": "atp",
                        //print_r("DataAreaIdAYT ::: {$r->DataAreaIdAYT}<br>");
                        //"ToDate": "1900-01-01T12:00:00Z",
                        //"Precio": 0,
                        //"DeliveryTime": 0,
                        //print_r("DeliveryTime ::: {$r->DeliveryTime}<br>");
                        //"UnidadPrecio": 1,
                        //"CantidadDesde": 45.72,
                        //"FechaDesde": "1900-01-01T12:00:00Z",
                        //"NombreLista": "",
                        //"CantidadHasta": 0,
                        //"OriginalPriceDiscAdmTransRecId": 5638754283,
                        //print_r("OriginalPriceDiscAdmTransRecId ::: {$r->OriginalPriceDiscAdmTransRecId}<br>");
                        //"Articulo": "0100-0010-0050",
                        //print_r("Articulo ::: {$r->Articulo}<br>");
                        //"FechaHasta": "1900-01-01T12:00:00Z",
                        //"SearchAgain": "No",
                        //print_r("SearchAgain ::: {$r->SearchAgain}<br>");
                        //"Moneda": "MXN"

                    }
                }
            }
        }
    }
}
