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
	function onLogin(){
		$result = $this->loginByGoogle();
		if($result->status == 'SUCCESS'){
			$row = $this->getUserByMail( $result->email);
			if(!get_option('de_social_login_force_register') && !$row){
				wp_redirect( site_url().'/wp-login.php?registration=1' );
				exit();
			}
			
			if(get_option('de_social_login_force_register') && !$row){
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
		$options 	= $this->getOptions();
		$response 	= new stdClass();
		$a			= explode('_',$this->get_var(parent::$loginKey));
		$action		= $a[1];
		$client_id		= $options['google_client'];
		$client_secret	= $options['google_secret'];
		$redirect_uri	= $callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check';
		
		$client = new Google_Client;
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->addScope("https://www.googleapis.com/auth/plus.profile.emails.read");
		$service = new Google_Service_Plus($client);
		
		if ($action == 'login'){// Get identity from user and redirect browser to OpenID Server
			//$client->SetTrustRoot($site.'index.php' );
			if(!(isset($_SESSION['access_token']) && $_SESSION['access_token'])){
				$authUrl = $client->createAuthUrl();
				$this->redirect($authUrl);
				die();
			}else{
				$this->redirect($redirect_uri);
				die();
			}
			/*else{
				$client->setAccessToken($_SESSION['access_token']);
				$user	= $service->people->get("me");
			}else{
				$error = $openid->GetError();			
				$response->status = 'ERROR';
				$response->error_code 	= $error['code'];
				$response->error_message = $error['description'];
			}*/		
		}elseif(isset($_GET['code'])){ 	// Perform HTTP Request to OpenID server to validate key
			$client->authenticate($_GET['code']);
			$_SESSION['access_token'] 	= $client->getAccessToken();
			$this->redirect($redirect_uri);
			die();
			//$redirect 					= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
			//header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
		}elseif(isset($_SESSION['access_token']) && $_SESSION['access_token']){
			$client->setAccessToken($_SESSION['access_token']);
			try{
				$user	= $service->people->get("me", array());
			}catch(Exception $fault){
				unset($_SESSION['access_token']);
				$this->redirect($callBackUrl.parent::$loginKey.'='.self::$loginBy.'_login');
				die();
			}
			if(!empty($user)){// OK HERE KEY IS VALID
				if(!empty($user->emails)){
					$response->email    	= $user->emails[0]->value;
					$response->username 	= $user->emails[0]->value;
					$response->first_name	= $user->name->givenName;
					$response->deuid		= $user->emails[0]->value;
					$response->deutype		= 'google';
					$response->status   	= 'SUCCESS';
					$response->error_message = '';
				}else{
					$response->status = 'ERROR';
					$response->error_code 	= 2;
					$response->error_message = "INVALID AUTHORIZATION";
				}
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