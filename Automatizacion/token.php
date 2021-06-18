<?php
// $pasw = '$r%ER2aY#wBD3cDP';
class Token{
	public function getToken($empresa, $plataforma){
		if($empresa == "ATP"){
			if($plataforma == "prod"){
				$texto = "https://solutiontinax-solutiontokeninaxpr.azurewebsites.net/SolutionToken/api/SolutionToken";
			}else{
				$texto = "https://solutiontinaxdev.azurewebsites.net/SolutionToken/api/SolutionToken";
			}
		}else if($empresa == "LIN"){
			if($plataforma == "prod"){
				$texto = "https://solutiontinaxprod.azurewebsites.net/SolutionTokenLID/api/SolutionToken";
			}else{
				$texto = "https://solutiontinaxdev.azurewebsites.net/SolutionTokenLIN/api/SolutionToken";
			}
		}else if($empresa == "INN"){
			if($plataforma == "prod"){
				$texto = "https://solutiontinaxprod.azurewebsites.net/SolutionTokenINN/api/SolutionTokenINN";
			}else{
				$texto = "https://solutiontinaxdev.azurewebsites.net/SolutionTokenINN/api/SolutionTokenINN";
			}
		}




		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $texto,
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
			$result = $response;
		}
		return json_decode($result);
	}
}

?>