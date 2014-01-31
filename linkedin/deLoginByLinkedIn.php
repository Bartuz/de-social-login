<?php
//http://developerextensions.com/joomla_301/index.php?option=com_users&task=user%2Elogin&SOCIALID=deLoginByLinkedIn_check&oauth_problem=user_refused
require(loginByOpenID_PATH.'linkedin/linkedin_3.2.0.class.php');
class deLoginByLinkedIn extends loginBySocialID {
	static $loginBy = 'deLoginByLinkedin';
	function onLogin( ){
		$result = $this->loginByLinkedIn();
		if($result->status == 'SUCCESS'){
			$row = $this->getUserByMail( $result->email );
			if(!$row){
				$this->creatUser($result->username, $result->email);
				$row = $this->getUserByMail( $result->email );
				update_user_meta($row->ID, 'email', $result->email);
				update_user_meta($row->ID, 'first_name', $result->first_name);
				update_user_meta($row->ID, 'last_name', $result->last_name);
				update_user_meta($row->ID, 'deuid', $result->deuid);
				update_user_meta($row->ID, 'deutype', $result->deutype);
				wp_update_user( array ('ID' => $row->ID, 'display_name' => $result->first_name.' '.$result->last_name) ) ;
			}
			$this->loginUser($row->ID);			
		}
	}
	
	function loginByLinkedIn(){
		$post 		= $_POST;
		$get  		= $_GET;
		$request 	= $_REQUEST;
		$site 		= $this->siteUrl();
		$callBackUrl= $this->callBackUrl();
		$response 	= new stdClass();
		$action		= explode('_',$this->get_var(parent::$loginKey));
		$action		= $action[1];
		
		$options 	= $this->getOptions();
		
		$API_CONFIG	= array(
			'appKey'		=>$options['linkedin_key'],
			'appSecret'    => $options['linkedin_secret'],
			'callbackUrl'	=>$callBackUrl.parent::$loginKey.'='.self::$loginBy.'_check'
		);
		@session_start();
		$OBJ_linkedin = new LinkedIn($API_CONFIG);
		if ($action == 'login'){
			// send a request for a LinkedIn access token
			$response = $OBJ_linkedin->retrieveTokenRequest(array('scope'=>'r_emailaddress'));
			if($response['success'] === TRUE) {
				$_SESSION['oauth_linkedin'] = $response['linkedin'];
				 // redirect the user to the LinkedIn authentication/authorisation page to initiate validation.
			  	$this->redirect(LINKEDIN::_URL_AUTH . $response['linkedin']['oauth_token']);
			}else{
				$response->status 		= 'ERROR';
				$response->error_code 	= 1;
				$response->error_message= 'Request token retrieval failed';
			}
		}elseif(isset($_GET['oauth_verifier'])){
			// LinkedIn has sent a response, user has granted permission, take the temp access token, the user's secret and the verifier to request the user's real secret key
			$response1 = $OBJ_linkedin->retrieveTokenAccess($_SESSION['oauth_linkedin']['oauth_token'], $_SESSION['oauth_linkedin']['oauth_token_secret'], $_GET['oauth_verifier']);
			if($response1['success'] === TRUE){
				$OBJ_linkedin->setTokenAccess($response1['linkedin']);
          		$OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
				 $response2 = $OBJ_linkedin->profile('~:(email-address,id,first-name,last-name,picture-url)');
				if($response2['success'] === TRUE) {
					//var_dump(array($_SESSION,$response2));die();
				  	$data = json_decode($response2['linkedin']);
				 	$response->status 		= 'SUCCESS';
					$response->deuid		= $data->id;
					$response->deutype		= 'linkedin';
					$response->first_name	= $data->firstName;
					$response->last_name	= $data->lastName;
					$response->email		= $data->emailAddress;
					$response->username		= $data->emailAddress;
					$response->error_message = '';
				}else{
				  	$response->status 		= 'ERROR';
					$response->error_code 	= 2;
					$response->error_message= 'Error retrieving profile information';
				}
			}else{
				$response->status 		= 'ERROR';
				$response->error_code 	= 1;
				$response->error_message= 'Access token retrieval failed';
			}
		}else{
			$response->status 		= 'ERROR';
			$response->error_code 	= 1;
			if(isset($get['oauth_problem'])&&$get['oauth_problem']=='user_refused'){
				$response->error_message= 'Access token retrieval failed';
			}else{
				$response->error_message= 'Request cancelled by user!';
			}
		}
		return $response;
	}
}

?>