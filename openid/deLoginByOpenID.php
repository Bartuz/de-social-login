<?php
class deLoginByOpenID extends loginBySocialID{
	static $loginBy = 'deLoginByOpenID';
	function onLogin( ){
		$result = $this->loginByOpenID();
		if($result->status == 'SUCCESS'){
			$row = $this->getUserByUsername($result->username);
			if(!$row){
				$user_id = $this->creatUser($result->username, $result->email);
			}else{
				$user_id = $row->ID;
			}
			//var_dump(array($result, $user_id, $row));
			$this->loginUser($user_id);			
		}
	}
	
	function loginByOpenID(){
		$post 		= $_POST;
		$get  		= $_GET;
		$request 	= $_REQUEST;
		$site 		= $this->siteUrl();
		$callBackUrl= $this->callBackUrl();
		$response 	= new stdClass();
		$a			= explode('_',$this->get_var(parent::$loginKey));
		$action		= $a[1];
		if ($action == 'login'){
			// Get identity from user and redirect browser to OpenID Server
			$openid = new deOpenIdOpenID;
			$openid->SetIdentity($get['openid_url']);
			$openid->SetTrustRoot('http://' . $_SERVER["HTTP_HOST"]);
			$openid->SetRequiredFields('email');
			$openid->SetOptionalFields(array('dob','gender','postcode','country','language','timezone'));
			if ($openid->GetOpenIDServer()){
				$openid->SetApprovedURL($callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check');  	// Send Response from OpenID server to this script
				$openid->Redirect(); 	// This will redirect user to OpenID Server
			}else{
				$error = $openid->GetError();
				$response->status 		= 'ERROR';
				$response->error_code 	= $error['code'];
				$response->error_message= $error['description'];
			}
		}else if($get['openid_mode'] == 'id_res'){ 	// Perform HTTP Request to OpenID server to validate key
			$openid = new deOpenIdOpenID;
			$openid->SetIdentity($get['openid_identity']);
			$openid_validation_result = $openid->ValidateWithServer();
			
			if ($openid_validation_result == true){ 		// OK HERE KEY IS VALID
				//var_dump($openid);die();
				$response->status 	= 'SUCCESS';
				$response->email	= '';
				$response->username	= explode('//',$openid->GetIdentity());
				$response->username	= $response->username[1];
				$response->error_message = '';
			}elseif($openid->IsError() == true){// ON THE WAY, WE GOT SOME ERROR
				$error 					= $openid->GetError();
				$response->status 		= 'ERROR';
				$response->error_code 	= $error['code'];
				$response->error_message= $error['description'];
			}else{// Signature Verification Failed
				$response->status 		= 'ERROR';
				$response->error_code 	= 2;
				$response->error_message= "INVALID AUTHORIZATION";
			}
		}elseif ($get['openid_mode'] == 'cancel'){ // User Canceled your Request
			$response->status 		= 'ERROR';
			$response->error_code 	= 1;
			$response->error_message= "USER CANCELED REQUEST";
		}
		return $response;
	}
}
?>