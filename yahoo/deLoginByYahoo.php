<?php
/**
 * WP Authentication plugin
 *
 * @author Sunil Kumar <dhanda.sunil@gmail.com>
 * @package		wordpress
 * @subpackage	wp
 * @since 3.4.2
 */
 
// Include the YOS library.
require dirname(__FILE__).'/yahoo.php';


class deLoginByYahoo extends loginBySocialID{
	static $loginBy = 'deLoginByYahoo';
	function onLogin( ){
		$result = $this->loginByYahoo();
		if($result->status == 'SUCCESS'){
			$row = $this->getUserByMail( $result->email);
			if(!$row){
				$this->creatUser($result->username, $result->email);
				$row = $this->getUserByMail( $result->email);
				update_user_meta($row->ID, 'email', $result->email);
				update_user_meta($row->ID, 'first_name', $result->first_name);
				update_user_meta($row->ID, 'last_name', $result->last_name);
				update_user_meta($row->ID, 'deuid', $result->deuid);
				update_user_meta($row->ID, 'deutype', $result->deutype);
				update_user_meta($row->ID, 'deuimage', $result->deuimage);
				wp_update_user( array ('ID' => $row->ID, 'display_name' => $result->first_name.' '.$result->last_name) ) ;
			}
			$this->loginUser($row->ID);	
		}
	}
	function loginByYahoo(){
		$post 		= $_POST;
		$get  		= $_GET;
		$request 	= $_REQUEST;
		$site 		= $this->siteUrl();
		$callBackUrl= $this->callBackUrl();
		$response 	= new stdClass();
		$a			= explode('_',$this->get_var(parent::$loginKey));
		$action		= $a[1];
		$options 	= $this->getOptions();
		if ($action == 'login'){// Get identity from user and redirect browser to OpenID Server
			$callback = $callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check';//YahooUtil::current_url();
  			
			try{
				$auth_url = YahooSession::createAuthorizationUrl($options['yahoo_key'], $options['yahoo_secret'], $callback);
				if(!$auth_url){
					$response->status = 'ERROR';
					$response->error_code 	= 1;
					$response->error_message = "UNABLE TO GET AUTH REQUEST";
				}else{
					$this->redirect($auth_url);	
				}
			}catch(Exception $e){
				$response->status = 'ERROR';
				$response->error_code 	= 1;
				$response->error_message = "UNABLE TO GET AUTH REQUEST";
			}
			
		}else if(isset($request['oauth_token']) && isset($request['oauth_verifier'])){
			$session = YahooSession::requireSession($options['yahoo_key'], $options['yahoo_secret'], $options['yahoo_appid']); // if a session is initialized, fetch the user's profile information
			if($session) {
				// Get the currently sessioned user.
				$user = $session->getSessionedUser();
				// Load the profile for the current user.
				$profile = $user->getProfile();
				$response->status 		= 'SUCCESS';
				$response->deuid		= $profile->guid;
				$response->deutype		= 'yahoo';
				$response->first_name	= $profile->givenName;
				$response->last_name	= $profile->familyName;
				$response->email		= false;
				foreach($profile->emails as $email){
					if(isset($email->primary) && $email->primary==true){
						if(isset($email->handle)){
							$response->email		= $email->handle;
						}
					}
				}
				if(!$response->email && isset($profile->emails[0]->handle)){
					$response->email	= $profile->emails[0]->handle;
				}
				$response->deuimage		= $profile->image->imageUrl;
				$response->username		= $response->email;
				$response->error_message = '';
			}else{
			 	$response->status = 'ERROR';
				$response->error_code 	= 1;
				$response->error_message = "Could not get AccessToken.";
			}
		}else{ // User Canceled your Request
			$response->status = 'ERROR';
			$response->error_code 	= 1;
			$response->error_message = "USER CANCELED REQUEST";
		}
		return $response;
	}
}

?>