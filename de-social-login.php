<?php
/*
Plugin Name: DE Social Login
Plugin URI: http://Tiddu.com
Description: A Simple wordpress plugin which enable the user to login in wordress site with Google/Twitter/OpenId/LinkedIn/Facebook accounts with one click.
Version: 1.0.1
Author: Surinder Singh and Sunil Kumar
Author URI: http://developerextensions.com
License:GPL2
*/
//error_reporting(E_ALL);

/* register and unregister hooks */
register_activation_hook(__FILE__, 'desl_install_plugin');
function desl_install_plugin(){
	if(!function_exists('curl_version')){
		trigger_error('You must enable CURL on your server!', E_USER_ERROR);
	}
	if(version_compare(phpversion(), '5.3', '<')){
		trigger_error('You must have at least PHP 5.3 to use De Social Login!', E_USER_ERROR);
	}
}

add_filter('wp_login_errors', 'desl_login_errors', 3);
function desl_login_errors($errors, $redirect_to=''){
	if( isset($_GET['registration']) && 1 == $_GET['registration'] )
		$errors->add('registration_required', get_option('de_social_login_force_register_message'), 'message');
	return $errors;
}

define( 'loginByOpenID_PATH', plugin_dir_path(__FILE__) );
if (is_admin()){// admin actions
	include(loginByOpenID_PATH.'admin.php');
}else{
	include(loginByOpenID_PATH.'front.php');
}
?>