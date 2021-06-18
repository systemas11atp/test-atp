<?php 
//require_once('/var/www/vhosts/avanceytec.com.mx/httpdocs/logs_locales.php');
//$activeStore = explode("/",$_SERVER['REQUEST_URI'])[1];

$servername = "localhost";
$dbname = "pruebas_shop";
$username = "test_atp";
$password = "_7xpLw81";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}else{
	print_r("PASS 1<br>");
}


$dbname_precios = "lista_precios_full";
$username_precios = "precios_full";
$password_precios = "_7xpLw81";

$di = "prstshp_";
$conn_precios = new mysqli($servername, $username_precios, $password_precios, $dbname_precios);
if ($conn_precios->connect_error) {
	die("Connection failed: " . $conn_precios->connect_error);
}else{
	print_r("PASS 2<br>");
}

$grupos = array(
	'C000002837' => 4,
	'CHIH-D-BS' => 5,
	'CHIH-D-IPP' => 6,
	'CHIH-D-TRA' => 7,
	'CHIH-EN-PP' => 8,
	'CHIH-ENV' => 9,
	'CHIH-ENV-D' => 10,
	'DESC-ESP' => 11,
	'LIDEART-1' => 12,
	'MEXICO-BS' => 13,
	'MEXICO-PR' => 14,
	'SUCS-D-BS' => 15,
	'SUCS-D-PR' => 16,
	'TXLA-D-BS' => 17,
	'TXLA-D-PR' => 18
);
$fecha=date("Y-m-d H:i:s");

$sql = "SELECT * FROM lista_precios_base WHERE id_product > 0 GROUP BY reference, moneda";
$result = $conn_precios->query($sql);
print_r("{$result->num_rows}<br>");
print_r("{$sql}<br>");
$fecha=date("Y-m-d");
$hechelepue=0;
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$id_product =  $row[id_product];
		$id_product_attribute =  $row[id_product_attribute];
		$mxn =  $row[mxn];
		$usd =  $row[usd];
		$moneda =  $row[moneda];
		$reference =  $row[reference];
		
		$sql_select = "SELECT * FROM actualiza_precios_dyn WHERE referencia = '{$reference}' AND moneda = '{$moneda}'";
		//print_r("{$sql}<br>");
		$result_select = $conn_precios->query($sql_select);
		if ($result_select->num_rows == 0) {
			$sql_insert = "INSERT INTO actualiza_precios_dyn VALUES(null,'{$reference}','{$moneda}',{$mxn},{$usd},'0000-00-00')";
			if($conn_precios->query($sql_insert)){
				$hechelepue++;
				print_r("{$hechelepue}) {$sql_insert}:::<br>");
			}else{
				print_r("(NOOOOO!!) {$sql_insert}:::<br>");
			}
		}
		
	}
}
print_r("<br>----------------------------------------------------------------------------------------------------<br><br>");

$sql = "SELECT * FROM lista_precios_base WHERE id_product > 0 GROUP BY id_product, id_product_attribute, reference, moneda";
$result = $conn_precios->query($sql);
print_r("{$result->num_rows}<br>");
print_r("{$sql}<br>");
$fecha=date("Y-m-d");
$hechelepue=0;
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$id_product =  $row[id_product];
		$id_product_attribute =  $row[id_product_attribute];
		$mxn =  $row[mxn];
		$usd =  $row[usd];
		$moneda =  $row[moneda];
		$reference =  $row[reference];
		
		$sql_select = "SELECT * FROM actualiza_precios_ps WHERE id_product = {$id_product} AND id_product_attribute = {$id_product_attribute} AND moneda = '{$moneda}'";
		//print_r("{$sql_select}<br>");
		$result_select = $conn_precios->query($sql_select);
		if ($result_select->num_rows == 0) {
			$sql_insert = "INSERT INTO actualiza_precios_ps VALUES(null,'{$reference}','{$moneda}',{$mxn},{$usd},'0000-00-00',{$id_product},{$id_product_attribute})";
			if($conn_precios->query($sql_insert)){
				$hechelepue++;
				print_r("{$hechelepue}) {$sql_insert}:::<br>");
			}else{
				print_r("(NOOOOO!!) {$sql_insert}:::<br>");
			}
		}
		
	}
}

?>