<?php

class deOpenIdGoogle{

	var $URLs                = array();

	var $error        		 = array();

	var $fields       		 = array();

	var $openid_url_identity = 'http://specs.openid.net/auth/2.0/identifier_select';

	var $typeEmail    		 = 'http://axschema.org/contact/email';

	var $extNs       		 = 'http://openid.net/srv/ax/1.0';

	var $OpenIdServer 		 = 'https://www.google.com/accounts/o8/ud';

	var $claimed_id   		 = 'http://specs.openid.net/auth/2.0/identifier_select';

	var $openid_ns    		 = 'http://specs.openid.net/auth/2.0';

	var $realm        		 = '';

	function SimpleOpenID(){

		if (!function_exists('curl_exec')) {

			die('Error: Class SimpleOpenID requires curl extension to work');

		}

	}

	function SetOpenIDServer($a){

		$this->URLs['openid_server'] = $a;

	}

	function SetTrustRoot($a){

		$this->URLs['trust_root'] = $a;

	}

	function SetCancelURL($a){

		$this->URLs['cancel'] = $a;

	}

	function SetApprovedURL($a){

		$this->URLs['approved'] = $a;

	}

	function SetRequiredFields($a){

		if (is_array($a)){

			$this->fields['required'] = $a;

		}else{

			$this->fields['required'][] = $a;

		}

	}

	function SetOptionalFields($a){

		if (is_array($a)){

			$this->fields['optional'] = $a;

		}else{

			$this->fields['optional'][] = $a;

		}

	}

	function SetIdentity($a){ 	// Set Identity URL

 			if(strpos($a, 'http://') === false) {

		 		$a = 'http://'.$a;

		 	}

			/*

			$u = parse_url(trim($a));

			if (!isset($u['path'])){

				$u['path'] = '/';

			}else if(substr($u['path'],-1,1) == '/'){

				$u['path'] = substr($u['path'], 0, strlen($u['path'])-1);

			}

			if (isset($u['query'])){ // If there is a query string, then use identity as is

				$identity = $a;

			}else{

				$identity = $u['scheme'] . '://' . $u['host'] . $u['path'];

			}*/

			$this->openid_url_identity = $a;

	}

	function GetIdentity(){ 	// Get Identity

		return $this->openid_url_identity;

	}

	function GetError(){

		$e = $this->error;

		return array('code'=>$e[0],'description'=>$e[1]);

	}



	function ErrorStore($code, $desc = null){

		$errs['OPENID_NOSERVERSFOUND'] = 'Cannot find OpenID Server TAG on Identity page.';

		if ($desc == null){

			$desc = $errs[$code];

		}

	   	$this->error = array($code,$desc);

	}



	function IsError(){

		if (count($this->error) > 0){

			return true;

		}else{

			return false;

		}

	}

	

	function splitResponse($response) {

		$r = array();

		$response = explode("\n", $response);

		foreach($response as $line) {

			$line = trim($line);

			if ($line != "") {

				list($key, $value) = explode(":", $line, 2);

				$r[trim($key)] = trim($value);

			}

		}

	 	return $r;

	}

	

	function OpenID_Standarize($openid_identity){

		$u = parse_url(strtolower(trim($openid_identity)));

		if ($u['path'] == '/'){

			$u['path'] = '';

		}

		if(substr($u['path'],-1,1) == '/'){

			$u['path'] = substr($u['path'], 0, strlen($u['path'])-1);

		}

		if (isset($u['query'])){ // If there is a query string, then use identity as is

			return $u['host'] . $u['path'] . '?' . $u['query'];

		}else{

			return $u['host'] . $u['path'];

		}

	}

	

	function array2url($arr){ // converts associated array to URL Query String
		$query = '';
		if (!is_array($arr)){

			return false;

		}

		foreach($arr as $key => $value){

			$query .= $key . "=" . $value . "&";

		}

		return $query;

	}

	

	function CURL_Request($url, $method="GET", $params = "") { // Remember, SSL MUST BE SUPPORTED

			if (is_array($params)) $params = $this->array2url($params);

			$curl = curl_init($url . ($method == "GET" && $params != "" ? "?" . $params : ""));

			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

			curl_setopt($curl, CURLOPT_HEADER, false);

			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($curl, CURLOPT_HTTPGET, ($method == "GET"));

			curl_setopt($curl, CURLOPT_POST, ($method == "POST"));

			if ($method == "POST") curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($curl);

			if (curl_errno($curl) == 0){

				$response;

			}else{

				$this->ErrorStore('OPENID_CURL', curl_error($curl));

			}

			return $response;

	}

	

	 function HTML2OpenIDServer($content) {

		$get = array();

		// Get details of their OpenID server and (optional) delegate

		preg_match_all('/<link[^>]*rel="openid.server"[^>]*href="([^"]+)"[^>]*\/?>/i', $content, $matches1);

		preg_match_all('/<link[^>]*href="([^"]+)"[^>]*rel="openid.server"[^>]*\/?>/i', $content, $matches2);

		$servers = array_merge($matches1[1], $matches2[1]);

		

		preg_match_all('/<link[^>]*rel="openid.delegate"[^>]*href="([^"]+)"[^>]*\/?>/i', $content, $matches1);

		

		preg_match_all('/<link[^>]*href="([^"]+)"[^>]*rel="openid.delegate"[^>]*\/?>/i', $content, $matches2);

		

		$delegates = array_merge($matches1[1], $matches2[1]);

		

		$ret = array($servers, $delegates);

		return $ret;

	}

	

	function GetOpenIDServer(){	

		$servers = $this->OpenIdServer;

		$this->SetOpenIDServer($servers);

		return $servers;

	}

	

	function GetRedirectURL(){

		$params = array();

		$params['openid.ns']          = urlencode($this->openid_ns);

		$params['openid.claimed_id']  = urlencode($this->claimed_id);

		$params['openid.return_to']   = urlencode($this->URLs['approved']);		

		$params['openid.mode']        = 'checkid_setup';

		$params['openid.identity']    = urlencode($this->openid_url_identity);

		$params['openid.trust_root']  = urlencode($this->URLs['trust_root']);

		$params['openid.realm']       = urlencode($this->URLs['approved']);		

		

		/* //for later use 

		if (count($this->fields['required']) > 0){

		//	$params['openid.sreg.required'] = implode(',',$this->fields['required']);

		}

		if (count($this->fields['optional']) > 0){

		//	$params['openid.sreg.optional'] = implode(',',$this->fields['optional']);

		}

		*/



		$params['openid.ns.ext1']        = urlencode($this->extNs);

		$params['openid.ext1.mode']      = "fetch_request";

		$params['openid.ext1.type.email']= urlencode($this->typeEmail);

		$params['openid.ext1.required']  = "email";	

		return $this->URLs['openid_server'] . "?". $this->array2url($params);

	}

	

	function Redirect(){

		$redirect_to = $this->GetRedirectURL();

		if (headers_sent()){ // Use JavaScript to redirect if content has been previously sent (not recommended, but safe)

			echo '<script language="JavaScript" type="text/javascript">window.location=\'';

			echo $redirect_to;

			echo '\';</script>';

		}else{	// Default Header Redirect

			header('Location: ' . $redirect_to);

		}

		exit;

	}

	function ValidateWithServer(){

	    $get = $_GET;

		$params = array(

			'openid.assoc_handle' => urlencode($get['openid_assoc_handle']),

			'openid.signed'       => urlencode($get['openid_signed']),

			'openid.sig'          => urlencode($get['openid_sig'])

		);

		// Send only required parameters to confirm validity		

		$arr_signed = explode(",", $get['openid_signed']);

		

		for ($i=0; $i<count($arr_signed); $i++){

			$s = str_replace('.','_',$arr_signed[$i]);

			$s2 = $arr_signed[$i];

			$c = $get['openid_' . $s];

			// if ($c != ""){

				$params['openid.' . $s2] = urlencode($c);

			// }

		}		

		

		$params['openid.mode'] = "check_authentication";

		// print "<pre>";

		// print_r($get);

		// print_r($params);

		// print "</pre>";

		$openid_server = $this->GetOpenIDServer();

		if ($openid_server == false){

			return false;

		}

		$response = $this->CURL_Request($openid_server,'GET',$params);

		$data     = $this->splitResponse($response);

		if ($data['is_valid'] == "true") {

			return true;

		}else{

			return false;

		}

	}

}



?>