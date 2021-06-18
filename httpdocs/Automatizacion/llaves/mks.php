<?php 
$activeStore = explode("/",$_SERVER['REQUEST_URI'])[1]; 
if($activeStore == "store"){
	$urlToken = "https://solutiontinax-solutiontokeninaxpr.azurewebsites.net/SolutionToken/api/SolutionToken";
	$urlDyn = "ayt.operations";
}else{
	$urlToken = "https://solutiontinaxdev.azurewebsites.net/SolutionToken/api/SolutionToken";
	$urlDyn = "tes-ayt.sandbox.operations";

}
$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://solutiontinaxdev.azurewebsites.net/SolutionToken/api/SolutionToken",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_USERPWD => 'atp\\administrador:Avance04',
	CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	$result = "cURL Error #:" . $err;
} else {
	$tok = $response;
	$tokenTst=json_decode($tok);
	$tokenTst=$tokenTst[0]->Token;
	
}

$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://solutiontinax-solutiontokeninaxpr.azurewebsites.net/SolutionToken/api/SolutionToken",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_USERPWD => 'atp\\administrador:Avance04',
	CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	$result = "cURL Error #:" . $err;
} else {
	$tok = $response;
	$tokenPr=json_decode($tok);
	$tokenPr=$tokenPr[0]->Token;
	
}

$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://solutiontinaxprod.azurewebsites.net/SolutionTokenLID/api/SolutionToken",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_USERPWD => 'atp\\administrador:Avance04',
	CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	$result = "cURL Error #:" . $err;
} else {
	$tok = $response;
	$tokenLidPr=json_decode($tok);
	$tokenLidPr=$tokenLidPr[0]->Token;
}

$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://solutiontinaxdev.azurewebsites.net/SolutionTokenLIN/api/SolutionToken",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_USERPWD => 'atp\\administrador:Avance04',
	CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
	$result = "cURL Error #:" . $err;
} else {
	$tok = $response;
	$tokenLidPrue=json_decode($tok);
	$tokenLidPrue=$tokenLidPrue[0]->Token;
}


$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://solutiontinaxprod.azurewebsites.net/SolutionTokenINN/api/SolutionTokenINN",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_USERPWD => 'atp\\administrador:Avance04',
	CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	$result = "cURL Error #:" . $err;
} else {
	$tok = $response;
	$tokenINNPr=json_decode($tok);
	$tokenINNPr=$tokenINNPr[0]->Token;
}

$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => "https://solutiontinaxdev.azurewebsites.net/SolutionTokenINN/api/SolutionTokenINN",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_POSTFIELDS => "",
	CURLOPT_USERPWD => 'atp\\administrador:Avance04',
	CURLOPT_HTTPAUTH => CURLAUTH_NTLM
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
	$result = "cURL Error #:" . $err;
} else {
	$tok = $response;
	$tokenINNPrue=json_decode($tok);
	$tokenINNPrue=$tokenINNPrue[0]->Token;
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</head>
<style>
	body{
		font-family: 'Oswald', sans-serif;
	}
	textarea{
		overflow: hidden;
		resize: none;
		line-height: 1.5;
		padding: 5px;
		width: 300px;
		height: 34px;
		border: 2px solid #9f9f9f;
		border-radius: 12px;
	}
	label{
		font-weight: 600;
		margin: 0;
		line-height: 1;
	}
	.tcopy{
		
	}
	.tcopy:hover{
		color: #6e6e6e;
		cursor: copy;
	}
	textarea:focus {
		outline: red auto 0px;
	}
	.borde{
		border: 2px solid #505860;
		border-radius: 21px;
	}
	.titulo{
		position: absolute;
		top: -30px;
		left: 50%;
		transform: translate(-50%, 10px);
	}
	.mh{
		min-height: 3rem;
	}
</style>
<body class="bg-dark text-light">
	<div class="container-fluid">
		<div class="row">
			<div class="container">
				<div class="row">
					<div class="col-12 my-5 py-5">
						<div class="row justify-content-center my-2 py-4">
							<div class="col-lg-5 col-8 borde mx-1">
								<div class="row justify-content-center pb-2">
									<div class="col-12 text-center mh">
										<h3 class="titulo bg-dark px-3">ATP Productivo</h3>
									</div>
									<textarea class="col-6" id="productivo"></textarea>
									<div class="col-12 text-center">
										<h5 class="tcopy mt-2" id="cproductivo" onclick="copia('productivo')">copy!</h5>
									</div>
								</div>
							</div>
							<div class="col-lg-5 col-8 borde mx-1">
								<div class="row justify-content-center pb-2">
									<div class="col-12 text-center mh">
										<h3 class="titulo bg-dark px-3">ATP Pruebas</h3>
									</div>
									<textarea class="col-6" id="pruebas"></textarea>
									<div class="col-12 text-center">
										<h5 class="tcopy mt-2" id="cpruebas" onclick="copia('pruebas')">copy!</h5>
									</div>
								</div>
							</div>
						</div>		
						<div class="row justify-content-center my-2 py-4">
							<div class="col-lg-5 col-8 borde mx-1">
								<div class="row justify-content-center pb-2">
									<div class="col-12 text-center mh">
										<h3 class="titulo bg-dark px-3">Lid Productivo</h3>
									</div>
									<textarea class="col-6" id="productivoLid"></textarea>
									<div class="col-12 text-center">
										<h5 class="tcopy mt-2" id="cproductivoLid" onclick="copia('productivoLid')">copy!</h5>
									</div>
								</div>
							</div>
							<div class="col-lg-5 col-8 borde mx-1">
								<div class="row justify-content-center pb-2">
									<div class="col-12 text-center mh">
										<h3 class="titulo bg-dark px-3">Lid Pruebas</h3>
									</div>
									<textarea class="col-6" id="pruebasLid"></textarea>
									<div class="col-12 text-center">
										<h5 class="tcopy mt-2" id="cpruebasLid" onclick="copia('pruebasLid')">copy!</h5>
									</div>
								</div>
							</div>
						</div>					
						<div class="row justify-content-center my-2 py-4">
							<div class="col-lg-5 col-8 borde mx-1">
								<div class="row justify-content-center pb-2">
									<div class="col-12 text-center mh">
										<h3 class="titulo bg-dark px-3">INN Productivo</h3>
									</div>
									<textarea class="col-6" id="productivoInn"></textarea>
									<div class="col-12 text-center">
										<h5 class="tcopy mt-2" id="cproductivoInn" onclick="copia('productivoInn')">copy!</h5>
									</div>
								</div>
							</div>
							<div class="col-lg-5 col-8 borde mx-1">
								<div class="row justify-content-center pb-2">
									<div class="col-12 text-center mh">
										<h3 class="titulo bg-dark px-3">INN Pruebas</h3>
									</div>
									<textarea class="col-6" id="pruebasInn"></textarea>
									<div class="col-12 text-center">
										<h5 class="tcopy mt-2" id="cpruebasInn" onclick="copia('pruebasInn')">copy!</h5>
									</div>
								</div>
							</div>
						</div>					
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>

		var tktst = ("<?php echo $tokenTst; ?>")
		document.getElementById('pruebas').innerText = tktst

		var tkprd = ("<?php echo $tokenPr; ?>")
		document.getElementById('productivo').innerText = tkprd

		var tktstLid = ("<?php echo $tokenLidPrue; ?>")
		document.getElementById('pruebasLid').innerText = tktstLid

		var tkprdLid = ("<?php echo $tokenLidPr; ?>")
		document.getElementById('productivoLid').innerText = tkprdLid

		var tktstInn = ("<?php echo $tokenINNPrue; ?>")
		document.getElementById('pruebasInn').innerText = tktstInn

		var tkprdInn = ("<?php echo $tokenINNPr; ?>")
		document.getElementById('productivoInn').innerText = tkprdInn

		function copia(id) {
			var textArea = document.getElementById(id)
			textArea.select()
			try {
				var status = document.execCommand('copy');
				if(!status){
					console.error("Cannot copy text");
				}else{
					/*
					if(id == 'productivo'){
						id2 = 'pruebas'
						id3 = 'productivoLid'
						id4 = 'pruebasLid'
						id5 = 'productivoInn'
						id5 = 'productivoInn'
					}else if(id == 'productivoLid'){
						id2 = 'pruebas'
						id3 = 'productivo'
						id4 = 'pruebasLid'
						id5 = 'productivoInn'
					}else if(id == 'pruebasLid'){
						id2 = 'pruebas'
						id3 = 'productivo'
						id4 = 'productivoLid'
						id5 = 'productivoInn'
					}else if(id == 'pruebas'){
						id2 = 'pruebasLid'
						id3 = 'productivo'
						id4 = 'productivoLid'
						id5 = 'productivoInn'
						id5 = 'productivoInn'
					}
					*/
					document.getElementById("cpruebas").innerText = "copy!"
					document.getElementById("cproductivo").innerText = "copy!"
					document.getElementById("cpruebasLid").innerText = "copy!"
					document.getElementById("cproductivoLid").innerText = "copy!"
					document.getElementById("cpruebasInn").innerText = "copy!"
					document.getElementById("cproductivoInn").innerText = "copy!"
					document.getElementById("c"+id).innerText ="copy! (texto copiado)"
				}
			} catch (err) {
				console.log('Unable to copy.' + err);
			}
		}

	</script>
</body>
</html>