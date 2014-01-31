<?php
/**
 * WP Authentication plugin
 *
 * @author Sunil Kumar <dhanda.sunil@gmail.com>
 * @package		wordpress
 * @subpackage	wp
 * @since 3.4.2
 */
class deLoginByGoogle extends loginBySocialID{
	static $loginBy = 'deLoginByGoogle';
	function onLogin( ){
		$result = $this->loginByGoogle();
		if($result->status == 'SUCCESS'){
			$row = $this->getUserByMail( $result->email);
			if(!$row){
				$this->creatUser($result->username, $result->email);
				$row = $this->getUserByMail($result->email);
				update_user_meta($row->ID, 'email', $result->email);
				update_user_meta($row->ID, 'first_name', $result->first_name);
				update_user_meta($row->ID, 'deuid', $result->deuid);
				update_user_meta($row->ID, 'deutype', $result->deutype);
				wp_update_user( array ('ID' => $row->ID, 'display_name' => $result->first_name) ) ;
			}
			$this->loginUser($row->ID);	
		}
	}
	function loginByGoogle(){
		$post 		= $_POST;
		$get  		= $_GET;
		$request 	= $_REQUEST;
		$site 		= $this->siteUrl();
		$callBackUrl= $this->callBackUrl();
		$response 	= new stdClass();
		$a			= explode('_',$this->get_var(parent::$loginKey));
		$action		= $a[1];
		if ($action == 'login'){// Get identity from user and redirect browser to OpenID Server
			$openid = new deOpenIdGoogle;
			$openid->SetTrustRoot($site.'index.php' );
			if ($openid->GetOpenIDServer()){
				$openid->SetCancelURL($callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check');	   
				$openid->SetApprovedURL($callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check');  	// Send Response from OpenID server to this script
				$openid->Redirect();// This will redirect user to OpenID Server				
			}else{
				$error = $openid->GetError();			
				$response->status = 'ERROR';
				$response->error_code 	= $error['code'];
				$response->error_message = $error['description'];
			}		
		}elseif($get['openid_mode'] == 'id_res'){ 	// Perform HTTP Request to OpenID server to validate key
			$openid = new deOpenIdGoogle;
			$openid->SetIdentity($get['openid_identity']);
			$openid_validation_result = $openid->ValidateWithServer();
			if($openid_validation_result == true){ 		// OK HERE KEY IS VALID
				$response->email    	= $get['openid_ext1_value_email'];
				$response->username 	= $get['openid_ext1_value_email'];
				$response->first_name	= array_shift(explode('@',$get['openid_ext1_value_email']));
				$response->deuid		= $get['openid_ext1_value_email'];
				$response->deutype		= 'google';
				$response->status   	= 'SUCCESS';
				$response->error_message = '';				
			}elseif($openid->IsError() == true){// ON THE WAY, WE GOT SOME ERROR
				$error = $openid->GetError();
				$response->status = 'ERROR';
				$response->error_code 	= $error['code'];
				$response->error_message = $error['description'];
			}else{// Signature Verification Failed
				$response->status = 'ERROR';
				$response->error_code 	= 2;
				$response->error_message = "INVALID AUTHORIZATION";
			}
		}elseif ($get['openid_mode'] == 'cancel'){ // User Canceled your Request
			$response->status = 'ERROR';
			$response->error_code 	= 1;
			$response->error_message = "USER CANCELED REQUEST";
		}
		return $response;
	}
}

?>