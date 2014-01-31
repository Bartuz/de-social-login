<?php
class loginBySocialID{
	static $loginKey 	= 'SOCIALID';
	function getBoxOrders(){
		$orderSettings = new stdClass();
		$boxes = array(
			'facebook'=>'Facebook',
			'twitter'=>'Twitter',
			'openid'=>'OpenId',
			'google'=>'Google',
			'linkedin'=>'Linkedin',
			'yahoo'=>'Yahoo',
		);
		$orders = get_option('de_social_login_orders');
		if(!$orders){
			$orders = array_keys($boxes);
		}else{
			$orders = explode(',',$orders);
			if(count($orders)!=count($boxes)){
				$orders = array_keys($boxes);
			}
		}
		$orderSettings->orders = $orders;
		$orderSettings->boxes = $boxes;
		return $orderSettings;
	}
	function getOptions(){
		$options = array();
		//facebook
		$options['facebook_key'] 		= get_option('de_social_login_facebook_id');
		$options['facebook_secret'] 	= get_option('de_social_login_facebook_secret');
		//twitter
		$options['twitter_key'] 		= get_option('de_social_login_twitter_id');
		$options['twitter_secret'] 		= get_option('de_social_login_twitter_secret');
		//linkedIn 
		$options['linkedin_key'] 		= get_option('de_social_login_linkedin_id');
		$options['linkedin_secret'] 	= get_option('de_social_login_linkedin_secret');
		//yahoo
		$options['yahoo_appid']			= get_option('de_social_login_yahoo_id');
		$options['yahoo_domain']		= get_option('de_social_login_yahoo_domain');
		$options['yahoo_key']			= get_option('de_social_login_yahoo_key');
		$options['yahoo_secret']		= get_option('de_social_login_yahoo_secret');
		return $options;
	}
	function get_var($key,$default=false){
		if(isset($_REQUEST[$key])){
			return $_REQUEST[$key];
		}
		return $default;
	}
	function redirect($redirect){
		if (headers_sent()){ // Use JavaScript to redirect if content has been previously sent (not recommended, but safe)
			echo '<script language="JavaScript" type="text/javascript">window.location=\'';
			echo $redirect;
			echo '\';</script>';
		}else{	// Default Header Redirect
			header('Location: ' . $redirect);
		}
		exit;
	}
	function updateUser($username, $email){
		$row = $this->getUserByUsername ($username);
		if($row && $email!='' && $row->user_email!=$email){
			$row = (array) $row;
			$row['user_email']  = $email;
			wp_update_user($row);
		}
	}

	function getUserByMail($email){
		global $wpdb;
		$row = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_email = '$email'");
		if($row){
			return $row;
		}
		return false;
	}
	function getUserByUsername ($username){
		global $wpdb;
		$row = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$username'");
		if($row){
			return $row;
		}
		return false;
	}
	
	function creatUser($user_name, $user_email){
		$random_password = wp_generate_password(12, false);
		$user_id = wp_create_user( $user_name, $random_password, $user_email );
		wp_new_user_notification( $user_id, $random_password );
		return $user_id;
	}
	
	function set_cookies($user_id = 0, $remember = true) {
		if (!function_exists('wp_set_auth_cookie')){
		  return false;
		}		
		if (!$user_id){
		  return false;
		}	   
		wp_clear_auth_cookie();	
		wp_set_auth_cookie($user_id, $remember);	
		wp_set_current_user($user_id);	
		return true;
  	}
	
	function loginUser($user_id ){
		$this->set_cookies($user_id);
		$redirect = site_url();
		wp_redirect( $redirect );
	}
	function siteUrl(){
		return site_url();
	}
	function callBackUrl(){
		$url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
		if(strpos($url, '?')===false){
			$url .= '?';
		}else{
			$url .= '&';
		}
		return $url;
	}
	function getPluginUrl(){
		return plugins_url( '' , __FILE__ );
	}
}
?>