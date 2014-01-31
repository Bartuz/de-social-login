<?php

/* Load OAuth lib. You can find it at http://oauth.net */
require_once loginByOpenID_PATH.'twitter/twitteroauth.php';


class deLoginByTwitter extends loginBySocialID{
	static $loginBy = 'deLoginByTwitter';
	function onLogin(){
		$result = $this->loginByOpenID();
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
	
	function loginByOpenID(){
		$post 		= $_POST;
		$get  		= $_GET;
		$request 	= $_REQUEST;
		$site 		= $this->siteUrl();
		$callBackUrl= $this->callBackUrl();
		$response 	= new stdClass();
		$action		= explode('_',$this->get_var(parent::$loginKey));
		$action		= $action[1];
		$options 	= $this->getOptions();
		@session_start();
		//var_dump(array($options['twitter_key'], $options['twitter_secret']) );
		
		if ($action == 'login'){
			// Get identity from user and redirect browser to OpenID Server
			if(!isset($request['oauth_token']) || $request['oauth_token']==''){
				//echo "<div style='display:none'>";
				$twitterObj 	= new TwitterOAuth($options['twitter_key'], $options['twitter_secret']);
				
	
				$request_token 	= $twitterObj->getRequestToken($callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check');
				$_SESSION['oauth_twitter'] = array();
				/* Save temporary credentials to session. */
				$_SESSION['oauth_twitter']['oauth_token'] = $token = $request_token['oauth_token'];
				$_SESSION['oauth_twitter']['oauth_token_secret'] = $request_token['oauth_token_secret'];
				 /* If last connection failed don't display authorization link. */
				switch ($twitterObj->http_code) {
					case 200:
						try{
							$url = $twitterObj->getAuthorizeUrl($token);
							$this->redirect($url);
						}catch(Exception $e){
							$response->status 		= 'ERROR';
							$response->error_code 	= 2;
							$response->error_message= 'Could not get AuthorizeUrl.';
						}
					break;
					default:
						$response->status 		= 'ERROR';
						$response->error_code 	= 2;
						$response->error_message= 'Could not connect to Twitter. Refresh the page or try again later.';
					break;
				}
				//echo('sssss3'.$url.', '.$callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check');				
				//echo "</div>";
			}else{
				$response->status 		= 'ERROR';
				$response->error_code 	= 2;
				$response->error_message= 'INVALID AUTHORIZATION';
			}
		}else if(isset($request['oauth_token']) && isset($request['oauth_verifier'])){
			/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
			$twitterObj = new TwitterOAuth($options['twitter_key'], $options['twitter_secret'], $_SESSION['oauth_twitter']['oauth_token'], $_SESSION['oauth_twitter']['oauth_token_secret']);			
			/* Remove no longer needed request tokens */
			unset($_SESSION['oauth_twitter']);
			try{
				$access_token 		= $twitterObj->getAccessToken($request['oauth_verifier']);
				/* If HTTP response is 200 continue otherwise send to connect page to retry */
				if (200 == $twitterObj->http_code) {
					$user_profile		= $twitterObj->get('users/show',array('screen_name'=>$access_token['screen_name'],'include_entities'=>true));
					//var_dump($user_profile);die();
					/* Request access twitterObj from twitter */
					$response->status 		= 'SUCCESS';
					$response->deuid		= $user_profile->id;
					$response->deutype		= 'twitter';
					$response->name			= explode(' ', $user_profile->name, 2);
					$response->first_name	= $response->name[0];
					$response->last_name	= (isset($response->name[1]))?$response->name[1]:'';
					$response->deuimage 	= $user_profile->profile_image_url;
					$response->email		= $user_profile->screen_name.'@twitter.com';
					$response->username		= $user_profile->screen_name.'@twitter.com';
					$response->error_message = '';				
				}else{
					$response->status 		= 'ERROR';
					$response->error_code 	= 2;
					$response->error_message= 'Could not connect to Twitter. Refresh the page or try again later.';
				}
			}catch(Exception $e){
				$response->status 		= 'ERROR';
				$response->error_code 	= 2;
				$response->error_message= 'Could not get AccessToken.';
			}
		}else{ // User Canceled your Request
			$response->status 		= 'ERROR';
			$response->error_code 	= 1;
			$response->error_message= "USER CANCELED REQUEST";
		}
		//var_dump($response);
		//die();
		return $response;
	}
}
?>