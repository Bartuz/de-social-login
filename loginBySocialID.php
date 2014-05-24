<?php
class loginBySocialID{
	static $loginKey 	= 'SOCIALID';
	public static function getBoxOrders(){
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
		$options['force_register'] 		= get_option('de_social_login_force_register');
		$options['force_register_message'] 		= get_option('de_social_login_force_register_message');
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
	
	function loginUser($user_id){
		$reauth = empty($_REQUEST['reauth']) ? false : true;
		if ( $reauth )
			wp_clear_auth_cookie();
		
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
			// Redirect to https if user wants ssl
			if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
				$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
		} else {
			$redirect_to = admin_url();
		}
		
		if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
		$secure_cookie = false;

		// If cookies are disabled we can't log in even with a valid user+pass
		if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
			$user = new WP_Error('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
		else
			$user = wp_signon('', $secure_cookie);
		
		if(!$this->set_cookies($user_id)){
			return false;
		}
		
		$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : site_url();
		$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
		wp_safe_redirect( $redirect_to );
		exit();
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
	public static function getPluginUrl(){
		return plugins_url( '' , __FILE__ );
	}
}
?>