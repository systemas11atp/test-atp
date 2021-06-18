<?php
set_time_limit(6000000);
$paginas = 5;
$limite = 250; //max 250
$cuentaTodo = 0;
/*
db = shopify
user = admin_shopify
pass = oof@deek1LUFT!prex
*/
$dbname = "shopify";
$username = "admin_shopify";
$password = "oof@deek1LUFT!prex";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
$sql_total = "SELECT COUNT(*) as total FROM customers";
$result = $conn->query($sql_total);
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$total = $row[total];
	}
}
print_r("total: {$total}<br>");
$ntot = $total/$limite;
$ntot2 = (int)($total/$limite);
if($ntot2 < $ntot){
	$ntot2 = $ntot2+1;
}
print_r("ntot: {$ntot}<br>");
print_r("ntot2: {$ntot2}<br>");
$ntot = $ntot2/$paginas;
$ntot2 = (int)($ntot2/$paginas);
if($ntot2 < $ntot){
	$ntot2 = $ntot2+1;
}
print_r("ntot: {$ntot}<br>");
print_r("ntot2: {$ntot2}<br>");
for ($j=0; $j<$paginas; $j++) {
	$curl = curl_init();
	$pagina = $j+01+($paginas * $ntot2);
	$texto = "https://lideart.myshopify.com/admin/customers.json?limit={$limite}&page={$pagina}";
	print_r("<br>texto = {$texto}<br>");
	curl_setopt_array($curl, array(
		CURLOPT_URL => $texto,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_POSTFIELDS => "",
		CURLOPT_COOKIE => "_secure_admin_session_id=769c28b11b04575bf82b5bf4bfc56f9e",
		CURLOPT_HTTPHEADER => array(
			"authorization: Basic NzQ3NjU1YmQ3ZjliY2ExOTMwMTViYTE5OWMyMWQ5ODY6NDhjMDZlODljOGUwMGUwZjgwNDNjZWZhMWUwNWE5YjI="
		),
	));
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
		echo "cURL Error #:" . $err;
	} else {
		$clientes_json = json_decode($response);
		print_r("sizeof(clientes_json->customers)  = ".sizeof($clientes_json->customers)."<br>");
		if(sizeof($clientes_json->customers) > 0){
			$cuentaTodo += sizeof($clientes_json->customers);
			for($i=0;$i<sizeof($clientes_json->customers);$i++){
				if($clientes_json->customers[$i]->verified_email == ""){
					$clientes_json->customers[$i]->verified_email = 0;
				}
				if($clientes_json->customers[$i]->last_order_id == ""){
					$clientes_json->customers[$i]->last_order_id = 0;
				}
				$sql_customers = "INSERT INTO `customers`(`id_shopify`, `email`, `first_name`, `last_name`, `state`, `total_spent`, `last_order_id`, `note`, `verified_email`, `phone`, `tags`, `currency`) VALUES (";
				$sql_customers .= "{$clientes_json->customers[$i]->id}, ";
				$clientes_json->customers[$i]->email = str_replace("'", "\'", $clientes_json->customers[$i]->email);
				$sql_customers .= "'{$clientes_json->customers[$i]->email}', ";
				$clientes_json->customers[$i]->first_name = str_replace("'", "\'", $clientes_json->customers[$i]->first_name);
				$sql_customers .= "'{$clientes_json->customers[$i]->first_name}', ";
				$clientes_json->customers[$i]->last_name = str_replace("'", "\'", $clientes_json->customers[$i]->last_name);
				$sql_customers .= "'{$clientes_json->customers[$i]->last_name}', ";
				$clientes_json->customers[$i]->state = str_replace("'", "\'", $clientes_json->customers[$i]->state);
				$sql_customers .= "'{$clientes_json->customers[$i]->state}', ";
				$sql_customers .= "{$clientes_json->customers[$i]->total_spent}, ";
				$sql_customers .= "{$clientes_json->customers[$i]->last_order_id}, ";
				$clientes_json->customers[$i]->note = str_replace("'", "\'", $clientes_json->customers[$i]->note);
				$sql_customers .= "'{$clientes_json->customers[$i]->note}', ";
				$sql_customers .= "{$clientes_json->customers[$i]->verified_email}, ";
				$clientes_json->customers[$i]->phone = str_replace("'", "\'", $clientes_json->customers[$i]->phone);
				$sql_customers .= "'{$clientes_json->customers[$i]->phone}', ";
				$clientes_json->customers[$i]->tags = str_replace("'", "\'", $clientes_json->customers[$i]->tags);
				$sql_customers .= "'{$clientes_json->customers[$i]->tags}', ";
				$clientes_json->customers[$i]->currency = str_replace("'", "\'", $clientes_json->customers[$i]->currency);
				$sql_customers .= "'{$clientes_json->customers[$i]->currency}')";
				$sql_customers = utf8_decode($sql_customers);
				if($conn->query($sql_customers)){
					$pos = strpos(strtolower($sql_customers), "rfc");
					if ($pos === false) {
					}else{
						$sql_customers_update = "UPDATE customers SET con_rfc = 1 WHERE id_shopify = {$clientes_json->customers[$i]->id}";
						$conn->query($sql_customers_update);
					}
					for($h=0;$h<sizeof($clientes_json->customers[$i]->addresses);$h++){
						$sql_address = "INSERT INTO `address`(`id_address_shopify`, `id_customer_shopify`, `first_name`, `last_name`, `company`, `address1`, `address2`, `city`, `province`, `country`, `zip`, `phone`, `name`, `province_code`, `country_code`, `country_name`, `default`) VALUES (";
						$sql_address .= " {$clientes_json->customers[$i]->addresses[$h]->id},";
						$sql_address .= " {$clientes_json->customers[$i]->addresses[$h]->customer_id},";
						$clientes_json->customers[$i]->addresses[$h]->first_name = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->first_name);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->first_name}',";
						$clientes_json->customers[$i]->addresses[$h]->last_name = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->last_name);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->last_name}',";
						$clientes_json->customers[$i]->addresses[$h]->company = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->company);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->company}',";
						$clientes_json->customers[$i]->addresses[$h]->address1 = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->address1);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->address1}',";
						$clientes_json->customers[$i]->addresses[$h]->address2 = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->address2);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->address2}',";
						$clientes_json->customers[$i]->addresses[$h]->city = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->city);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->city}',";
						$clientes_json->customers[$i]->addresses[$h]->province = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->province);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->province}',";
						$clientes_json->customers[$i]->addresses[$h]->country = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->country);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->country}',";
						$clientes_json->customers[$i]->addresses[$h]->zip = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->zip);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->zip}',";
						$clientes_json->customers[$i]->addresses[$h]->phone = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->phone);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->phone}',";
						$clientes_json->customers[$i]->addresses[$h]->name = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->name);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->name}',";
						$clientes_json->customers[$i]->addresses[$h]->province_code = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->province_code);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->province_code}',";
						$clientes_json->customers[$i]->addresses[$h]->country_code = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->country_code);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->country_code}',";
						$clientes_json->customers[$i]->addresses[$h]->country_name = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->country_name);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->country_name}',";
						$clientes_json->customers[$i]->addresses[$h]->default = str_replace("'", "\'", $clientes_json->customers[$i]->addresses[$h]->default);
						$sql_address .= " '{$clientes_json->customers[$i]->addresses[$h]->default}')";
						$sql_address = utf8_decode($sql_address);
						if($conn->query($sql_address)){
							$pos = strpos(strtolower($sql_address), "rfc");
							if ($pos === false) {
							}else{
								$sql_address_update = "UPDATE address SET con_rfc = 1 WHERE id_address_shopify = {$clientes_json->customers[$i]->addresses[$h]->id}";
								$conn->query($sql_address_update);
							}
						}else{
							print_r("Fallo address {{$clientes_json->customers[$i]->addresses[$h]->id} <br>");
							print_r("{$sql_address}<br><br>");
						}
					}
				}else{
					print_r("Fallo customer {$clientes_json->customers[$i]->id} <br>");
					print_r("{$sql_customers}<br><br>");
				}
			}
		}else{
			$j = $paginas+1;
		}
		print_r("<br>cuentaTodo = {$cuentaTodo}");
	}
}
?>
